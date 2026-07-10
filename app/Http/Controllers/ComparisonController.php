<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CountryComparison;
use App\Services\CountryService;
use App\Services\CurrencyService;
use App\Services\RiskScoringService;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function index(Request $request, CountryService $countriesService, WeatherService $weatherService, CurrencyService $currencyService, RiskScoringService $riskService)
    {
        $countries = Country::orderBy('name')->get();
        $a = $request->a ? Country::find($request->a) : null;
        $b = $request->b ? Country::find($request->b) : null;
        $result = null;

        if ($a && $b && $a->id !== $b->id) {
            $make = fn (Country $country) => [
                'country' => $country,
                'economic' => $countriesService->economic($country),
                'weather' => $weatherService->current($country),
                'currency' => $country->currency_code ? $currencyService->rate('USD', $country->currency_code) : null,
                'risk' => $riskService->calculate($country),
            ];
            $result = ['a' => $make($a), 'b' => $make($b)];

            CountryComparison::create([
                'user_id' => auth()->id(),
                'country_a_id' => $a->id,
                'country_b_id' => $b->id,
                'result' => [
                    'a_score' => $result['a']['risk']->total_score,
                    'b_score' => $result['b']['risk']->total_score,
                    'a_currency' => $result['a']['currency']?->rate,
                    'b_currency' => $result['b']['currency']?->rate,
                ],
            ]);
        }

        return view('comparison.index', compact('countries', 'a', 'b', 'result'));
    }
}
