<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillPortCountryIdsCommand extends Command
{
    protected $signature = 'supplyguard:backfill-port-country-ids {--dry-run : Hanya cek tanpa mengubah database}';

    protected $description = 'Menghubungkan ulang ports.country_id berdasarkan country_name.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $countries = DB::table('countries')
            ->select('id', 'name', 'official_name', 'code', 'cca3')
            ->get();

        if ($countries->isEmpty()) {
            $this->error('Data negara masih kosong.');

            return self::FAILURE;
        }

        $countryIndex = [];

        foreach ($countries as $country) {
            $names = [
                $country->name,
                $country->official_name,
                $country->code,
                $country->cca3,
            ];

            foreach ($names as $name) {
                $key = $this->normalizeName($name);

                if ($key !== '') {
                    $countryIndex[$key] = $country->id;
                }
            }
        }

        $checked = 0;
        $updated = 0;
        $notMatched = [];

        DB::table('ports')
            ->whereNull('country_id')
            ->whereNotNull('country_name')
            ->where('country_name', '<>', '')
            ->orderBy('id')
            ->chunkById(200, function ($ports) use ($countryIndex, $dryRun, &$checked, &$updated, &$notMatched) {
                foreach ($ports as $port) {
                    $checked++;

                    $key = $this->normalizeName($port->country_name);

                    $countryId = $countryIndex[$key] ?? null;

                    if (!$countryId) {
                        $notMatched[] = $port->country_name;
                        continue;
                    }

                    if (!$dryRun) {
                        DB::table('ports')
                            ->where('id', $port->id)
                            ->update([
                                'country_id' => $countryId,
                                'updated_at' => now(),
                            ]);
                    }

                    $updated++;
                }
            });

        $this->newLine();

        if ($dryRun) {
            $this->info('Mode simulasi selesai. Database belum diubah.');
        } else {
            $this->info('Relasi country_id pelabuhan berhasil diperbarui.');
        }

        $this->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Pelabuhan dicek', number_format($checked, 0, ',', '.')],
                [$dryRun ? 'Akan diperbarui' : 'Berhasil diperbarui', number_format($updated, 0, ',', '.')],
                ['Nama negara belum cocok', number_format(count(array_unique($notMatched)), 0, ',', '.')],
            ]
        );

        $notMatched = collect($notMatched)
            ->unique()
            ->sort()
            ->take(30);

        if ($notMatched->isNotEmpty()) {
            $this->warn('Contoh nama negara yang belum cocok:');

            foreach ($notMatched as $name) {
                $this->line('- ' . $name);
            }
        }

        return self::SUCCESS;
    }

    private function normalizeName(mixed $value): string
    {
        $text = Str::lower(trim((string) $value));

        if ($text === '') {
            return '';
        }

        $text = Str::ascii($text);

        $text = str_replace(
            ['&', '.', ',', '(', ')', '[', ']', '/', '\\', '-', '_', "'", '"'],
            ' ',
            $text
        );

        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}