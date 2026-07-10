<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\CountryService;
use App\Services\CurrencyService;
use App\Services\RiskScoringService;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class ComparisonApiController extends Controller
{
    public function compare(Request $request, CountryService $countries, WeatherService $weather, CurrencyService $currency, RiskScoringService $risk)
    {
        $validated = $request->validate(['a' => 'required|different:b|exists:countries,id', 'b' => 'required|exists:countries,id']);
        $a = Country::findOrFail($validated['a']);
        $b = Country::findOrFail($validated['b']);
        $make = fn (Country $country) => [
            'country' => $country,
            'economic' => $countries->economic($country),
            'weather' => $weather->current($country),
            'currency' => $country->currency_code ? $currency->rate('USD', $country->currency_code) : null,
            'risk' => $risk->calculate($country),
        ];
        return response()->json(['success' => true, 'data' => ['a' => $make($a), 'b' => $make($b)]]);
    }
}
