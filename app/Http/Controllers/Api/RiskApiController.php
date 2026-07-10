<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RiskScore;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;

class RiskApiController extends Controller
{
    public function index(Request $request)
    {
        $query = RiskScore::with('country')->latest('calculated_at');
        if ($request->filled('level')) {
            $query->where('risk_level', $request->string('level'));
        }
        return response()->json(['success' => true, 'data' => $query->paginate($request->integer('per_page', 20))]);
    }

    public function summary()
    {
        $latestIds = RiskScore::selectRaw('MAX(id)')->groupBy('country_id');
        $latest = RiskScore::whereIn('id', $latestIds)->get();
        return response()->json(['success' => true, 'data' => [
            'total' => $latest->count(),
            'average' => round((float) $latest->avg('total_score'), 2),
            'levels' => $latest->countBy('risk_level'),
        ]]);
    }

    public function show(Country $country)
    {
        return response()->json(['success' => true, 'data' => $country->risks()->with('components')->latest('calculated_at')->first()]);
    }

    public function calculate(Country $country, RiskScoringService $service)
    {
        return response()->json(['success' => true, 'message' => 'Skor risiko berhasil dihitung.', 'data' => $service->calculate($country)]);
    }

    public function components(RiskScore $riskScore)
    {
        return response()->json(['success' => true, 'data' => $riskScore->load(['country', 'components'])]);
    }
}
