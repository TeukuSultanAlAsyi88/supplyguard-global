<?php

namespace App\Services;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\WeatherData;
use Illuminate\Support\Collection;

class TransportationDisruptionService
{
    /**
     * Bobot indikator gangguan transportasi.
     *
     * Analisis ini merupakan indikator internal SupplyGuard,
     * bukan data kemacetan pelabuhan secara real-time.
     */
    private const WEATHER_WEIGHT = 0.40;
    private const NEWS_WEIGHT = 0.35;
    private const PORT_WEIGHT = 0.25;

    /**
     * Menganalisis potensi gangguan transportasi suatu negara.
     */
    public function analyze(Country $country): array
    {
        $weather = $country
            ->latestWeather()
            ->first();

        $recentNews = NewsCache::query()
            ->where('country_id', $country->id)
            ->whereNotNull('published_at')
            ->where(
                'published_at',
                '>=',
                now()->subDays(30)
            )
            ->latestPublished()
            ->limit(20)
            ->get();

        $portCount = Port::query()
            ->where(
                function ($query) use ($country) {
                    $query
                        ->where(
                            'country_id',
                            $country->id
                        )
                        ->orWhere(
                            'country_name',
                            $country->name
                        );
                }
            )
            ->count();

        $weatherScore = $this->weatherScore(
            $weather
        );

        $newsScore = $this->newsScore(
            $recentNews
        );

        $portScore = $this->portAvailabilityScore(
            $portCount
        );

        $totalScore = round(
            ($weatherScore * self::WEATHER_WEIGHT)
            + ($newsScore * self::NEWS_WEIGHT)
            + ($portScore * self::PORT_WEIGHT),
            2
        );

        $level = $this->level($totalScore);

        $components = [
            'weather' => [
                'label' => 'Gangguan cuaca',
                'score' => $weatherScore,
                'weight' => self::WEATHER_WEIGHT * 100,
                'weighted_score' => round(
                    $weatherScore
                    * self::WEATHER_WEIGHT,
                    2
                ),
            ],

            'news' => [
                'label' => 'Berita logistik dan geopolitik',
                'score' => $newsScore,
                'weight' => self::NEWS_WEIGHT * 100,
                'weighted_score' => round(
                    $newsScore
                    * self::NEWS_WEIGHT,
                    2
                ),
            ],

            'ports' => [
                'label' => 'Ketersediaan pelabuhan',
                'score' => $portScore,
                'weight' => self::PORT_WEIGHT * 100,
                'weighted_score' => round(
                    $portScore
                    * self::PORT_WEIGHT,
                    2
                ),
            ],
        ];

        $dominantComponent = collect($components)
            ->sortByDesc('weighted_score')
            ->keys()
            ->first();

        $factors = $this->factors(
            $weather,
            $recentNews,
            $portCount
        );

        return [
            'country_id' => $country->id,
            'country_name' => $country->name,
            'score' => $totalScore,
            'level' => $level,
            'badge_class' => $this->badgeClass($level),
            'components' => $components,
            'dominant_component' => $dominantComponent,
            'dominant_component_label' =>
                $components[$dominantComponent]['label']
                ?? 'Tidak tersedia',
            'factors' => $factors,
            'recommendation' => $this->recommendation(
                $level,
                $components[$dominantComponent]['label']
                    ?? 'faktor utama'
            ),
            'port_count' => $portCount,
            'news_count' => $recentNews->count(),
            'weather_available' => $weather !== null,
            'confidence' => $this->confidence(
                $weather,
                $recentNews,
                $portCount
            ),
            'calculated_at' => now()->toIso8601String(),
            'methodology' => [
                'weather_weight' =>
                    self::WEATHER_WEIGHT * 100,
                'news_weight' =>
                    self::NEWS_WEIGHT * 100,
                'port_weight' =>
                    self::PORT_WEIGHT * 100,
                'note' =>
                    'Indikator dihitung dari cuaca terbaru, berita 30 hari terakhir, dan ketersediaan pelabuhan. Nilai ini bukan data kemacetan pelabuhan real-time.',
            ],
        ];
    }

    /**
     * Mengambil skor gangguan cuaca.
     */
    private function weatherScore(
        ?WeatherData $weather
    ): float {
        if (!$weather) {
            return 0.0;
        }

        return round(
            min(
                max(
                    (float) $weather
                        ->weather_disruption_score,
                    0
                ),
                100
            ),
            2
        );
    }

    /**
     * Menghitung skor gangguan dari berita.
     *
     * Lima berita dengan skor tertinggi digunakan agar kejadian
     * penting tidak tertutup oleh banyak berita netral.
     */
    private function newsScore(
        Collection $news
    ): float {
        if ($news->isEmpty()) {
            return 0.0;
        }

        $highestScores = $news
            ->map(
                fn (NewsCache $item): float =>
                    (float) $item
                        ->transport_disruption_score
            )
            ->sortDesc()
            ->take(5);

        return round(
            min(
                max(
                    (float) $highestScores->avg(),
                    0
                ),
                100
            ),
            2
        );
    }

