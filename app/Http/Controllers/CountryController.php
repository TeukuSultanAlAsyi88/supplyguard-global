<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CountryService;
use App\Services\TransportationDisruptionService;
use App\Services\WeatherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CountryController extends Controller
{
    /**
     * Menampilkan daftar negara.
     */
    public function index(
        Request $request,
        CountryService $countryService
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Sinkronisasi awal
        |--------------------------------------------------------------------------
        |
        | Sinkronisasi hanya dijalankan ketika tabel countries masih kosong.
        | Kegagalan sinkronisasi tidak membuat halaman berhenti karena data
        | awal tetap dapat diisi melalui seeder atau proses sinkronisasi manual.
        |
        */

        if (!Country::query()->exists()) {
            try {
                $countryService->syncCountries();
            } catch (Throwable $exception) {
                Log::warning(
                    'Sinkronisasi awal data negara gagal.',
                    [
                        'message' => $exception->getMessage(),
                    ]
                );
            }
        }

        $keyword = trim(
            (string) $request->query('q', '')
        );

        $selectedRegion = trim(
            (string) $request->query('region', '')
        );

        /*
        |--------------------------------------------------------------------------
        | Query negara
        |--------------------------------------------------------------------------
        */

        $countries = Country::query()
            ->with([
                'latestRisk',
            ])
            ->search($keyword)
            ->region($selectedRegion)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $regions = Country::query()
            ->whereNotNull('region')
            ->where('region', '<>', '')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return view(
            'countries.index',
            compact(
                'countries',
                'regions',
                'keyword',
                'selectedRegion'
            )
        );
    }

    /**
     * Menampilkan detail satu negara.
     */
    public function show(
        Country $country,
        CountryService $countryService,
        WeatherService $weatherService,
        TransportationDisruptionService $transportService
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Muat relasi utama
        |--------------------------------------------------------------------------
        */

        $country->loadMissing([
            'latestEconomic',
            'latestWeather',
            'latestRisk.components',
        ]);

        $country->loadCount([
            'ports',
            'news',
            'risks',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Riwayat ekonomi
        |--------------------------------------------------------------------------
        */

        $economics = $countryService
            ->economicHistory(
                $country,
                10
            );

        $economic = $economics
            ->sortByDesc('year')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Data cuaca
        |--------------------------------------------------------------------------
        |
        | WeatherService tetap digunakan agar mekanisme sinkronisasi dan cache
        | yang sudah ada pada project tidak berubah.
        |
        */

        try {
            $weather = $weatherService->current(
                $country
            );
        } catch (Throwable $exception) {
            Log::warning(
                'Pengambilan cuaca negara gagal.',
                [
                    'country_id' => $country->id,
                    'message' => $exception->getMessage(),
                ]
            );

            $weather = $country->latestWeather;
        }

        /*
        |--------------------------------------------------------------------------
        | Skor risiko terbaru
        |--------------------------------------------------------------------------
        */

        $risk = $country
            ->risks()
            ->with('components')
            ->latestCalculated()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Analisis gangguan transportasi
        |--------------------------------------------------------------------------
        */

        $transportAnalysis = $transportService
            ->analyze(
                $country
            );

        /*
        |--------------------------------------------------------------------------
        | Data pendukung detail negara
        |--------------------------------------------------------------------------
        */

        $ports = $country
            ->ports()
            ->orderBy('name')
            ->limit(10)
            ->get();

        $recentNews = $country
            ->news()
            ->latestPublished()
            ->limit(6)
            ->get();

        $summary = [
            'ports_count' => $country->ports_count,
            'news_count' => $country->news_count,
            'risk_history_count' => $country->risks_count,
            'has_weather' => $weather !== null,
            'has_economic' => $economic !== null,
            'has_risk' => $risk !== null,
        ];

        return view(
            'countries.show',
            compact(
                'country',
                'economic',
                'economics',
                'weather',
                'risk',
                'transportAnalysis',
                'ports',
                'recentNews',
                'summary'
            )
        );
    }

    /**
     * Menyinkronkan data negara secara manual.
     */
    public function sync(
        CountryService $countryService
    ): RedirectResponse {
        try {
            $count = (int) $countryService
                ->syncCountries();

            if ($count > 0) {
                return back()->with(
                    'success',
                    "Berhasil menyinkronkan {$count} negara."
                );
            }

            return back()->with(
                'error',
                'Sinkronisasi data negara belum berhasil. Silakan periksa koneksi internet dan konfigurasi layanan eksternal.'
            );
        } catch (Throwable $exception) {
            Log::error(
                'Sinkronisasi data negara gagal.',
                [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat menyinkronkan data negara. Data yang sudah tersimpan tetap aman.'
            );
        }
    }
}