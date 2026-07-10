<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\Request;

class CountryApiController extends Controller
{
    public function index(Request $request)
    {
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

        return response()->json(['success' => true, 'data' => $query->orderBy('name')->paginate($request->integer('per_page', 20))]);
    }

    public function regions()
    {
        return response()->json(['success' => true, 'data' => Country::whereNotNull('region')->distinct()->orderBy('region')->pluck('region')]);
    }

    public function show(Country $country)
    {
        return response()->json(['success' => true, 'data' => $country->load(['latestEconomic', 'latestRisk', 'latestWeather'])]);
    }

    public function economics(Country $country, CountryService $service)
    {
        return response()->json(['success' => true, 'data' => $service->economic($country)]);
    }

    public function economicHistory(Request $request, Country $country, CountryService $service)
    {
        return response()->json(['success' => true, 'data' => $service->economicHistory($country, $request->integer('years', 10))]);
    }

    public function weather(Country $country)
    {
        return response()->json(['success' => true, 'data' => $country->latestWeather]);
    }

    public function ports(Country $country)
    {
        return response()->json(['success' => true, 'data' => $country->ports()->orderBy('name')->paginate(50)]);
    }

    public function riskHistory(Country $country)
    {
        return response()->json(['success' => true, 'data' => $country->risks()->with('components')->latest('calculated_at')->limit(50)->get()]);
    }
}
