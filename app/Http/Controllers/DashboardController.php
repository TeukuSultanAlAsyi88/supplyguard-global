<?php

namespace App\Http\Controllers;

use App\Models\{
    ApiLog,
    Country,
    CurrencyRate,
    NewsCache,
    Port,
    RiskScore,
    Watchlist,
    WeatherData
};
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', $this->dashboardData());
    }

    /**
     * Mengirim data dashboard terbaru dalam format JSON.
     * Method ini dipanggil oleh AJAX setiap 5 menit.
     */
    public function live(): JsonResponse
    {
        $data = $this->dashboardData();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard berhasil diperbarui.',

            'data' => [
                'counts' => [
                    'countries' => $data['countryCount'],
                    'ports' => $data['portCount'],
                    'news' => $data['newsCount'],
                    'watchlists' => $data['watchlistCount'],
                ],

                'top_risks' => $data['topRisks']
                    ->map(fn (RiskScore $risk) => [
                        'country' => $risk->country?->name ?? '-',
                        'score' => (float) $risk->total_score,
                        'level' => $risk->risk_level,
                        'calculated_at' =>
                            $risk->calculated_at?->toIso8601String(),
                        'calculated_human' =>
                            $risk->calculated_at?->diffForHumans() ?? '-',
                    ])
                    ->values(),

                'recent_news' => $data['recentNews']
                    ->map(fn (NewsCache $news) => [
                        'title' => $news->title,
                        'source' => $news->source,
                        'sentiment' => $news->sentiment,
                        'published_at' =>
                            $news->published_at?->toIso8601String(),
                        'published_human' =>
                            $news->published_at?->diffForHumans() ?? '-',
                    ])
                    ->values(),

                'api_health' => $data['apiHealth']
                    ->map(fn (ApiLog $log) => [
                        'service' => $log->service,
                        'success' => (bool) $log->success,
                        'response_time_ms' => $log->response_time_ms,
                        'requested_at' =>
                            $log->requested_at?->toIso8601String(),
                        'requested_human' =>
                            $log->requested_at?->diffForHumans() ?? '-',
                    ])
                    ->values(),

                'risk_levels' => $data['riskLevels'],

                'refreshed_at' => now()->toIso8601String(),
                'refreshed_label' =>
                    now()->translatedFormat('d F Y, H:i:s'),

                'latest_data_at' =>
                    $data['latestDataAt']?->toIso8601String(),

                'latest_data_label' =>
                    $data['latestDataAt']
                        ?->translatedFormat('d F Y, H:i:s'),
            ],
        ]);
    }

    /**
     * Mengambil seluruh data yang dipakai dashboard.
     */
    private function dashboardData(): array
    {
        // Mengambil satu skor risiko terbaru untuk setiap negara.
        $latestRiskIds = RiskScore::query()
            ->selectRaw('MAX(id)')
            ->groupBy('country_id');

        $topRisks = RiskScore::with('country')
            ->whereIn('id', $latestRiskIds)
            ->orderByDesc('total_score')
            ->take(5)
            ->get();

        $recentNews = NewsCache::latest('published_at')
            ->take(5)
            ->get();

        $apiHealth = ApiLog::latest('requested_at')
            ->take(6)
            ->get();

        // Mencari waktu data terbaru dari beberapa tabel.
        $latestDates = collect([
            RiskScore::max('calculated_at'),
            NewsCache::max('published_at'),
            ApiLog::max('requested_at'),
            WeatherData::max('observed_at'),
            CurrencyRate::max('recorded_at'),
        ])
            ->filter()
            ->map(
                fn ($date) =>
                \Illuminate\Support\Carbon::parse($date)
            );

        return [
            'countryCount' => Country::count(),
            'portCount' => Port::count(),
            'newsCount' => NewsCache::count(),

            'watchlistCount' => Watchlist::where(
                'user_id',
                auth()->id()
            )->count(),

            'topRisks' => $topRisks,
            'recentNews' => $recentNews,
            'apiHealth' => $apiHealth,

            'riskLevels' => RiskScore::whereIn('id', $latestRiskIds)
                ->get()
                ->countBy('risk_level'),

            'latestDataAt' => $latestDates
                ->sortDesc()
                ->first(),
        ];
    }
}