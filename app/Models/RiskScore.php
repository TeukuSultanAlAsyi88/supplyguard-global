<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskScore extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'country_id',
        'weather_score',
        'inflation_score',
        'currency_score',
        'news_score',
        'total_score',
        'risk_level',
        'calculated_at',
    ];

    /**
     * Konversi tipe data atribut.
     */
    protected $casts = [
        'country_id' => 'integer',
        'weather_score' => 'float',
        'inflation_score' => 'float',
        'currency_score' => 'float',
        'news_score' => 'float',
        'total_score' => 'float',
        'calculated_at' => 'datetime',
    ];

    /**
     * Negara pemilik skor risiko.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Rincian komponen perhitungan risiko.
     */
    public function components(): HasMany
    {
        return $this->hasMany(RiskComponent::class);
    }

    /**
     * Filter skor berdasarkan negara.
     */
    public function scopeCountry(
        Builder $query,
        mixed $countryId
    ): Builder {
        if (
            $countryId === null
            || $countryId === ''
        ) {
            return $query;
        }

        return $query->where(
            'country_id',
            (int) $countryId
        );
    }

    /**
     * Mengurutkan skor berdasarkan waktu perhitungan terbaru.
     */
    public function scopeLatestCalculated(
        Builder $query
    ): Builder {
        return $query
            ->orderByDesc('calculated_at')
            ->orderByDesc('id');
    }

    /**
     * Filter berdasarkan tingkat risiko.
     */
    public function scopeLevel(
        Builder $query,
        ?string $level
    ): Builder {
        $level = trim((string) $level);

        if ($level === '') {
            return $query;
        }

        $normalized = $this->normalizeRiskLevel(
            $level,
            0
        );

        return $query->where(
            'risk_level',
            $normalized
        );
    }

    /**
     * Mengambil data dengan risiko tinggi atau kritis.
     */
    public function scopeHighRisk(
        Builder $query
    ): Builder {
        return $query->where(
            function (Builder $builder) {
                $builder
                    ->where('total_score', '>=', 51)
                    ->orWhereIn(
                        'risk_level',
                        [
                            'Tinggi',
                            'Kritis',
                            'High',
                            'Critical',
                            'high',
                            'critical',
                        ]
                    );
            }
        );
    }

    /**
     * Mengambil satu skor terbaru untuk setiap negara.
     */
    public function scopeLatestPerCountry(
        Builder $query
    ): Builder {
        $latestIds = static::query()
            ->selectRaw('MAX(id)')
            ->groupBy('country_id');

        return $query->whereIn(
            'id',
            $latestIds
        );
    }

    /**
     * Label tingkat risiko yang konsisten.
     */
    public function getRiskLevelLabelAttribute(): string
    {
        return $this->normalizeRiskLevel(
            $this->risk_level,
            (float) $this->total_score
        );
    }

    /**
     * Class badge yang siap dipakai pada Blade.
     */
    public function getRiskBadgeClassAttribute(): string
    {
        return match ($this->risk_level_label) {
            'Rendah' => 'badge-low',
            'Sedang' => 'badge-medium',
            'Tinggi' => 'badge-high',
            'Kritis' => 'badge-critical',
            default => 'text-bg-secondary',
        };
    }

    /**
     * Seluruh skor komponen dalam bentuk array.
     */
    public function getComponentScoresAttribute(): array
    {
        return [
            'weather' => round(
                $this->boundedScore(
                    $this->weather_score
                ),
                2
            ),

            'inflation' => round(
                $this->boundedScore(
                    $this->inflation_score
                ),
                2
            ),

            'currency' => round(
                $this->boundedScore(
                    $this->currency_score
                ),
                2
            ),

            'news' => round(
                $this->boundedScore(
                    $this->news_score
                ),
                2
            ),
        ];
    }

    /**
     * Nama komponen dengan skor tertinggi.
     */
    public function getDominantComponentAttribute(): string
    {
        $scores = $this->component_scores;

        arsort($scores);

        return (string) array_key_first($scores);
    }

    /**
     * Label komponen dominan dalam Bahasa Indonesia.
     */
    public function getDominantComponentLabelAttribute(): string
    {
        return match ($this->dominant_component) {
            'weather' => 'Cuaca',
            'inflation' => 'Inflasi',
            'currency' => 'Nilai tukar',
            'news' => 'Berita dan geopolitik',
            default => 'Tidak tersedia',
        };
    }

    /**
     * Nilai komponen dominan.
     */
    public function getDominantComponentScoreAttribute(): float
    {
        return (float) (
            $this->component_scores[
                $this->dominant_component
            ] ?? 0
        );
    }

    /**
     * Rekomendasi keputusan bisnis berdasarkan skor risiko
     * dan faktor risiko yang paling dominan.
     */
    public function getDecisionRecommendationAttribute(): string
    {
        $level = $this->risk_level_label;
        $factor = $this->dominant_component_label;

        return match ($level) {
            'Kritis' =>
                "Risiko rantai pasok berada pada tingkat kritis. Tunda keputusan impor yang tidak mendesak, evaluasi negara pemasok alternatif, dan prioritaskan mitigasi pada faktor {$factor}.",

            'Tinggi' =>
                "Risiko rantai pasok tergolong tinggi. Siapkan pemasok, rute, atau jadwal pengiriman alternatif dan lakukan pemantauan intensif terhadap faktor {$factor}.",

            'Sedang' =>
                "Risiko rantai pasok tergolong sedang. Proses impor masih dapat dipertimbangkan dengan pengawasan berkala dan mitigasi khusus pada faktor {$factor}.",

            default =>
                "Risiko rantai pasok relatif rendah. Pengiriman dapat dipertimbangkan, tetapi perubahan pada faktor {$factor} tetap perlu dipantau.",
        };
    }

    /**
     * Ringkasan hasil analisis yang siap ditampilkan.
     */
    public function getAnalysisSummaryAttribute(): string
    {
        $countryName =
            $this->country?->name
            ?? 'negara ini';

        $score = number_format(
            (float) $this->total_score,
            2,
            ',',
            '.'
        );

        return "Skor risiko {$countryName} adalah {$score}/100 dengan tingkat {$this->risk_level_label}. Faktor dominan adalah {$this->dominant_component_label} ({$this->dominant_component_score}/100).";
    }

    /**
     * Menentukan apakah risiko memerlukan perhatian tinggi.
     */
    public function requiresImmediateAttention(): bool
    {
        return in_array(
            $this->risk_level_label,
            [
                'Tinggi',
                'Kritis',
            ],
            true
        );
    }

    /**
     * Menentukan apakah data skor memiliki seluruh
     * komponen utama yang diwajibkan.
     */
    public function hasCompleteComponents(): bool
    {
        return $this->weather_score !== null
            && $this->inflation_score !== null
            && $this->currency_score !== null
            && $this->news_score !== null;
    }

    /**
     * Menyamakan label tingkat risiko berbahasa
     * Inggris dan Indonesia.
     */
    private function normalizeRiskLevel(
        mixed $level,
        float $score
    ): string {
        $normalized = strtolower(
            trim((string) $level)
        );

        if (
            in_array(
                $normalized,
                [
                    'rendah',
                    'low',
                    'low risk',
                ],
                true
            )
        ) {
            return 'Rendah';
        }

        if (
            in_array(
                $normalized,
                [
                    'sedang',
                    'medium',
                    'moderate',
                    'medium risk',
                    'moderate risk',
                ],
                true
            )
        ) {
            return 'Sedang';
        }

        if (
            in_array(
                $normalized,
                [
                    'tinggi',
                    'high',
                    'high risk',
                ],
                true
            )
        ) {
            return 'Tinggi';
        }

        if (
            in_array(
                $normalized,
                [
                    'kritis',
                    'critical',
                    'critical risk',
                ],
                true
            )
        ) {
            return 'Kritis';
        }

        return match (true) {
            $score >= 76 => 'Kritis',
            $score >= 51 => 'Tinggi',
            $score >= 26 => 'Sedang',
            default => 'Rendah',
        };
    }

    /**
     * Membatasi nilai skor agar tetap berada
     * pada rentang 0 sampai 100.
     */
    private function boundedScore(
        mixed $score
    ): float {
        return min(
            max(
                (float) ($score ?? 0),
                0
            ),
            100
        );
    }
}