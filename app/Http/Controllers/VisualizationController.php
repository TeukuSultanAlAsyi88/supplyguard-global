<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RiskScore;
use App\Services\CountryService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class VisualizationController extends Controller
{
    public function index(Request $request, CountryService $countryService, CurrencyService $currencyService)
    {
        $countries = Country::orderBy('name')->get();
        $country = $request->country ? Country::find($request->country) : Country::where('code', 'ID')->first();
        $economics = $country ? $countryService->economicHistory($country, 10) : collect();
        $risks = $country ? RiskScore::where('country_id', $country->id)->orderBy('calculated_at')->take(30)->get() : collect();
        $currencies = $country && $country->currency_code ? $currencyService->history('USD', $country->currency_code, 30) : collect();

        return view('visualization.index', compact('countries', 'country', 'economics', 'risks', 'currencies'));
    }
}
