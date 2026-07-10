<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\NewsCache;
use App\Services\NewsService;
use App\Services\SentimentService;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    public function index(Request $request, NewsService $service)
    {
        $country = $request->country ? Country::where('code', strtoupper($request->country))->orWhere('id', $request->country)->first() : null;
        return response()->json(['success' => true, 'data' => $service->fetch($country, $request->string('q', 'logistics trade shipping economy'), $request->integer('limit', 20))]);
    }

    public function show(NewsCache $news)
    {
        return response()->json(['success' => true, 'data' => $news->load(['country', 'analysis'])]);
    }

    public function summary(Request $request)
    {
        $query = NewsCache::query();
        if ($request->filled('country')) {
            $country = Country::where('code', strtoupper($request->country))->orWhere('id', $request->country)->first();
            $query->when($country, fn ($builder) => $builder->where('country_id', $country->id));
        }
        $items = $query->latest('published_at')->limit($request->integer('limit', 100))->get();
        $total = max(1, $items->count());
        $counts = $items->countBy('sentiment');
        $percentages = collect(['Positif', 'Netral', 'Negatif'])->mapWithKeys(fn ($label) => [$label => round((($counts[$label] ?? 0) / $total) * 100, 2)]);
        return response()->json(['success' => true, 'data' => ['total' => $items->count(), 'counts' => $counts, 'percentages' => $percentages]]);
    }

    public function analyze(Request $request, SentimentService $service)
    {
        $validated = $request->validate(['text' => 'required|string|max:10000']);
        return response()->json(['success' => true, 'data' => $service->analyze($validated['text'])]);
    }
}
