<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Models\Country;
use App\Models\CurrencyRate;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\Watchlist;
use App\Models\WeatherData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama pengguna.
     */
    public function index(): View
    {
        return view(
            'dashboard',
            $this->dashboardData()
        );
    }

    /**
     * Mengirim data dashboard terbaru dalam format JSON.
     *
     * Method ini dipanggil melalui AJAX oleh dashboard
     * untuk memperbarui data tanpa me-reload seluruh halaman.
     */
    public function live(): JsonResponse
    {
        $data = $this->dashboardData();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard berhasil diperbarui.',

            'data' => [
                /*
                |--------------------------------------------------------------------------
                | Ringkasan dashboard
                |--------------------------------------------------------------------------
                */

                'counts' => [
                    'countries' => $data['countryCount'],
                    'ports' => $data['portCount'],
                    'news' => $data['newsCount'],
                    'watchlists' => $data['watchlistCount'],
                ],

                /*
                |--------------------------------------------------------------------------
                | Lima negara dengan risiko tertinggi
                |--------------------------------------------------------------------------
                */

                'top_risks' => $data['topRisks']
                    ->map(function (RiskScore $risk): array {
                        return [
                            'id' => $risk->id,
                            'country_id' => $risk->country_id,

                            'country' => [
                                'id' => $risk->country?->id,
                                'name' => $risk->country?->name,
                                'code' => $risk->country?->code,
                                'cca3' => $risk->country?->cca3,
                                'latitude' => $risk->country?->latitude,
                                'longitude' => $risk->country?->longitude,
                            ],

                            'country_name' =>
                                $risk->country?->name ?? '-',

                            'country_code' =>
                                $risk->country?->code,

                            'iso3' =>
                                $risk->country?->cca3,

                            'total_score' =>
                                (float) $risk->total_score,

                            'score' =>
                                (float) $risk->total_score,

                            'risk_level' =>
                                $this->normalizeRiskLevel(
                                    $risk->risk_level,
                                    (float) $risk->total_score
                                ),

                            'level' =>
                                $this->normalizeRiskLevel(
                                    $risk->risk_level,
                                    (float) $risk->total_score
                                ),

                            'calculated_at' =>
                                $risk->calculated_at
                                    ?->toIso8601String(),

                            'calculated_human' =>
                                $risk->calculated_at
                                    ?->diffForHumans() ?? '-',
                        ];
                    })
                    ->values(),

                /*
                |--------------------------------------------------------------------------
                | Berita terbaru
                |--------------------------------------------------------------------------
                |
                | image_url dan url ikut dikirim agar thumbnail berita
                | tetap muncul setelah dashboard diperbarui lewat AJAX.
                |
                */

                'recent_news' => $data['recentNews']
                    ->map(function (NewsCache $news): array {
                        return [
                            'id' => $news->id,
                            'country_id' => $news->country_id,
                            'title' => $news->title,
                            'description' => $news->description,
                            'url' => $news->url,
                            'image_url' => $news->image_url,
                            'source' => $news->source,

                            'sentiment' =>
                                $this->normalizeSentiment(
                                    $news->sentiment
                                ),

                            'positive_score' =>
                                (int) $news->positive_score,

                            'negative_score' =>
                                (int) $news->negative_score,

                            'published_at' =>
                                $news->published_at
                                    ?->toIso8601String(),

                            'published_human' =>
                                $news->published_at
                                    ?->diffForHumans() ?? '-',
                        ];
                    })
                    ->values(),

                /*
                |--------------------------------------------------------------------------
                | Status layanan API eksternal
                |--------------------------------------------------------------------------
                */

                'api_health' => $data['apiHealth']
                    ->map(function (ApiLog $log): array {
                        return [
                            'id' => $log->id,
                            'service' => $log->service,
                            'endpoint' => $log->endpoint,
                            'method' => $log->method,
                            'status_code' => $log->status_code,
                            'success' => (bool) $log->success,

                            'response_time_ms' =>
                                (int) ($log->response_time_ms ?? 0),

                            'message' => $log->message,

                            'requested_at' =>
                                $log->requested_at
                                    ?->toIso8601String(),

                            'requested_human' =>
                                $log->requested_at
                                    ?->diffForHumans() ?? '-',
                        ];
                    })
                    ->values(),

                /*
                |--------------------------------------------------------------------------
                | Distribusi tingkat risiko
                |--------------------------------------------------------------------------
                */

                'risk_levels' => $data['riskLevels'],

                /*
                |--------------------------------------------------------------------------
                | Informasi waktu pembaruan
                |--------------------------------------------------------------------------
                */

                'refreshed_at' =>
                    now()->toIso8601String(),

                'refreshed_label' =>
                    now()->translatedFormat(
                        'd F Y, H:i:s'
                    ),

                'latest_data_at' =>
                    $data['latestDataAt']
                        ?->toIso8601String(),

                'latest_data_label' =>
                    $data['latestDataAt']
                        ?->translatedFormat(
                            'd F Y, H:i:s'
                        ),
            ],
        ]);
    }

    /**
     * Mengambil seluruh data yang digunakan dashboard.
     */
    private function dashboardData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Skor risiko terbaru setiap negara
        |--------------------------------------------------------------------------
        |
        | Setiap negara mungkin mempunyai banyak riwayat skor.
        | Dashboard hanya menggunakan satu skor terbaru.
        |
        */

        $latestRiskIds = RiskScore::query()
            ->selectRaw('MAX(id)')
            ->groupBy('country_id');

        $latestRisks = RiskScore::query()
            ->with([
                'country:id,name,code,cca3,latitude,longitude',
            ])
            ->whereIn('id', $latestRiskIds)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Lima negara dengan skor risiko tertinggi
        |--------------------------------------------------------------------------
        */

        $topRisks = $latestRisks
            ->sortByDesc(
                fn (RiskScore $risk): float =>
                    (float) $risk->total_score
            )
            ->take(5)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Distribusi tingkat risiko
        |--------------------------------------------------------------------------
        */

        $riskLevels = $this->buildRiskDistribution(
            $latestRisks
        );

        /*
        |--------------------------------------------------------------------------
        | Lima berita terbaru
        |--------------------------------------------------------------------------
        */

        $recentNews = NewsCache::query()
            ->latest('published_at')
            ->latest('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Status terakhir setiap layanan API
        |--------------------------------------------------------------------------
        |
        | Setiap layanan hanya ditampilkan satu kali berdasarkan
        | log terbaru agar tidak terdapat layanan yang berulang.
        |
        */

        $latestApiLogIds = ApiLog::query()
            ->selectRaw('MAX(id)')
            ->groupBy('service');

        $apiHealth = ApiLog::query()
            ->whereIn('id', $latestApiLogIds)
            ->orderByDesc('requested_at')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Waktu data terbaru
        |--------------------------------------------------------------------------
        */

        $latestDataAt = $this->latestDataTimestamp();

        /*
        |--------------------------------------------------------------------------
        | Data untuk tampilan dashboard
        |--------------------------------------------------------------------------
        */

        return [
            'countryCount' =>
                Country::query()->count(),

            'portCount' =>
                Port::query()->count(),

            'newsCount' =>
                NewsCache::query()->count(),

            'watchlistCount' =>
                Watchlist::query()
                    ->where(
                        'user_id',
                        auth()->id()
                    )
                    ->count(),

            'topRisks' => $topRisks,
            'recentNews' => $recentNews,
            'apiHealth' => $apiHealth,
            'riskLevels' => $riskLevels,
            'latestDataAt' => $latestDataAt,
        ];
    }

    /**
     * Membentuk distribusi risiko yang selalu mempunyai
     * empat kategori utama.
     */
    private function buildRiskDistribution(
        Collection $latestRisks
    ): array {
        $distribution = [
            'Rendah' => 0,
            'Sedang' => 0,
            'Tinggi' => 0,
            'Kritis' => 0,
        ];

        foreach ($latestRisks as $risk) {
            $level = $this->normalizeRiskLevel(
                $risk->risk_level,
                (float) $risk->total_score
            );

            if (
                array_key_exists(
                    $level,
                    $distribution
                )
            ) {
                $distribution[$level]++;
            }
        }

        return $distribution;
    }

    /**
     * Menyamakan label tingkat risiko agar data berbahasa
     * Inggris dan Indonesia tetap dapat digunakan.
     */
    private function normalizeRiskLevel(
        mixed $level,
        float $score = 0
    ): string {
        $normalized = strtolower(
            trim((string) $level)
        );

        if (
            in_array(
                $normalized,
                [
                    'rendah',
                    'low',
                    'low risk',
                ],
                true
            )
        ) {
            return 'Rendah';
        }

        if (
            in_array(
                $normalized,
                [
                    'sedang',
                    'medium',
                    'moderate',
                    'medium risk',
                    'moderate risk',
                ],
                true
            )
        ) {
            return 'Sedang';
        }

        if (
            in_array(
                $normalized,
                [
                    'tinggi',
                    'high',
                    'high risk',
                ],
                true
            )
        ) {
            return 'Tinggi';
        }

        if (
            in_array(
                $normalized,
                [
                    'kritis',
                    'critical',
                    'critical risk',
                ],
                true
            )
        ) {
            return 'Kritis';
        }

        /*
         * Fallback berdasarkan nilai skor jika label kosong
         * atau tidak dikenali.
         */
        return match (true) {
            $score >= 76 => 'Kritis',
            $score >= 51 => 'Tinggi',
            $score >= 26 => 'Sedang',
            default => 'Rendah',
        };
    }

    /**
     * Menyamakan label sentimen berita.
     */
    private function normalizeSentiment(
        mixed $sentiment
    ): string {
        $normalized = strtolower(
            trim((string) $sentiment)
        );

        return match ($normalized) {
            'positif',
            'positive' => 'Positif',

            'negatif',
            'negative' => 'Negatif',

            default => 'Netral',
        };
    }

    /**
     * Mengambil waktu terbaru dari beberapa sumber data utama.
     */
    private function latestDataTimestamp(): ?Carbon
    {
        $dates = collect([
            RiskScore::query()
                ->max('calculated_at'),

            NewsCache::query()
                ->max('published_at'),

            ApiLog::query()
                ->max('requested_at'),

            WeatherData::query()
                ->max('observed_at'),

            CurrencyRate::query()
                ->max('recorded_at'),
        ])
            ->filter()
            ->map(function (mixed $date): ?Carbon {
                try {
                    return Carbon::parse($date);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter()
            ->sortDesc();

        return $dates->first();
    }
}