    /**
     * Mengubah jumlah pelabuhan menjadi skor risiko.
     *
     * Semakin sedikit pelabuhan yang tersedia, semakin tinggi
     * risiko ketergantungan transportasi.
     */
    private function portAvailabilityScore(
        int $portCount
    ): float {
        return match (true) {
            $portCount <= 0 => 100.0,
            $portCount === 1 => 80.0,
            $portCount === 2 => 60.0,
            $portCount === 3 => 45.0,
            $portCount <= 5 => 30.0,
            $portCount <= 9 => 15.0,
            default => 5.0,
        };
    }

    /**
     * Menentukan tingkat gangguan.
     */
    private function level(
        float $score
    ): string {
        return match (true) {
            $score >= 81 => 'Kritis',
            $score >= 61 => 'Tinggi',
            $score >= 31 => 'Sedang',
            default => 'Rendah',
        };
    }

    /**
     * Class badge untuk Blade.
     */
    private function badgeClass(
        string $level
    ): string {
        return match ($level) {
            'Rendah' => 'badge-low',
            'Sedang' => 'badge-medium',
            'Tinggi' => 'badge-high',
            'Kritis' => 'badge-critical',
            default => 'text-bg-secondary',
        };
    }

    /**
     * Mengumpulkan faktor penyebab utama.
     */
    private function factors(
        ?WeatherData $weather,
        Collection $news,
        int $portCount
    ): array {
        $factors = collect();

        if ($weather) {
            $factors = $factors->merge(
                $weather
                    ->weather_disruption_factors
            );
        } else {
            $factors->push(
                'Data cuaca terbaru belum tersedia'
            );
        }

        $newsFactors = $news
            ->sortByDesc(
                fn (NewsCache $item): float =>
                    (float) $item
                        ->transport_disruption_score
            )
            ->take(5)
            ->flatMap(
                fn (NewsCache $item): array =>
                    $item
                        ->transport_disruption_factors
            );

        $factors = $factors->merge(
            $newsFactors
        );

        $factors->push(
            match (true) {
                $portCount <= 0 =>
                    'Data pelabuhan belum tersedia',

                $portCount === 1 =>
                    'Ketergantungan pada satu pelabuhan',

                $portCount <= 3 =>
                    'Pilihan pelabuhan alternatif terbatas',

                default =>
                    "{$portCount} pelabuhan tersedia sebagai alternatif",
            }
        );

        return $factors
            ->map(
                fn (mixed $factor): string =>
                    trim((string) $factor)
            )
            ->filter()
            ->reject(
                fn (string $factor): bool =>
                    $factor ===
                    'Tidak ada gangguan cuaca dominan'
                    || $factor ===
                    'Tidak ada indikasi gangguan transportasi dominan'
            )
            ->unique()
            ->take(8)
            ->values()
            ->whenEmpty(
                fn (Collection $collection) =>
                    $collection->push(
                        'Tidak ada faktor gangguan dominan'
                    )
            )
            ->all();
    }

    /**
     * Rekomendasi keputusan operasional.
     */
    private function recommendation(
        string $level,
        string $dominantFactor
    ): string {
        return match ($level) {
            'Kritis' =>
                "Potensi gangguan transportasi berada pada tingkat kritis. Tunda pengiriman yang tidak mendesak, pilih pelabuhan atau rute alternatif, dan prioritaskan mitigasi pada {$dominantFactor}.",

            'Tinggi' =>
                "Potensi gangguan transportasi tergolong tinggi. Siapkan jadwal, pelabuhan, dan penyedia logistik alternatif serta pantau {$dominantFactor} secara intensif.",

            'Sedang' =>
                "Potensi gangguan transportasi tergolong sedang. Pengiriman masih dapat dipertimbangkan dengan pemantauan berkala dan mitigasi khusus pada {$dominantFactor}.",

            default =>
                "Potensi gangguan transportasi relatif rendah. Pengiriman dapat dipertimbangkan, tetapi perubahan {$dominantFactor} tetap perlu dipantau.",
        };
    }

    /**
     * Tingkat kepercayaan analisis berdasarkan kelengkapan data.
     */
    private function confidence(
        ?WeatherData $weather,
        Collection $news,
        int $portCount
    ): string {
        $availableSources = 0;

        if ($weather) {
            $availableSources++;
        }

        if ($news->isNotEmpty()) {
            $availableSources++;
        }

        if ($portCount > 0) {
            $availableSources++;
        }

        return match ($availableSources) {
            3 => 'Tinggi',
            2 => 'Sedang',
            default => 'Rendah',
        };
    }
}