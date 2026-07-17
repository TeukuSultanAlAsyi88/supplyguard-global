<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class NewsCache extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan model.
     */
    protected $table = 'news_cache';

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'country_id',
        'title',
        'description',
        'url',
        'image_url',
        'source',
        'published_at',
        'sentiment',
        'positive_score',
        'negative_score',
        'query',
        'language',
    ];

    /**
     * Konversi tipe data atribut.
     */
    protected $casts = [
        'country_id' => 'integer',
        'published_at' => 'datetime',
        'positive_score' => 'integer',
        'negative_score' => 'integer',
    ];

    /**
     * Negara yang berkaitan dengan berita.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Hasil analisis sentimen berita.
     */
    public function analysis(): HasOne
    {
        return $this->hasOne(
            NewsSentiment::class,
            'news_cache_id'
        );
    }

    /**
     * Pencarian berita berdasarkan judul, deskripsi,
     * sumber, query, bahasa, dan nama negara.
     */
    public function scopeSearch(
        Builder $query,
        ?string $keyword
    ): Builder {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->where(
            function (Builder $builder) use ($keyword) {
                $builder
                    ->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('source', 'like', "%{$keyword}%")
                    ->orWhere('query', 'like', "%{$keyword}%")
                    ->orWhere('language', 'like', "%{$keyword}%")
                    ->orWhereHas(
                        'country',
                        function (Builder $countryQuery) use ($keyword) {
                            $countryQuery
                                ->where('name', 'like', "%{$keyword}%")
                                ->orWhere('code', 'like', "%{$keyword}%")
                                ->orWhere('cca3', 'like', "%{$keyword}%");
                        }
                    );
            }
        );
    }

    /**
     * Filter berita berdasarkan negara.
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
     * Filter berita berdasarkan sentimen.
     */
    public function scopeSentiment(
        Builder $query,
        ?string $sentiment
    ): Builder {
        $sentiment = trim((string) $sentiment);

        if ($sentiment === '') {
            return $query;
        }

        $normalized = $this->normalizeSentimentValue(
            $sentiment
        );

        return $query->where(
            'sentiment',
            $normalized
        );
    }

    /**
     * Mengurutkan berita dari yang paling baru.
     */
    public function scopeLatestPublished(
        Builder $query
    ): Builder {
        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * Hanya mengambil berita dengan sentimen negatif.
     */
    public function scopeNegative(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'sentiment',
            [
                'Negatif',
                'Negative',
                'negative',
                'negatif',
            ]
        );
    }

    /**
     * Hanya mengambil berita yang berpotensi berkaitan
     * dengan gangguan transportasi dan rantai pasok.
     */
    public function scopeTransportDisruption(
        Builder $query
    ): Builder {
        $keywords = $this->transportDisruptionKeywords();

        return $query->where(
            function (Builder $builder) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $builder
                        ->orWhere(
                            'title',
                            'like',
                            "%{$keyword}%"
                        )
                        ->orWhere(
                            'description',
                            'like',
                            "%{$keyword}%"
                        );
                }
            }
        );
    }

    /**
     * Label sentimen yang konsisten untuk tampilan.
     */
    public function getSentimentLabelAttribute(): string
    {
        return $this->normalizeSentimentValue(
            $this->sentiment
        );
    }

    /**
     * Class badge sentimen yang siap dipakai pada Blade.
     */
    public function getSentimentBadgeClassAttribute(): string
    {
        return match ($this->sentiment_label) {
            'Positif' => 'text-bg-success',
            'Negatif' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }

    /**
     * Skor dominasi sentimen negatif.
     */
    public function getNegativeIntensityAttribute(): float
    {
        $positive = max(
            (int) ($this->positive_score ?? 0),
            0
        );

        $negative = max(
            (int) ($this->negative_score ?? 0),
            0
        );

        $total = $positive + $negative;

        if ($total === 0) {
            return $this->sentiment_label === 'Negatif'
                ? 100.0
                : 0.0;
        }

        return round(
            min(
                max(
                    ($negative / $total) * 100,
                    0
                ),
                100
            ),
            2
        );
    }

    /**
     * Skor indikasi gangguan transportasi dari isi berita.
     *
     * Nilai ini merupakan indikator berita, bukan skor risiko
     * rantai pasok utama. Skor ini nantinya dapat digabungkan
     * dengan cuaca dan ketersediaan pelabuhan.
     */
    public function getTransportDisruptionScoreAttribute(): float
    {
        $text = $this->normalizedContent();

        $matchedKeywords = collect(
            $this->transportDisruptionKeywords()
        )
            ->filter(
                fn (string $keyword): bool =>
                    Str::contains(
                        $text,
                        Str::lower($keyword)
                    )
            )
            ->unique()
            ->values();

        $keywordScore = min(
            $matchedKeywords->count() * 12,
            60
        );

        $negativeSentimentScore =
            $this->sentiment_label === 'Negatif'
                ? 20
                : (
                    $this->sentiment_label === 'Netral'
                        ? 5
                        : 0
                );

        $negativeIntensityScore =
            min(
                $this->negative_intensity * 0.20,
                20
            );

        $score =
            $keywordScore
            + $negativeSentimentScore
            + $negativeIntensityScore;

        return round(
            min(
                max($score, 0),
                100
            ),
            2
        );
    }

    /**
     * Tingkat indikasi gangguan transportasi dari berita.
     */
    public function getTransportDisruptionLevelAttribute(): string
    {
        $score = $this->transport_disruption_score;

        return match (true) {
            $score >= 81 => 'Kritis',
            $score >= 61 => 'Tinggi',
            $score >= 31 => 'Sedang',
            default => 'Rendah',
        };
    }

    /**
     * Faktor gangguan transportasi yang ditemukan dalam berita.
     */
    public function getTransportDisruptionFactorsAttribute(): array
    {
        $text = $this->normalizedContent();

        $labels = [
            'delay' => 'Keterlambatan pengiriman',
            'delayed' => 'Keterlambatan pengiriman',
            'disruption' => 'Gangguan operasional',
            'congestion' => 'Kemacetan pelabuhan',
            'strike' => 'Aksi mogok',
            'closed' => 'Penutupan fasilitas',
            'closure' => 'Penutupan fasilitas',
            'blockade' => 'Blokade jalur transportasi',
            'war' => 'Konflik atau perang',
            'conflict' => 'Konflik geopolitik',
            'storm' => 'Gangguan badai',
            'flood' => 'Gangguan banjir',
            'shipping' => 'Gangguan aktivitas pelayaran',
            'port' => 'Gangguan aktivitas pelabuhan',
            'logistics' => 'Gangguan logistik',
            'supply chain' => 'Gangguan rantai pasok',
            'mogok' => 'Aksi mogok',
            'terlambat' => 'Keterlambatan pengiriman',
            'keterlambatan' => 'Keterlambatan pengiriman',
            'kemacetan' => 'Kemacetan pelabuhan',
            'ditutup' => 'Penutupan fasilitas',
            'penutupan' => 'Penutupan fasilitas',
            'blokade' => 'Blokade jalur transportasi',
            'perang' => 'Konflik atau perang',
            'konflik' => 'Konflik geopolitik',
            'badai' => 'Gangguan badai',
            'banjir' => 'Gangguan banjir',
            'pelayaran' => 'Gangguan aktivitas pelayaran',
            'pelabuhan' => 'Gangguan aktivitas pelabuhan',
            'logistik' => 'Gangguan logistik',
            'rantai pasok' => 'Gangguan rantai pasok',
        ];

        $factors = collect($labels)
            ->filter(
                fn (string $label, string $keyword): bool =>
                    Str::contains(
                        $text,
                        Str::lower($keyword)
                    )
            )
            ->values()
            ->unique()
            ->all();

        if (empty($factors)) {
            $factors[] =
                'Tidak ada indikasi gangguan transportasi dominan';
        }

        return $factors;
    }

    /**
     * Rekomendasi keputusan berdasarkan berita.
     */
    public function getTransportRecommendationAttribute(): string
    {
        return match (
            $this->transport_disruption_level
        ) {
            'Kritis' =>
                'Tunda pengiriman yang tidak mendesak dan gunakan pelabuhan atau jalur alternatif.',

            'Tinggi' =>
                'Siapkan rute pengiriman alternatif dan lakukan konfirmasi operasional dengan penyedia logistik.',

            'Sedang' =>
                'Pantau perkembangan berita dan jadwal keberangkatan sebelum proses impor dilanjutkan.',

            default =>
                'Belum ditemukan indikasi gangguan besar, tetapi pemantauan berita tetap diperlukan.',
        };
    }

    /**
     * Menentukan apakah berita termasuk indikasi
     * gangguan transportasi yang perlu diperhatikan.
     */
    public function isTransportDisruption(): bool
    {
        return $this->transport_disruption_score >= 31;
    }

    /**
     * Menentukan apakah gambar berita tersedia.
     */
    public function hasImage(): bool
    {
        return filter_var(
            $this->image_url,
            FILTER_VALIDATE_URL
        ) !== false;
    }

    /**
     * URL gambar yang aman untuk tampilan.
     */
    public function getDisplayImageAttribute(): ?string
    {
        return $this->hasImage()
            ? $this->image_url
            : null;
    }

    /**
     * Menentukan apakah tautan berita tersedia.
     */
    public function hasValidUrl(): bool
    {
        return filter_var(
            $this->url,
            FILTER_VALIDATE_URL
        ) !== false;
    }

    /**
     * Ringkasan singkat untuk kartu berita.
     */
    public function getShortDescriptionAttribute(): string
    {
        $description = trim(
            strip_tags(
                (string) $this->description
            )
        );

        return $description !== ''
            ? Str::limit(
                $description,
                160
            )
            : 'Deskripsi berita tidak tersedia.';
    }

    /**
     * Normalisasi nilai sentimen.
     */
    private function normalizeSentimentValue(
        mixed $sentiment
    ): string {
        $normalized = Str::lower(
            trim((string) $sentiment)
        );

        return match ($normalized) {
            'positif',
            'positive' => 'Positif',

            'negatif',
            'negative' => 'Negatif',

            default => 'Netral',
        };
    }

    /**
     * Isi berita yang sudah dinormalisasi.
     */
    private function normalizedContent(): string
    {
        return Str::lower(
            trim(
                implode(
                    ' ',
                    [
                        (string) $this->title,
                        (string) $this->description,
                        (string) $this->query,
                    ]
                )
            )
        );
    }

    /**
     * Daftar kata kunci gangguan transportasi.
     */
    private function transportDisruptionKeywords(): array
    {
        return [
            'delay',
            'delayed',
            'disruption',
            'congestion',
            'strike',
            'closed',
            'closure',
            'blockade',
            'war',
            'conflict',
            'storm',
            'flood',
            'shipping',
            'port',
            'logistics',
            'supply chain',
            'mogok',
            'terlambat',
            'keterlambatan',
            'kemacetan',
            'ditutup',
            'penutupan',
            'blokade',
            'perang',
            'konflik',
            'badai',
            'banjir',
            'pelayaran',
            'pelabuhan',
            'logistik',
            'rantai pasok',
        ];
    }
}