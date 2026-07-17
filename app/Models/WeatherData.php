<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherData extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model.
     */
    protected $table = 'weather_data';

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'country_id',
        'temperature',
        'apparent_temperature',
        'humidity',
        'precipitation',
        'precipitation_probability',
        'wind_speed',
        'wind_gust',
        'weather_code',
        'condition',
        'is_day',
        'storm_risk',
        'observed_at',
    ];

    /**
     * Konversi tipe data atribut.
     */
    protected $casts = [
        'country_id' => 'integer',
        'temperature' => 'float',
        'apparent_temperature' => 'float',
        'humidity' => 'float',
        'precipitation' => 'float',
        'precipitation_probability' => 'float',
        'wind_speed' => 'float',
        'wind_gust' => 'float',
        'weather_code' => 'integer',
        'storm_risk' => 'float',
        'is_day' => 'boolean',
        'observed_at' => 'datetime',
    ];

    /**
     * Negara pemilik data cuaca.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Filter data cuaca berdasarkan negara.
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
     * Mengurutkan data dari waktu pengamatan terbaru.
     */
    public function scopeLatestObserved(
        Builder $query
    ): Builder {
        return $query
            ->orderByDesc('observed_at')
            ->orderByDesc('id');
    }

    /**
     * Filter data berdasarkan rentang waktu pengamatan.
     */
    public function scopeObservedBetween(
        Builder $query,
        mixed $start,
        mixed $end
    ): Builder {
        if (!$start || !$end) {
            return $query;
        }

        return $query->whereBetween(
            'observed_at',
            [
                $start,
                $end,
            ]
        );
    }

    /**
     * Mengambil kondisi cuaca yang berpotensi mengganggu
     * aktivitas logistik dan transportasi.
     */
    public function scopePotentiallyDisruptive(
        Builder $query
    ): Builder {
        return $query->where(
            function (Builder $builder) {
                $builder
                    ->where('storm_risk', '>=', 50)
                    ->orWhere(
                        'precipitation_probability',
                        '>=',
                        70
                    )
                    ->orWhere(
                        'precipitation',
                        '>=',
                        20
                    )
                    ->orWhere(
                        'wind_speed',
                        '>=',
                        40
                    )
                    ->orWhere(
                        'wind_gust',
                        '>=',
                        60
                    );
            }
        );
    }

    /**
     * Skor dampak cuaca terhadap kegiatan logistik.
     *
     * Skor ini bukan skor risiko rantai pasok utama.
     * Nilai ini digunakan sebagai salah satu indikator dalam
     * analisis gangguan transportasi.
     */
    public function getWeatherDisruptionScoreAttribute(): float
    {
        $stormScore = $this->normalizePercentage(
            $this->storm_risk
        );

        $rainProbabilityScore =
            $this->normalizePercentage(
                $this->precipitation_probability
            );

        $precipitationScore = $this->normalizeByLimit(
            $this->precipitation,
            50
        );

        $windScore = $this->normalizeByLimit(
            $this->wind_speed,
            80
        );

        $gustScore = $this->normalizeByLimit(
            $this->wind_gust,
            120
        );

        $score =
            ($stormScore * 0.35)
            + ($rainProbabilityScore * 0.20)
            + ($precipitationScore * 0.15)
            + ($windScore * 0.15)
            + ($gustScore * 0.15);

        return round(
            min(
                max($score, 0),
                100
            ),
            2
        );
    }

    /**
     * Tingkat dampak cuaca terhadap transportasi.
     */
    public function getWeatherDisruptionLevelAttribute(): string
    {
        $score = $this->weather_disruption_score;

        return match (true) {
            $score >= 81 => 'Kritis',
            $score >= 61 => 'Tinggi',
            $score >= 31 => 'Sedang',
            default => 'Rendah',
        };
    }

    /**
     * Faktor cuaca dominan yang berpotensi mengganggu
     * transportasi atau kegiatan pelabuhan.
     */
    public function getWeatherDisruptionFactorsAttribute(): array
    {
        $factors = [];

        if (
            $this->normalizePercentage(
                $this->storm_risk
            ) >= 50
        ) {
            $factors[] = 'Risiko badai meningkat';
        }

        if (
            (float) $this->precipitation_probability
            >= 70
        ) {
            $factors[] = 'Peluang hujan tinggi';
        }

        if (
            (float) $this->precipitation
            >= 20
        ) {
            $factors[] = 'Curah hujan tinggi';
        }

        if (
            (float) $this->wind_speed
            >= 40
        ) {
            $factors[] = 'Kecepatan angin tinggi';
        }

        if (
            (float) $this->wind_gust
            >= 60
        ) {
            $factors[] = 'Hembusan angin kuat';
        }

        if (empty($factors)) {
            $factors[] =
                'Tidak ada gangguan cuaca dominan';
        }

        return $factors;
    }

    /**
     * Rekomendasi operasional berdasarkan dampak cuaca.
     */
    public function getWeatherRecommendationAttribute(): string
    {
        return match (
            $this->weather_disruption_level
        ) {
            'Kritis' =>
                'Tunda pengiriman yang tidak mendesak dan gunakan jalur atau pelabuhan alternatif.',

            'Tinggi' =>
                'Lakukan koordinasi dengan operator pelabuhan dan siapkan jadwal pengiriman alternatif.',

            'Sedang' =>
                'Pantau perubahan cuaca, kecepatan angin, dan jadwal keberangkatan sebelum pengiriman.',

            default =>
                'Kondisi cuaca relatif aman, tetapi pemantauan berkala tetap diperlukan.',
        };
    }

    /**
     * Label kondisi cuaca untuk tampilan.
     */
    public function getConditionLabelAttribute(): string
    {
        $condition = trim(
            (string) $this->condition
        );

        return $condition !== ''
            ? $condition
            : 'Tidak tersedia';
    }

    /**
     * Menentukan apakah kondisi cuaca masuk kategori ekstrem.
     */
    public function isExtreme(): bool
    {
        return $this->weather_disruption_score >= 61;
    }

    /**
     * Menormalisasi nilai persentase menjadi 0 sampai 100.
     *
     * Nilai 0 sampai 1 dianggap sebagai pecahan persentase.
     * Nilai di atas 1 dianggap sudah menggunakan skala 0–100.
     */
    private function normalizePercentage(
        mixed $value
    ): float {
        $number = (float) ($value ?? 0);

        if ($number >= 0 && $number <= 1) {
            $number *= 100;
        }

        return min(
            max($number, 0),
            100
        );
    }

    /**
     * Menormalisasi nilai berdasarkan batas maksimum.
     */
    private function normalizeByLimit(
        mixed $value,
        float $limit
    ): float {
        $number = max(
            (float) ($value ?? 0),
            0
        );

        if ($limit <= 0) {
            return 0;
        }

        return min(
            ($number / $limit) * 100,
            100
        );
    }
}