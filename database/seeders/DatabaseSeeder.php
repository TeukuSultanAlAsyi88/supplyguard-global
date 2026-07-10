<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Country;
use App\Models\CountryEconomic;
use App\Models\CurrencyRate;
use App\Models\NegativeWord;
use App\Models\NewsCache;
use App\Models\NewsSentiment;
use App\Models\Port;
use App\Models\PositiveWord;
use App\Models\RiskScore;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WeatherData;
use App\Services\PortImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@supplyguard.test'],
            ['name' => 'Administrator SupplyGuard', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true]
        );

        User::updateOrCreate(
            ['email' => 'user@supplyguard.test'],
            ['name' => 'Pengguna Demo', 'password' => Hash::make('password'), 'role' => 'user', 'is_active' => true]
        );

        $this->seedCountries();
        $this->seedPorts($admin->id);
        $this->seedSentimentWords();
        $this->seedSettings();
        $this->seedArticles($admin->id);
        $this->seedEconomicHistory();
        $this->seedCurrencyHistory();
        $this->seedWeather();
        $this->seedNews();
        $this->seedRiskHistory();
    }

    private function seedCountries(): void
    {
        $countries = [
            ['Indonesia', 'Republic of Indonesia', 'ID', 'IDN', 'Asia', 'South-Eastern Asia', 'Jakarta', 'IDR', 'Indonesian rupiah', 'Indonesian', 'https://flagcdn.com/id.svg', -2.5489, 118.0149, 275501339],
            ['China', "People's Republic of China", 'CN', 'CHN', 'Asia', 'Eastern Asia', 'Beijing', 'CNY', 'Chinese yuan', 'Chinese', 'https://flagcdn.com/cn.svg', 35.8617, 104.1954, 1411750000],
            ['Germany', 'Federal Republic of Germany', 'DE', 'DEU', 'Europe', 'Western Europe', 'Berlin', 'EUR', 'Euro', 'German', 'https://flagcdn.com/de.svg', 51.1657, 10.4515, 83200000],
            ['Australia', 'Commonwealth of Australia', 'AU', 'AUS', 'Oceania', 'Australia and New Zealand', 'Canberra', 'AUD', 'Australian dollar', 'English', 'https://flagcdn.com/au.svg', -25.2744, 133.7751, 26000000],
            ['United States', 'United States of America', 'US', 'USA', 'Americas', 'North America', 'Washington, D.C.', 'USD', 'United States dollar', 'English', 'https://flagcdn.com/us.svg', 37.0902, -95.7129, 333000000],
            ['Japan', 'Japan', 'JP', 'JPN', 'Asia', 'Eastern Asia', 'Tokyo', 'JPY', 'Japanese yen', 'Japanese', 'https://flagcdn.com/jp.svg', 36.2048, 138.2529, 125000000],
            ['Singapore', 'Republic of Singapore', 'SG', 'SGP', 'Asia', 'South-Eastern Asia', 'Singapore', 'SGD', 'Singapore dollar', 'English, Malay', 'https://flagcdn.com/sg.svg', 1.3521, 103.8198, 5637000],
            ['Malaysia', 'Malaysia', 'MY', 'MYS', 'Asia', 'South-Eastern Asia', 'Kuala Lumpur', 'MYR', 'Malaysian ringgit', 'Malay', 'https://flagcdn.com/my.svg', 4.2105, 101.9758, 33900000],
            ['India', 'Republic of India', 'IN', 'IND', 'Asia', 'Southern Asia', 'New Delhi', 'INR', 'Indian rupee', 'Hindi, English', 'https://flagcdn.com/in.svg', 20.5937, 78.9629, 1417000000],
            ['United Kingdom', 'United Kingdom of Great Britain and Northern Ireland', 'GB', 'GBR', 'Europe', 'Northern Europe', 'London', 'GBP', 'Pound sterling', 'English', 'https://flagcdn.com/gb.svg', 55.3781, -3.4360, 67000000],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country[2]],
                [
                    'name' => $country[0],
                    'official_name' => $country[1],
                    'cca3' => $country[3],
                    'region' => $country[4],
                    'subregion' => $country[5],
                    'capital' => $country[6],
                    'currency_code' => $country[7],
                    'currency_name' => $country[8],
                    'language' => $country[9],
                    'flag_url' => $country[10],
                    'latitude' => $country[11],
                    'longitude' => $country[12],
                    'population' => $country[13],
                ]
            );
        }
    }

    private function seedPorts(int $adminId): void
    {
        $ports = [
            ['Tanjung Priok', 'IDJKT', 'Jakarta', 'Indonesia', -6.1033, 106.8869],
            ['Tanjung Perak', 'IDSUB', 'Surabaya', 'Indonesia', -7.1987, 112.7351],
            ['Belawan', 'IDBLW', 'Medan', 'Indonesia', 3.7850, 98.6940],
            ['Port of Shanghai', 'CNSHA', 'Shanghai', 'China', 31.2304, 121.4737],
            ['Port of Shenzhen', 'CNSZX', 'Shenzhen', 'China', 22.5431, 114.0579],
            ['Port of Hamburg', 'DEHAM', 'Hamburg', 'Germany', 53.5461, 9.9661],
            ['Port Botany', 'AUBTB', 'Sydney', 'Australia', -33.9690, 151.2190],
            ['Port of Los Angeles', 'USLAX', 'Los Angeles', 'United States', 33.7405, -118.2720],
            ['Port of Yokohama', 'JPYOK', 'Yokohama', 'Japan', 35.4437, 139.6380],
            ['Port of Singapore', 'SGSIN', 'Singapore', 'Singapore', 1.2644, 103.8400],
            ['Port Klang', 'MYPKG', 'Klang', 'Malaysia', 3.0000, 101.4000],
            ['Jawaharlal Nehru Port', 'INNSA', 'Navi Mumbai', 'India', 18.9497, 72.9512],
            ['Port of Felixstowe', 'GBFXT', 'Felixstowe', 'United Kingdom', 51.9630, 1.3510],
        ];

        foreach ($ports as $port) {
            $country = Country::where('name', $port[3])->first();
            Port::updateOrCreate(
                ['unlocode' => $port[1]],
                [
                    'country_id' => $country?->id,
                    'name' => $port[0],
                    'city' => $port[2],
                    'country_name' => $port[3],
                    'latitude' => $port[4],
                    'longitude' => $port[5],
                    'port_type' => 'Pelabuhan Laut',
                    'status' => 'Aktif',
                ]
            );
        }

        $samplePorts = database_path('data/world_port_index_sample.csv');
        if (is_file($samplePorts)) {
            app(PortImportService::class)->import($samplePorts, $adminId);
        }
    }

    private function seedSentimentWords(): void
    {
        foreach (['growth', 'increase', 'profit', 'stable', 'improve', 'recovery', 'strong', 'success', 'naik', 'tumbuh', 'stabil', 'untung', 'membaik', 'pulih', 'aman'] as $word) {
            PositiveWord::firstOrCreate(compact('word'));
        }
        foreach (['war', 'crisis', 'inflation', 'delay', 'disaster', 'decrease', 'conflict', 'shortage', 'perang', 'krisis', 'inflasi', 'terlambat', 'bencana', 'turun', 'konflik', 'kelangkaan'] as $word) {
            NegativeWord::firstOrCreate(compact('word'));
        }
    }

    private function seedSettings(): void
    {
        foreach ([
            'risk_weather_weight' => 30,
            'risk_inflation_weight' => 20,
            'risk_news_weight' => 40,
            'risk_currency_weight' => 10,
        ] as $key => $value) {
            SystemSetting::updateOrCreate(
                compact('key'),
                ['value' => $value, 'type' => 'number', 'description' => 'Bobot perhitungan risiko']
            );
        }
    }

    private function seedArticles(int $adminId): void
    {
        Article::firstOrCreate(
            ['slug' => 'panduan-pemantauan-risiko'],
            [
                'user_id' => $adminId,
                'title' => 'Panduan Pemantauan Risiko Rantai Pasok',
                'excerpt' => 'Cara membaca indikator risiko pada SupplyGuard Indonesia.',
                'content' => 'Gunakan skor risiko, kondisi cuaca, data ekonomi, nilai tukar, dan sentimen berita sebagai bahan pendukung keputusan.',
                'status' => 'published',
                'published_at' => now(),
            ]
        );
    }

    /** Data demo membuat grafik 10 tahun langsung tampil; sinkronisasi World Bank akan memperbaruinya. */
    private function seedEconomicHistory(): void
    {
        $profiles = [
            'ID' => [932_000_000_000, 0.055, 3.2, 261_000_000],
            'CN' => [11_200_000_000_000, 0.060, 2.1, 1_378_000_000],
            'DE' => [3_500_000_000_000, 0.030, 1.6, 82_000_000],
            'AU' => [1_200_000_000_000, 0.035, 1.9, 24_200_000],
            'US' => [18_700_000_000_000, 0.045, 2.2, 323_000_000],
            'JP' => [5_000_000_000_000, 0.015, 0.7, 127_000_000],
            'SG' => [318_000_000_000, 0.050, 1.4, 5_600_000],
            'MY' => [301_000_000_000, 0.045, 2.1, 31_500_000],
            'IN' => [2_290_000_000_000, 0.070, 4.8, 1_324_000_000],
            'GB' => [2_690_000_000_000, 0.030, 2.0, 65_600_000],
        ];
        $endYear = (int) now()->format('Y') - 1;

        foreach ($profiles as $code => [$baseGdp, $growth, $baseInflation, $basePopulation]) {
            $country = Country::where('code', $code)->first();
            if (! $country) {
                continue;
            }

            for ($offset = 9; $offset >= 0; $offset--) {
                $index = 9 - $offset;
                $year = $endYear - $offset;
                $gdp = $baseGdp * ((1 + $growth) ** $index);
                $inflation = max(0.2, $baseInflation + sin($index * 0.9) * 1.2 + ($index === 7 ? 2.4 : 0));
                $population = (int) round($basePopulation * ((1.009) ** $index));

                CountryEconomic::updateOrCreate(
                    ['country_id' => $country->id, 'year' => $year],
                    [
                        'gdp' => round($gdp, 2),
                        'inflation' => round($inflation, 4),
                        'exports' => round($gdp * (0.18 + (($index % 3) * 0.01)), 2),
                        'imports' => round($gdp * (0.16 + (($index % 2) * 0.012)), 2),
                        'population' => $population,
                    ]
                );
            }
        }
    }

    /** Riwayat kurs demo memastikan grafik 30 hari tersedia tanpa internet. */
    private function seedCurrencyHistory(): void
    {
        $rates = ['IDR' => 16250, 'CNY' => 7.23, 'EUR' => 0.92, 'AUD' => 1.52, 'USD' => 1, 'JPY' => 151.2, 'SGD' => 1.35, 'MYR' => 4.70, 'INR' => 83.4, 'GBP' => 0.79];

        foreach ($rates as $target => $baseRate) {
            $previous = null;
            for ($day = 29; $day >= 0; $day--) {
                $date = now()->subDays($day)->toDateString();
                $index = 29 - $day;
                $rate = $baseRate * (1 + (sin($index / 3) * 0.006) + ($index * 0.00025));
                if ($target === 'USD') {
                    $rate = 1;
                }
                $change = $previous && $previous > 0 ? (($rate - $previous) / $previous) * 100 : 0;
                $recordedAt = $day === 0 ? now() : Carbon::parse($date)->setTime(12, 0);

                CurrencyRate::updateOrCreate(
                    ['base_currency' => 'USD', 'target_currency' => $target, 'rate_date' => $date, 'source' => 'Data Demo Seeder'],
                    ['rate' => round($rate, 6), 'change_percent' => round($change, 4), 'recorded_at' => $recordedAt]
                );
                $previous = $rate;
            }
        }
    }

    private function seedWeather(): void
    {
        $weather = [
            'ID' => [30.2, 72, 2.4, 68, 17, 28, 61],
            'CN' => [24.4, 60, 0.4, 28, 14, 24, 2],
            'DE' => [18.1, 66, 1.2, 48, 21, 34, 61],
            'AU' => [22.8, 48, 0.0, 10, 25, 37, 1],
            'US' => [26.0, 58, 0.3, 25, 24, 39, 2],
            'JP' => [27.2, 70, 3.0, 75, 19, 31, 63],
            'SG' => [31.0, 76, 4.2, 82, 16, 29, 80],
            'MY' => [29.5, 79, 3.1, 77, 15, 27, 80],
            'IN' => [33.1, 64, 0.8, 40, 22, 36, 3],
            'GB' => [16.4, 74, 1.8, 55, 27, 44, 61],
        ];

        foreach ($weather as $code => [$temperature, $humidity, $rain, $probability, $wind, $gust, $weatherCode]) {
            $country = Country::where('code', $code)->first();
            if (! $country) {
                continue;
            }
            $storm = min(100, ($wind * .55) + ($gust * .35) + min(20, $rain * 4) + ($probability * .15));
            WeatherData::updateOrCreate(
                ['country_id' => $country->id, 'observed_at' => now()->startOfHour()],
                [
                    'temperature' => $temperature,
                    'apparent_temperature' => $temperature + 1.2,
                    'humidity' => $humidity,
                    'precipitation' => $rain,
                    'precipitation_probability' => $probability,
                    'wind_speed' => $wind,
                    'wind_gust' => $gust,
                    'weather_code' => $weatherCode,
                    'condition' => in_array($weatherCode, [61, 63, 65, 80, 81, 82], true) ? 'Hujan' : 'Berawan',
                    'is_day' => true,
                    'storm_risk' => round($storm, 2),
                ]
            );
        }
    }

    private function seedNews(): void
    {
        $records = [
            ['ID', 'Ekspor Indonesia tumbuh stabil di tengah pemulihan logistik', 'Kinerja ekspor meningkat dan pelabuhan beroperasi stabil.', 'Positif', 2, 0],
            ['ID', 'Cuaca buruk berpotensi menyebabkan keterlambatan pengiriman', 'Hujan dan angin kencang dapat memicu delay kapal.', 'Negatif', 0, 2],
            ['SG', 'Aktivitas perdagangan regional berjalan normal', 'Pergerakan barang terpantau netral tanpa gangguan besar.', 'Netral', 0, 0],
            ['CN', 'Shipping demand improves as exports increase', 'Trade volume shows stable recovery and stronger demand.', 'Positif', 3, 0],
            ['DE', 'European logistics faces inflation and delay risk', 'Higher inflation and transport delay add pressure to supply chains.', 'Negatif', 0, 2],
            ['AU', 'Port operations continue without major disruption', 'Shipping and trade activity remain steady.', 'Netral', 0, 0],
        ];

        foreach ($records as $index => [$code, $title, $description, $sentiment, $positive, $negative]) {
            $country = Country::where('code', $code)->first();
            $news = NewsCache::updateOrCreate(
                ['title' => $title],
                [
                    'country_id' => $country?->id,
                    'description' => $description,
                    'source' => 'Data Demo',
                    'published_at' => now()->subHours($index * 3),
                    'sentiment' => $sentiment,
                    'positive_score' => $positive,
                    'negative_score' => $negative,
                    'query' => 'demo',
                    'language' => str_contains($title, 'Indonesia') || str_contains($title, 'Cuaca') ? 'id' : 'en',
                ]
            );
            NewsSentiment::updateOrCreate(
                ['news_cache_id' => $news->id],
                [
                    'sentiment' => $sentiment,
                    'positive_count' => $positive,
                    'negative_count' => $negative,
                    'neutral_count' => $sentiment === 'Netral' ? 1 : 0,
                    'matched_positive' => [],
                    'matched_negative' => [],
                ]
            );
        }
    }

    /** Skor demo memberi isi awal bagi dashboard; tombol Hitung memperbarui memakai data/API terbaru. */
    private function seedRiskHistory(): void
    {
        $profiles = [
            'ID' => [48, 34, 26, 42],
            'CN' => [30, 28, 34, 45],
            'DE' => [38, 22, 18, 36],
            'AU' => [24, 26, 20, 22],
            'US' => [31, 35, 24, 32],
            'JP' => [44, 18, 21, 28],
            'SG' => [42, 20, 16, 24],
            'MY' => [46, 29, 25, 31],
            'IN' => [36, 52, 30, 39],
            'GB' => [40, 41, 22, 35],
        ];
        $weights = ['Cuaca' => 30, 'Inflasi' => 20, 'Berita' => 40, 'Mata Uang' => 10];

        foreach ($profiles as $code => $scores) {
            $country = Country::where('code', $code)->first();
            if (! $country) {
                continue;
            }

            for ($day = 6; $day >= 0; $day--) {
                [$weather, $inflation, $news, $currency] = array_map(
                    fn ($score) => max(0, min(100, $score + sin($day + $country->id) * 4)),
                    $scores
                );
                $total = round(($weather * .30) + ($inflation * .20) + ($news * .40) + ($currency * .10), 2);
                $level = $total <= 30 ? 'Rendah' : ($total <= 60 ? 'Sedang' : 'Tinggi');
                $calculatedAt = now()->subDays($day)->startOfDay()->addHours(8);

                $risk = RiskScore::updateOrCreate(
                    ['country_id' => $country->id, 'calculated_at' => $calculatedAt],
                    [
                        'weather_score' => round($weather, 2),
                        'inflation_score' => round($inflation, 2),
                        'currency_score' => round($currency, 2),
                        'news_score' => round($news, 2),
                        'total_score' => $total,
                        'risk_level' => $level,
                    ]
                );

                $componentScores = ['Cuaca' => $weather, 'Inflasi' => $inflation, 'Berita' => $news, 'Mata Uang' => $currency];
                foreach ($componentScores as $component => $normalized) {
                    $risk->components()->updateOrCreate(
                        ['component' => $component],
                        [
                            'raw_value' => $normalized,
                            'normalized_score' => round($normalized, 2),
                            'weight' => $weights[$component],
                            'weighted_score' => round($normalized * ($weights[$component] / 100), 2),
                            'notes' => 'Data demo awal; hitung ulang untuk memakai data terbaru.',
                        ]
                    );
                }
            }
        }
    }
}
