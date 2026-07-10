<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RiskScore;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    public function index(Request $request, RiskScoringService $service)
    {
        $countries = Country::orderBy('name')->get();
        $selected = $request->country ? Country::find($request->country) : null;
        $score = $selected ? $service->calculate($selected) : null;
        $latest = RiskScore::with('country')->latest('calculated_at')->paginate(15);

        return view('risk.index', compact('countries', 'selected', 'score', 'latest'));
    }

    public function calculate(Request $request, Country $country, RiskScoringService $service)
    {
        $score = $service->calculate($country);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Skor risiko berhasil dihitung.', 'data' => $score]);
        }
        return back()->with('success', 'Skor risiko berhasil dihitung.');
    }
}
