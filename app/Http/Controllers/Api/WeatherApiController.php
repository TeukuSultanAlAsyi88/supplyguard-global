<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\WeatherData;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherApiController extends Controller
{
    public function index(Request $request, WeatherService $service)
    {
        $data = $request->boolean('sync') ? $service->syncOverview($request->integer('limit', 20)) : $service->overview($request->integer('limit', 50));
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function show(Request $request, Country $country, WeatherService $service)
    {
        return response()->json(['success' => true, 'data' => $service->current($country, $request->boolean('force'))?->load('country')]);
    }

    public function history(Request $request, Country $country)
    {
        $data = WeatherData::where('country_id', $country->id)->latest('observed_at')->limit($request->integer('limit', 48))->get();
        return response()->json(['success' => true, 'data' => $data]);
    }
}
