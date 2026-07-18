<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CalculateRiskScoresCommand extends Command
{
    protected $signature = 'supplyguard:calculate-risk
                            {country? : Kode ISO2, ISO3, atau nama negara tertentu}
                            {--limit=0 : Batasi jumlah negara, 0 berarti semua}
                            {--only-missing : Hanya hitung negara yang belum punya skor risiko}';

    protected $description = 'Menghitung ulang skor risiko rantai pasok untuk negara berdasarkan data cuaca, ekonomi, nilai tukar, berita, dan pelabuhan.';

    public function handle(): int
    {
        if (! Schema::hasTable('countries')) {
            $this->error('Tabel countries belum tersedia.');
            return self::FAILURE;
        }

        if (! Schema::hasTable('risk_scores')) {
            $this->error('Tabel risk_scores belum tersedia. Jalankan migrasi terlebih dahulu.');
            return self::FAILURE;
        }

        $countryKeyword = $this->argument('country');
        $limit = max(0, (int) $this->option('limit'));

        $query = Country::query()
            ->orderBy('name');

        if ($countryKeyword) {
            $keyword = trim((string) $countryKeyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('code', $keyword)
                    ->orWhere('cca3', $keyword)
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('official_name', 'like', '%' . $keyword . '%');
            });
        }

        if ($this->option('only-missing')) {
            $query->whereNotExists(function (Builder $subQuery) {
                $subQuery->selectRaw('1')
                    ->from('risk_scores')
                    ->whereColumn('risk_scores.country_id', 'countries.id');
            });
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $countries = $query->get();

        if ($countries->isEmpty()) {
            $this->warn('Tidak ada negara yang perlu dihitung.');
            return self::SUCCESS;
        }

        $this->info('Mulai menghitung risiko untuk ' . $countries->count() . ' negara...');

        $progress = $this->output->createProgressBar($countries->count());
        $progress->start();

        $success = 0;
        $failed = 0;

        foreach ($countries as $country) {
            try {
                $scores = $this->calculateCountryRisk($country);
                $this->saveRiskScore($country, $scores);
                $success++;
            } catch (Throwable $exception) {
                $failed++;
                $this->newLine();
                $this->warn('Gagal menghitung ' . $country->name . ': ' . $exception->getMessage());
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);

        $this->info('Perhitungan risiko selesai.');
        $this->line('Berhasil : ' . $success . ' negara');
        $this->line('Gagal    : ' . $failed . ' negara');

        $this->newLine();

        $this->table(
            ['Informasi', 'Jumlah'],
            [
                ['Total negara', DB::table('countries')->count()],
                ['Negara punya risiko', DB::table('risk_scores')->whereNotNull('country_id')->distinct()->count('country_id')],
                ['Total data risiko', DB::table('risk_scores')->count()],
                ['Risiko terbaru', DB::table('risk_scores')->latest('calculated_at')->value('calculated_at') ?? '-'],
            ]
        );

        return self::SUCCESS;
    }

    private function calculateCountryRisk(Country $country): array
    {
        $weatherScore = $this->weatherScore($country);
        $inflationScore = $this->inflationScore($country);
        $currencyScore = $this->currencyScore($country);
        $newsScore = $this->newsScore($country);
        $portScore = $this->portScore($country);

        $totalScore = (
            ($weatherScore * 0.27) +
            ($inflationScore * 0.21) +
            ($currencyScore * 0.18) +
            ($newsScore * 0.22) +
            ($portScore * 0.12)
        );

        $totalScore = round(min(100, max(0, $totalScore)), 2);

        $riskLevel = $this->riskLevel($totalScore);

        return [
            'weather_score' => round($weatherScore, 2),
            'inflation_score' => round($inflationScore, 2),
            'currency_score' => round($currencyScore, 2),
            'news_score' => round($newsScore, 2),
            'port_score' => round($portScore, 2),
            'total_score' => $totalScore,
            'risk_level' => $riskLevel,
            'risk_label' => $riskLevel,
            'recommendation' => $this->recommendation($riskLevel),
            'calculated_at' => now(),
        ];
    }

    private function weatherScore(Country $country): float
    {
        if (! Schema::hasTable('weather_data')) {
            return 25;
        }

        $columns = Schema::getColumnListing('weather_data');

        if (! in_array('country_id', $columns, true)) {
            return 25;
        }

        $dateColumn = in_array('observed_at', $columns, true)
            ? 'observed_at'
            : (in_array('created_at', $columns, true) ? 'created_at' : 'id');

        $weather = DB::table('weather_data')
            ->where('country_id', $country->id)
            ->orderByDesc($dateColumn)
            ->first();

        if (! $weather) {
            return 35;
        }

        $stormRisk = $this->readNumber($weather, [
            'storm_risk_score',
            'weather_risk_score',
            'risk_score',
            'disruption_score',
        ]);

        if ($stormRisk !== null) {
            return min(100, max(0, $stormRisk));
        }

        $score = 10;

        $temperature = $this->readNumber($weather, [
            'temperature',
            'temperature_2m',
            'temp',
        ]);

        $windSpeed = $this->readNumber($weather, [
            'wind_speed',
            'wind_speed_10m',
            'windspeed',
        ]);

        $precipitation = $this->readNumber($weather, [
            'precipitation',
            'rain',
            'rainfall',
        ]);

        if ($temperature !== null) {
            if ($temperature >= 38 || $temperature <= 0) {
                $score += 25;
            } elseif ($temperature >= 32 || $temperature <= 10) {
                $score += 12;
            }
        }

        if ($windSpeed !== null) {
            if ($windSpeed >= 60) {
                $score += 30;
            } elseif ($windSpeed >= 35) {
                $score += 15;
            }
        }

        if ($precipitation !== null) {
            if ($precipitation >= 50) {
                $score += 25;
            } elseif ($precipitation >= 15) {
                $score += 12;
            }
        }

        return min(100, $score);
    }

    private function inflationScore(Country $country): float
    {
        if (! Schema::hasTable('country_economics')) {
            return 25;
        }

        $columns = Schema::getColumnListing('country_economics');

        if (! in_array('country_id', $columns, true)) {
            return 25;
        }

        $dateColumn = in_array('year', $columns, true)
            ? 'year'
            : (in_array('created_at', $columns, true) ? 'created_at' : 'id');

        $economic = DB::table('country_economics')
            ->where('country_id', $country->id)
            ->orderByDesc($dateColumn)
            ->first();

        if (! $economic) {
            return 35;
        }

        $inflation = $this->readNumber($economic, [
            'inflation_rate',
            'inflation',
            'inflation_value',
            'value',
        ]);

        if ($inflation === null) {
            return 30;
        }

        if ($inflation >= 15) {
            return 90;
        }

        if ($inflation >= 10) {
            return 75;
        }

        if ($inflation >= 6) {
            return 55;
        }

        if ($inflation >= 3) {
            return 35;
        }

        if ($inflation >= 0) {
            return 20;
        }

        return 30;
    }

    private function currencyScore(Country $country): float
    {
        if (! Schema::hasTable('currency_rates')) {
            return 25;
        }

        $columns = Schema::getColumnListing('currency_rates');

        $rateColumn = in_array('rate', $columns, true)
            ? 'rate'
            : (in_array('exchange_rate', $columns, true) ? 'exchange_rate' : null);

        if (! $rateColumn) {
            return 25;
        }

        $dateColumn = in_array('recorded_at', $columns, true)
            ? 'recorded_at'
            : (in_array('created_at', $columns, true) ? 'created_at' : 'id');

        $currencyCode = $this->countryCurrencyCode($country);

        $query = DB::table('currency_rates')
            ->whereNotNull($rateColumn);

        if ($currencyCode) {
            if (in_array('target_currency', $columns, true)) {
                $query->where('target_currency', $currencyCode);
            } elseif (in_array('currency', $columns, true)) {
                $query->where('currency', $currencyCode);
            }
        }

        $rates = $query
            ->orderByDesc($dateColumn)
            ->limit(7)
            ->pluck($rateColumn)
            ->map(fn ($value) => (float) $value)
            ->filter(fn ($value) => $value > 0)
            ->values();

        if ($rates->count() < 2) {
            return 30;
        }

        $latest = $rates->first();
        $oldest = $rates->last();

        if ($oldest <= 0) {
            return 30;
        }

        $changePercent = abs(($latest - $oldest) / $oldest) * 100;

        if ($changePercent >= 10) {
            return 85;
        }

        if ($changePercent >= 5) {
            return 65;
        }

        if ($changePercent >= 2) {
            return 45;
        }

        return 20;
    }

    private function newsScore(Country $country): float
    {
        if (! Schema::hasTable('news_cache')) {
            return 25;
        }

        $columns = Schema::getColumnListing('news_cache');

        $query = DB::table('news_cache');

        if (in_array('country_id', $columns, true)) {
            $query->where('country_id', $country->id);
        } elseif (in_array('country_code', $columns, true)) {
            $query->where('country_code', $country->code);
        } else {
            return 25;
        }

        $total = (clone $query)->count();

        if ($total <= 0) {
            return 35;
        }

        if (! in_array('sentiment', $columns, true)) {
            return 30;
        }

        $negative = (clone $query)
            ->where(function ($q) {
                $q->where('sentiment', 'Negatif')
                    ->orWhere('sentiment', 'negative');
            })
            ->count();

        $negativeRatio = ($negative / max(1, $total)) * 100;

        if ($negativeRatio >= 70) {
            return 90;
        }

        if ($negativeRatio >= 50) {
            return 75;
        }

        if ($negativeRatio >= 30) {
            return 55;
        }

        if ($negativeRatio >= 10) {
            return 35;
        }

        return 20;
    }

    private function portScore(Country $country): float
    {
        if (! Schema::hasTable('ports')) {
            return 25;
        }

        $columns = Schema::getColumnListing('ports');

        $query = DB::table('ports');

        if (in_array('country_id', $columns, true)) {
            $query->where('country_id', $country->id);
        } elseif (in_array('country_name', $columns, true)) {
            $query->where('country_name', $country->name);
        } else {
            return 25;
        }

        $count = $query->count();

        if ($count <= 0) {
            return 60;
        }

        if ($count <= 2) {
            return 35;
        }

        return 20;
    }

    private function saveRiskScore(Country $country, array $scores): void
    {
        $columns = Schema::getColumnListing('risk_scores');

        $payload = [];

        $this->putIfColumnExists($payload, $columns, 'country_id', $country->id);
        $this->putIfColumnExists($payload, $columns, 'weather_score', $scores['weather_score']);
        $this->putIfColumnExists($payload, $columns, 'weather_risk_score', $scores['weather_score']);
        $this->putIfColumnExists($payload, $columns, 'inflation_score', $scores['inflation_score']);
        $this->putIfColumnExists($payload, $columns, 'inflation_risk_score', $scores['inflation_score']);
        $this->putIfColumnExists($payload, $columns, 'currency_score', $scores['currency_score']);
        $this->putIfColumnExists($payload, $columns, 'currency_risk_score', $scores['currency_score']);
        $this->putIfColumnExists($payload, $columns, 'news_score', $scores['news_score']);
        $this->putIfColumnExists($payload, $columns, 'news_risk_score', $scores['news_score']);
        $this->putIfColumnExists($payload, $columns, 'port_score', $scores['port_score']);
        $this->putIfColumnExists($payload, $columns, 'port_risk_score', $scores['port_score']);
        $this->putIfColumnExists($payload, $columns, 'total_score', $scores['total_score']);
        $this->putIfColumnExists($payload, $columns, 'score', $scores['total_score']);
        $this->putIfColumnExists($payload, $columns, 'risk_level', $scores['risk_level']);
        $this->putIfColumnExists($payload, $columns, 'risk_label', $scores['risk_label']);
        $this->putIfColumnExists($payload, $columns, 'recommendation', $scores['recommendation']);
        $this->putIfColumnExists($payload, $columns, 'calculated_at', $scores['calculated_at']);

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        if (! DB::table('risk_scores')->where('country_id', $country->id)->exists()) {
            if (in_array('created_at', $columns, true)) {
                $payload['created_at'] = now();
            }

            DB::table('risk_scores')->insert($payload);
            return;
        }

        DB::table('risk_scores')
            ->where('country_id', $country->id)
            ->update($payload);
    }

    private function riskLevel(float $score): string
    {
        if ($score >= 80) {
            return 'Kritis';
        }

        if ($score >= 60) {
            return 'Tinggi';
        }

        if ($score >= 30) {
            return 'Sedang';
        }

        return 'Rendah';
    }

    private function recommendation(string $riskLevel): string
    {
        return match ($riskLevel) {
            'Kritis' => 'Risiko sangat tinggi. Disarankan mencari alternatif negara pemasok, memperkuat cadangan stok, dan memantau berita serta cuaca secara intensif.',
            'Tinggi' => 'Risiko tinggi. Disarankan melakukan evaluasi pemasok, menyiapkan rute logistik alternatif, dan memantau indikator utama secara berkala.',
            'Sedang' => 'Risiko sedang. Aktivitas rantai pasok masih dapat berjalan, namun tetap perlu pemantauan terhadap cuaca, ekonomi, nilai tukar, dan berita.',
            default => 'Risiko rendah. Aktivitas rantai pasok relatif aman, namun pemantauan berkala tetap diperlukan.',
        };
    }

    private function putIfColumnExists(array &$payload, array $columns, string $column, mixed $value): void
    {
        if (in_array($column, $columns, true)) {
            $payload[$column] = $value;
        }
    }

    private function readNumber(object $row, array $columns): ?float
    {
        foreach ($columns as $column) {
            if (isset($row->{$column}) && is_numeric($row->{$column})) {
                return (float) $row->{$column};
            }
        }

        return null;
    }

    private function countryCurrencyCode(Country $country): ?string
    {
        $possibleColumns = [
            'currency_code',
            'currency',
            'currency_alpha3',
        ];

        foreach ($possibleColumns as $column) {
            if (! empty($country->{$column})) {
                return strtoupper((string) $country->{$column});
            }
        }

        $map = [
            'ID' => 'IDR',
            'US' => 'USD',
            'CN' => 'CNY',
            'JP' => 'JPY',
            'SG' => 'SGD',
            'MY' => 'MYR',
            'TH' => 'THB',
            'VN' => 'VND',
            'PH' => 'PHP',
            'IN' => 'INR',
            'GB' => 'GBP',
            'DE' => 'EUR',
            'FR' => 'EUR',
            'IT' => 'EUR',
            'ES' => 'EUR',
            'NL' => 'EUR',
            'AU' => 'AUD',
            'CA' => 'CAD',
            'KR' => 'KRW',
            'MM' => 'MMK',
            'TR' => 'TRY',
            'BR' => 'BRL',
            'RU' => 'RUB',
            'ZA' => 'ZAR',
        ];

        $code = strtoupper((string) ($country->code ?? ''));

        return $map[$code] ?? null;
    }
}