<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\SystemSetting;
use App\Services\NewsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncCountryNews extends Command
{
    protected $signature = 'supplyguard:sync-news
                            {country? : Kode ISO2, ISO3, atau nama negara}
                            {--limit=80 : Jumlah negara per proses, maksimal 250}
                            {--articles=5 : Artikel per negara}
                            {--reset : Mulai lagi dari negara pertama}
                            {--only-missing : Hanya ambil negara yang belum punya berita}
                            {--delay=2 : Jeda antar request API dalam detik}
                            {--force : Paksa ambil ulang berita dari GNews}';

    protected $description =
        'Mengambil berita GNews untuk negara secara bertahap dan memperluas cakupan negara.';

    public function handle(
        NewsService $newsService
    ): int {
        if (blank(config('services.gnews.key'))) {
            $this->error(
                'GNEWS_API_KEY belum diisi di file .env.'
            );

            return self::FAILURE;
        }

        $limit = max(
            1,
            min((int) $this->option('limit'), 250)
        );

        $articles = max(
            1,
            min((int) $this->option('articles'), 10)
        );

        $delay = max(
            0,
            (int) $this->option('delay')
        );

        $countryKeyword = $this->argument('country');

        $settingKey = 'news_sync_last_country_id';

        if ($this->option('reset')) {
            SystemSetting::updateOrCreate(
                ['key' => $settingKey],
                [
                    'value' => '0',
                    'type' => 'integer',
                    'description' =>
                        'ID negara terakhir sinkronisasi berita',
                ]
            );
        }

        $countries = $this->getCountries(
            limit: $limit,
            settingKey: $settingKey,
            countryKeyword: $countryKeyword
        );

        if ($countries->isEmpty()) {
            $this->warn(
                'Tidak ada negara yang perlu disinkronkan.'
            );

            return self::SUCCESS;
        }

        $this->newLine();

        $this->info('Sinkronisasi berita GNews dimulai.');
        $this->line('Jumlah negara : ' . $countries->count());
        $this->line('Artikel/negara: ' . $articles);
        $this->line('Delay request : ' . $delay . ' detik');

        if ($this->option('only-missing')) {
            $this->line('Mode         : hanya negara yang belum punya berita');
        } elseif ($countryKeyword) {
            $this->line('Mode         : negara tertentu');
        } else {
            $this->line('Mode         : sinkronisasi bertahap');
        }

        $this->newLine();

        $progress = $this->output->createProgressBar(
            $countries->count()
        );

        $progress->start();

        $success = 0;
        $failed = 0;
        $withNews = 0;
        $withoutNews = 0;
        $failedCountries = [];

        foreach ($countries as $country) {
            try {
                $beforeCount = DB::table('news_cache')
                    ->where('country_id', $country->id)
                    ->count();

                /*
                 * Query dibuat cukup luas agar peluang berita muncul
                 * lebih besar, tetapi tetap relevan dengan SupplyGuard:
                 * logistik, perdagangan, ekonomi, inflasi, ekspor,
                 * impor, pelabuhan, pengiriman, dan supply chain.
                 */
                $searchQuery = $this->buildSearchQuery(
                    $country
                );

                $newsService->fetch(
                    country: $country,
                    query: $searchQuery,
                    limit: $articles,
                    force: $this->option('force')
                        || $this->option('only-missing')
                );

                $afterCount = DB::table('news_cache')
                    ->where('country_id', $country->id)
                    ->count();

                if ($afterCount > $beforeCount || $afterCount > 0) {
                    $withNews++;
                } else {
                    $withoutNews++;
                }

                $success++;

                /*
                 * Simpan posisi terakhir hanya untuk mode bertahap.
                 * Untuk country tertentu dan only-missing, posisi
                 * terakhir tidak perlu dipaksa berubah.
                 */
                if (
                    ! $countryKeyword
                    && ! $this->option('only-missing')
                ) {
                    SystemSetting::updateOrCreate(
                        ['key' => $settingKey],
                        [
                            'value' => (string) $country->id,
                            'type' => 'integer',
                            'description' =>
                                'ID negara terakhir sinkronisasi berita',
                        ]
                    );
                }
            } catch (Throwable $exception) {
                report($exception);

                $failed++;

                $failedCountries[] = [
                    'name' => $country->name,
                    'code' => $country->code,
                    'message' => $exception->getMessage(),
                ];
            }

            $progress->advance();

            /*
             * Paket gratis GNews biasanya sensitif terhadap rate limit.
             * Delay dibuat agar request tidak terlalu cepat.
             */
            if ($delay > 0) {
                sleep($delay);
            }
        }

        $progress->finish();

        $this->newLine(2);

        $this->info('Sinkronisasi berita selesai.');

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
                    'Negara dengan berita',
                    $withNews,
                ],
                [
                    'Negara belum mendapat berita baru',
                    $withoutNews,
                ],
                [
                    'Total negara unik punya berita',
                    $this->newsCountryCount(),
                ],
                [
                    'Total berita tersimpan',
                    $this->newsRowCount(),
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
            'php artisan tinker --execute="dump([\'berita_negara_unik\'=>DB::table(\'news_cache\')->whereNotNull(\'country_id\')->distinct()->count(\'country_id\'),\'total_berita\'=>DB::table(\'news_cache\')->count(),\'gnews_logs\'=>DB::table(\'api_logs\')->where(\'service\',\'GNews\')->count(),\'gnews_gagal\'=>DB::table(\'api_logs\')->where(\'service\',\'GNews\')->where(\'success\',0)->count()]);"'
        );

        return self::SUCCESS;
    }

    private function getCountries(
        int $limit,
        string $settingKey,
        ?string $countryKeyword
    ) {
        $query = Country::query()
            ->select('countries.*');

        /*
         * Mode negara tertentu.
         *
         * Contoh:
         * php artisan supplyguard:sync-news ID
         * php artisan supplyguard:sync-news USA
         * php artisan supplyguard:sync-news Indonesia
         */
        if ($countryKeyword) {
            $keyword = trim((string) $countryKeyword);
            $upperKeyword = strtoupper($keyword);

            return $query
                ->where(function ($subQuery) use (
                    $keyword,
                    $upperKeyword
                ) {
                    $subQuery
                        ->where('countries.code', $upperKeyword)
                        ->orWhere('countries.cca3', $upperKeyword)
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
                })
                ->orderBy('countries.name')
                ->limit($limit)
                ->get();
        }

        /*
         * Mode only-missing:
         * hanya ambil negara yang belum punya berita sama sekali.
         */
        if ($this->option('only-missing')) {
            return $query
                ->whereNotExists(function ($subQuery) {
                    $subQuery
                        ->selectRaw('1')
                        ->from('news_cache')
                        ->whereColumn(
                            'news_cache.country_id',
                            'countries.id'
                        );
                })
                ->orderBy('countries.name')
                ->limit($limit)
                ->get();
        }

        /*
         * Mode bertahap:
         * lanjut dari ID negara terakhir.
         */
        $lastCountryId = (int) (
            SystemSetting::where(
                'key',
                $settingKey
            )->value('value') ?? 0
        );

        $countries = $query
            ->where('countries.id', '>', $lastCountryId)
            ->orderBy('countries.id')
            ->limit($limit)
            ->get();

        /*
         * Jika sudah sampai negara terakhir,
         * mulai lagi dari awal.
         */
        if ($countries->isEmpty()) {
            SystemSetting::updateOrCreate(
                ['key' => $settingKey],
                [
                    'value' => '0',
                    'type' => 'integer',
                    'description' =>
                        'ID negara terakhir sinkronisasi berita',
                ]
            );

            $countries = Country::query()
                ->select('countries.*')
                ->orderBy('countries.id')
                ->limit($limit)
                ->get();
        }

        return $countries;
    }

    private function buildSearchQuery(
        Country $country
    ): string {
        $countryName = trim(
            (string) $country->name
        );

        /*
         * Jangan terlalu panjang agar query GNews tetap stabil.
         * Negara tetap dimasukkan agar berita lebih spesifik.
         */
        return '"'
            . $countryName
            . '" '
            . '(logistics OR trade OR shipping OR economy OR inflation OR export OR import OR port OR supply chain)';
    }

    private function newsCountryCount(): int
    {
        return (int) DB::table('news_cache')
            ->whereNotNull('country_id')
            ->distinct()
            ->count('country_id');
    }

    private function newsRowCount(): int
    {
        return (int) DB::table('news_cache')
            ->count();
    }
}