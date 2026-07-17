<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'name',
        'official_name',
        'code',
        'cca3',
        'region',
        'subregion',
        'capital',
        'currency_code',
        'currency_name',
        'language',
        'flag_url',
        'latitude',
        'longitude',
        'population',
    ];

    /**
     * Konversi tipe data atribut.
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'population' => 'integer',
    ];

    /**
     * Riwayat data ekonomi negara.
     */
    public function economics(): HasMany
    {
        return $this->hasMany(CountryEconomic::class);
    }

    /**
     * Data ekonomi terbaru negara.
     */
    public function latestEconomic(): HasOne
    {
        return $this->hasOne(CountryEconomic::class)
            ->ofMany([
                'year' => 'max',
                'id' => 'max',
            ]);
    }

    /**
     * Seluruh riwayat skor risiko negara.
     */
    public function risks(): HasMany
    {
        return $this->hasMany(RiskScore::class);
    }

    /**
     * Skor risiko terbaru negara.
     */
    public function latestRisk(): HasOne
    {
        return $this->hasOne(RiskScore::class)
            ->ofMany([
                'calculated_at' => 'max',
                'id' => 'max',
            ]);
    }

    /**
     * Pelabuhan yang berada di negara ini.
     */
    public function ports(): HasMany
    {
        return $this->hasMany(Port::class);
    }

    /**
     * Riwayat data cuaca negara.
     */
    public function weatherHistory(): HasMany
    {
        return $this->hasMany(WeatherData::class);
    }

    /**
     * Data cuaca terbaru negara.
     */
    public function latestWeather(): HasOne
    {
        return $this->hasOne(WeatherData::class)
            ->ofMany([
                'observed_at' => 'max',
                'id' => 'max',
            ]);
    }

    /**
     * Berita yang berkaitan dengan negara ini.
     */
    public function news(): HasMany
    {
        return $this->hasMany(NewsCache::class);
    }

    /**
     * Daftar pemantauan yang menggunakan negara ini.
     */
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Pencarian berdasarkan nama, nama resmi, ISO2, ISO3,
     * ibu kota, atau kode mata uang.
     */
    public function scopeSearch(
        Builder $query,
        ?string $keyword
    ): Builder {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($keyword) {
            $builder
                ->where('name', 'like', "%{$keyword}%")
                ->orWhere('official_name', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%")
                ->orWhere('cca3', 'like', "%{$keyword}%")
                ->orWhere('capital', 'like', "%{$keyword}%")
                ->orWhere('currency_code', 'like', "%{$keyword}%");
        });
    }

    /**
     * Filter negara berdasarkan wilayah.
     */
    public function scopeRegion(
        Builder $query,
        ?string $region
    ): Builder {
        $region = trim((string) $region);

        if ($region === '') {
            return $query;
        }

        return $query->where('region', $region);
    }

    /**
     * Menentukan apakah koordinat negara tersedia.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null;
    }

    /**
     * Menghasilkan label kode negara yang mudah ditampilkan.
     */
    public function getCodeLabelAttribute(): string
    {
        $code = $this->code ?: '-';
        $cca3 = $this->cca3 ?: '-';

        return "{$code} / {$cca3}";
    }
}