<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Port extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'country_id',
        'name',
        'unlocode',
        'wpi_number',
        'city',
        'country_name',
        'latitude',
        'longitude',
        'port_type',
        'harbor_size',
        'harbor_type',
        'status',
        'data_source',
        'imported_at',
    ];

    /**
     * Konversi tipe data atribut.
     */
    protected $casts = [
        'country_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'imported_at' => 'datetime',
    ];

    /**
     * Negara tempat pelabuhan berada.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Pencarian pelabuhan berdasarkan nama, kota, kode,
     * negara, jenis pelabuhan, ukuran, dan status.
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
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('unlocode', 'like', "%{$keyword}%")
                    ->orWhere('wpi_number', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('country_name', 'like', "%{$keyword}%")
                    ->orWhere('port_type', 'like', "%{$keyword}%")
                    ->orWhere('harbor_size', 'like', "%{$keyword}%")
                    ->orWhere('harbor_type', 'like', "%{$keyword}%")
                    ->orWhere('status', 'like', "%{$keyword}%");
            }
        );
    }

    /**
     * Filter berdasarkan negara.
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
     * Filter berdasarkan status pelabuhan.
     */
    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {
        $status = trim((string) $status);

        if ($status === '') {
            return $query;
        }

        return $query->where(
            'status',
            $status
        );
    }

    /**
     * Hanya mengambil pelabuhan yang mempunyai koordinat.
     */
    public function scopeWithCoordinates(
        Builder $query
    ): Builder {
        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    /**
     * Menentukan apakah koordinat pelabuhan tersedia.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null;
    }

    /**
     * Nama lokasi pelabuhan yang mudah ditampilkan.
     */
    public function getLocationLabelAttribute(): string
    {
        $parts = collect([
            $this->city,
            $this->country?->name
                ?? $this->country_name,
        ])
            ->filter(
                fn (mixed $value): bool =>
                    trim((string) $value) !== ''
            )
            ->unique()
            ->values();

        return $parts->isNotEmpty()
            ? $parts->implode(', ')
            : '-';
    }

    /**
     * Kode pelabuhan yang paling relevan.
     */
    public function getCodeLabelAttribute(): string
    {
        if (
            trim((string) $this->unlocode) !== ''
        ) {
            return (string) $this->unlocode;
        }

        if (
            trim((string) $this->wpi_number) !== ''
        ) {
            return (string) $this->wpi_number;
        }

        return '-';
    }

    /**
     * Label status yang lebih rapi untuk tampilan.
     */
    public function getStatusLabelAttribute(): string
    {
        $status = trim((string) $this->status);

        if ($status === '') {
            return 'Tidak tersedia';
        }

        return str($status)
            ->replace([
                '_',
                '-',
            ], ' ')
            ->title()
            ->toString();
    }

    /**
     * Label jenis pelabuhan untuk tampilan.
     */
    public function getTypeLabelAttribute(): string
    {
        $type = trim((string) $this->port_type);

        if ($type === '') {
            return 'Tidak tersedia';
        }

        return str($type)
            ->replace([
                '_',
                '-',
            ], ' ')
            ->title()
            ->toString();
    }
}