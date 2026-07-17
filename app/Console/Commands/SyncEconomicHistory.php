<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Console\Command;
use Throwable;

class SyncEconomicHistory extends Command
{
    protected $signature = 'supplyguard:sync-economics
                            {country? : Kode ISO2, ISO3, atau nama negara}
                            {--years=10 : Jumlah tahun riwayat ekonomi}
                            {--limit=0 : Batasi jumlah negara, 0 berarti semua}
                            {--only-missing : Hanya sinkronkan negara yang belum punya data ekonomi}
                            {--all : Tetap didukung untuk kompatibilitas command lama}';

    protected $description = 'Sinkronkan riwayat GDP, inflasi, populasi, ekspor, dan impor dari World Bank.';

    public function handle(
        CountryService $service
    ): int {
        $years = max(
            1,
            (int) $this->option('years')
        );

        $limit = max(
            0,
            (int) $this->option('limit')
        );

        $countryKeyword = $this->argument('country');

        $query = Country::query()
            ->select('countries.*')
            ->where(function ($query) {
                $query
                    ->whereNotNull('countries.code')
                    ->orWhereNotNull('countries.cca3');
            });

        /*
         * Jika user mengisi country, command hanya memproses
         * satu negara sesuai kode ISO2, ISO3, atau nama negara.
         *
         * Contoh:
         * php artisan supplyguard:sync-economics ID
         * php artisan supplyguard:sync-economics USA
         * php artisan supplyguard:sync-economics Indonesia
         */
        if ($countryKeyword) {
            $keyword = trim((string) $countryKeyword);
            $upperKeyword = strtoupper($keyword);

            $query->where(function ($subQuery) use (
                $keyword,
                $upperKeyword
            ) {
                $subQuery
                    ->where(
                        'countries.code',
                        $upperKeyword
                    )
                    ->orWhere(
                        'countries.cca3',
                        $upperKeyword
                    )
                    ->orWhere(
                        'countries.name',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhere(
                        'countries.official_name',
                        'like',
                        '%' . $keyword . '%'
                    );
            });
        }

        /*
         * Jika --only-missing dipakai, sistem hanya mengambil
         * negara yang belum punya data ekonomi.
         */
        if ($this->option('only-missing')) {
            $query->whereNotExists(function ($subQuery) {
                $subQuery
                    ->selectRaw('1')
                    ->from('country_economics')
                    ->whereColumn(
                        'country_economics.country_id',
                        'countries.id'
                    );
            });
        }

        $query->orderBy('countries.name');

        /*
         * Jika limit 0, semua negara diproses.
         * Ini yang membuat data ekonomi lebih kuat dan global.
         */
        if ($limit > 0) {
            $query->limit($limit);
        }

        $countries = $query->get();

        if ($countries->isEmpty()) {
            $this->error('Negara tidak ditemukan atau semua negara sudah memiliki data ekonomi.');

            return self::FAILURE;
        }

        $this->newLine();

        $this->info('Sinkronisasi ekonomi dimulai.');
        $this->line('Jumlah negara : ' . $countries->count());
        $this->line('Jumlah tahun  : ' . $years);

        if ($this->option('only-missing')) {
            $this->line('Mode          : hanya negara yang belum punya data ekonomi');
        } else {
            $this->line('Mode          : semua negara yang memenuhi syarat');
        }

        $this->newLine();

        $bar = $this->output->createProgressBar(
            $countries->count()
        );

        $success = 0;
        $failed = 0;
        $failedCountries = [];

        foreach ($countries as $country) {
            try {
                $service->economicHistory(
                    $country,
                    $years,
                    true
                );

                $success++;
            } catch (Throwable $e) {
                $failed++;

                $failedCountries[] = [
                    'name' => $country->name,
                    'code' => $country->code,
                    'message' => $e->getMessage(),
                ];
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);

        $this->info('Riwayat ekonomi selesai disinkronkan.');

        $this->table(
            [
                'Keterangan',
                'Jumlah',
            ],
            [
                [
                    'Negara diproses',
                    $countries->count(),
                ],
                [
                    'Berhasil diproses',
                    $success,
                ],
                [
                    'Gagal diproses',
                    $failed,
                ],
                [
                    'Total negara unik dengan ekonomi',
                    $this->economicCountryCount(),
                ],
                [
                    'Total baris ekonomi',
                    $this->economicRowCount(),
                ],
            ]
        );

        if (! empty($failedCountries)) {
            $this->warn('Beberapa negara gagal diproses:');

            foreach (
                array_slice($failedCountries, 0, 20)
                as $failedCountry
            ) {
                $this->line(
                    '- '
                    . $failedCountry['name']
                    . ' ('
                    . ($failedCountry['code'] ?? '-')
                    . '): '
                    . $failedCountry['message']
                );
            }
        }

        $this->newLine();

        $this->comment('Cek hasil dengan command:');
        $this->line(
            'php artisan tinker --execute="dump([\'ekonomi_negara_unik\'=>DB::table(\'country_economics\')->whereNotNull(\'country_id\')->distinct()->count(\'country_id\'),\'total_economics\'=>DB::table(\'country_economics\')->count(),\'world_bank_logs\'=>DB::table(\'api_logs\')->where(\'service\',\'World Bank\')->count(),\'world_bank_gagal\'=>DB::table(\'api_logs\')->where(\'service\',\'World Bank\')->where(\'success\',0)->count()]);"'
        );

        return self::SUCCESS;
    }

    private function economicCountryCount(): int
    {
        return (int) \DB::table('country_economics')
            ->whereNotNull('country_id')
            ->distinct()
            ->count('country_id');
    }

    private function economicRowCount(): int
    {
        return (int) \DB::table('country_economics')
            ->count();
    }
}