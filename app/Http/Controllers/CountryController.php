<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CountryService;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(Request $request, CountryService $service)
    {
        if (Country::count() === 0) {
            $service->syncCountries();
        }

        $query = Country::query()->with('latestRisk');
        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('code', 'like', '%'.$term.'%')
                ->orWhere('cca3', 'like', '%'.$term.'%'));
        }
        if ($request->filled('region')) {
            $query->where('region', $request->string('region'));
        }

        return view('countries.index', [
            'countries' => $query->orderBy('name')->paginate(20)->withQueryString(),
            'regions' => Country::whereNotNull('region')->distinct()->orderBy('region')->pluck('region'),
        ]);
    }

    public function show(Country $country, CountryService $countryService, WeatherService $weatherService)
    {
        $economics = $countryService->economicHistory($country, 10);
        $economic = $economics->sortByDesc('year')->first();
        $weather = $weatherService->current($country);
        $risk = $country->risks()->with('components')->latest('calculated_at')->first();

        return view('countries.show', compact('country', 'economic', 'economics', 'weather', 'risk'));
    }

    public function sync(CountryService $service)
    {
        $count = $service->syncCountries();
        return back()->with($count ? 'success' : 'error', $count
            ? "Berhasil menyinkronkan {$count} negara."
            : 'Sinkronisasi eksternal gagal. Data awal dari seeder tetap dapat digunakan; isi REST_COUNTRIES_API_KEY untuk REST Countries v5.');
    }
}
