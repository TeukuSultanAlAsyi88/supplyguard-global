<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index(Request $request, WeatherService $service)
    {
        $countries = Country::orderBy('name')->get();
        $country = $request->country ? Country::find($request->country) : Country::where('code', 'ID')->first();
        $weather = $country ? $service->current($country) : null;
        $mapWeather = $service->overview(50);
        if ($mapWeather->isEmpty()) {
            $mapWeather = $service->syncOverview(10);
        }

        return view('weather.index', compact('countries', 'country', 'weather', 'mapWeather'));
    }
}
