<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PortController extends Controller
{
    /**
     * Menampilkan dashboard lokasi pelabuhan.
     */
    public function index(Request $request): View
    {
        $keyword = trim(
            (string) $request->query('q', '')
        );

        $selectedCountry = trim(
            (string) $request->query('country', '')
        );

        /*
        |--------------------------------------------------------------------------
        | Query utama pelabuhan
        |--------------------------------------------------------------------------
        */

        $query = Port::query()
            ->with([
                'country:id,name,code,cca3',
            ]);

        $this->applySearchFilter(
            $query,
            $keyword
        );

        $this->applyCountryFilter(
            $query,
            $selectedCountry
        );

        /*
        |--------------------------------------------------------------------------
        | Data tabel
        |--------------------------------------------------------------------------
        */

        $filteredPortCount = (clone $query)
            ->count();

        $ports = (clone $query)
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Data peta
        |--------------------------------------------------------------------------
        |
        | Hanya pelabuhan dengan koordinat valid yang dikirim ke peta.
        | Batas 1.000 marker dipertahankan agar browser tetap ringan.
        |
        */

        $mapPorts = (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween(
                'latitude',
                [
                    -90,
                    90,
                ]
            )
            ->whereBetween(
                'longitude',
                [
                    -180,
                    180,
                ]
            )
            ->orderBy('name')
            ->limit(1000)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Pilihan negara pada filter
        |--------------------------------------------------------------------------
        |
        | Menggabungkan country_name pada tabel ports dan relasi countries.
        | Ini membuat filter tetap lengkap ketika sebagian data hasil import
        | hanya memiliki country_id.
        |
        */

        $portCountries = $this->getPortCountries();

        /*
        |--------------------------------------------------------------------------
        | Ringkasan data
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'total_ports' => Port::query()->count(),
            'filtered_ports' => $filteredPortCount,
            'mapped_ports' => $mapPorts->count(),
            'countries' => $portCountries->count(),
        ];

        $latestImportedAt = Port::query()
            ->max('imported_at');

        $mapCenter = $this->calculateMapCenter(
            $mapPorts
        );

        return view(
            'ports.index',
            compact(
                'ports',
                'mapPorts',
                'portCountries',
                'statistics',
                'latestImportedAt',
                'mapCenter',
                'keyword',
                'selectedCountry'
            )
        );
    }

    /**
     * Menerapkan pencarian pelabuhan.
     */
    private function applySearchFilter(
        Builder $query,
        string $keyword
    ): void {
        if ($keyword === '') {
            return;
        }

        $query->where(
            function (Builder $builder) use ($keyword) {
                $builder
                    ->where(
                        'name',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'country_name',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'city',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'unlocode',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'wpi_number',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'port_type',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'harbor_size',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'harbor_type',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhere(
                        'status',
                        'like',
                        "%{$keyword}%"
                    )
                    ->orWhereHas(
                        'country',
                        function (
                            Builder $countryQuery
                        ) use ($keyword) {
                            $countryQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$keyword}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$keyword}%"
                                )
                                ->orWhere(
                                    'cca3',
                                    'like',
                                    "%{$keyword}%"
                                );
                        }
                    );
            }
        );
    }

    /**
     * Menerapkan filter negara.
     */
    private function applyCountryFilter(
        Builder $query,
        string $country
    ): void {
        if ($country === '') {
            return;
        }

        $query->where(
            function (Builder $builder) use ($country) {
                $builder
                    ->where(
                        'country_name',
                        $country
                    )
                    ->orWhereHas(
                        'country',
                        function (
                            Builder $countryQuery
                        ) use ($country) {
                            $countryQuery->where(
                                'name',
                                $country
                            );
                        }
                    );
            }
        );
    }

    /**
     * Mengambil daftar negara yang memiliki data pelabuhan.
     */
    private function getPortCountries(): Collection
    {
        $countriesFromPorts = Port::query()
            ->whereNotNull('country_name')
            ->where('country_name', '<>', '')
            ->distinct()
            ->pluck('country_name');

        $countriesFromRelations = Country::query()
            ->whereHas('ports')
            ->pluck('name');

        return $countriesFromPorts
            ->merge($countriesFromRelations)
            ->map(
                fn (mixed $country): string =>
                    trim((string) $country)
            )
            ->filter(
                fn (string $country): bool =>
                    $country !== ''
            )
            ->unique(
                fn (string $country): string =>
                    mb_strtolower($country)
            )
            ->sort(
                SORT_NATURAL
                | SORT_FLAG_CASE
            )
            ->values();
    }

    /**
     * Menentukan titik tengah peta berdasarkan marker yang tampil.
     */
    private function calculateMapCenter(
        Collection $ports
    ): array {
        if ($ports->isEmpty()) {
            return [
                'latitude' => 20.0,
                'longitude' => 0.0,
                'zoom' => 2,
            ];
        }

        $latitude = (float) $ports->avg(
            fn (Port $port): float =>
                (float) $port->latitude
        );

        $longitude = (float) $ports->avg(
            fn (Port $port): float =>
                (float) $port->longitude
        );

        return [
            'latitude' => round(
                $latitude,
                6
            ),

            'longitude' => round(
                $longitude,
                6
            ),

            'zoom' => $ports->count() === 1
                ? 8
                : (
                    $ports->count() <= 10
                        ? 5
                        : 2
                ),
        ];
    }
}