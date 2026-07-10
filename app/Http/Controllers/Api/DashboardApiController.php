<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{ApiLog, Country, CurrencyRate, NewsCache, Port, RiskScore, WeatherData};

class DashboardApiController extends Controller
{
    public function summary()
    {
        return response()->json(['success' => true, 'data' => [
            'countries' => Country::count(),
            'ports' => Port::count(),
            'news' => NewsCache::count(),
            'risk_records' => RiskScore::count(),
            'weather_records' => WeatherData::count(),
            'currency_records' => CurrencyRate::count(),
        ]]);
    }

    public function charts()
    {
        $latestIds = RiskScore::selectRaw('MAX(id)')->groupBy('country_id');
        return response()->json(['success' => true, 'data' => [
            'risk_levels' => RiskScore::whereIn('id', $latestIds)->get()->countBy('risk_level'),
            'news_sentiment' => NewsCache::latest('published_at')->limit(100)->get()->countBy('sentiment'),
            'ports_by_country' => Port::selectRaw('country_name, COUNT(*) total')->groupBy('country_name')->orderByDesc('total')->limit(10)->get(),
        ]]);
    }

    public function integrations()
    {
        $logs = ApiLog::latest('requested_at')->get()->unique('service')->values();
        return response()->json(['success' => true, 'data' => $logs]);
    }
}
