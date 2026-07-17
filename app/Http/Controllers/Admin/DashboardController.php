<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\Article;
use App\Models\NegativeWord;
use App\Models\Port;
use App\Models\PositiveWord;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard administrator.
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Statistik pengguna
        |--------------------------------------------------------------------------
        */

        $userCount = User::query()->count();

        /*
        |--------------------------------------------------------------------------
        | Statistik data utama
        |--------------------------------------------------------------------------
        */

        $portCount = Port::query()->count();

        $articleCount = Article::query()->count();

        $positiveCount = PositiveWord::query()->count();

        $negativeCount = NegativeWord::query()->count();

        /*
        |--------------------------------------------------------------------------
        | Statistik log API
        |--------------------------------------------------------------------------
        */

        $apiLogCount = ApiLog::query()->count();

        $apiSuccess = ApiLog::query()
            ->where('success', true)
            ->count();

        $apiFailed = ApiLog::query()
            ->where('success', false)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Persentase keberhasilan API
        |--------------------------------------------------------------------------
        */

        $apiSuccessRate = $apiLogCount > 0
            ? round(
                ($apiSuccess / $apiLogCount) * 100,
                1
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Log API terbaru
        |--------------------------------------------------------------------------
        */

        $logs = ApiLog::query()
            ->orderByDesc('requested_at')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Pengguna terbaru
        |--------------------------------------------------------------------------
        */

        $recentUsers = User::query()
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Artikel terbaru
        |--------------------------------------------------------------------------
        */

        $recentArticles = Article::query()
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Waktu request API terakhir
        |--------------------------------------------------------------------------
        */

        $lastApiRequest = $logs
            ->first()
            ?->requested_at;

        /*
        |--------------------------------------------------------------------------
        | Kirim data ke halaman dashboard admin
        |--------------------------------------------------------------------------
        */

        return view('admin.dashboard', [
            'userCount' => $userCount,

            'portCount' => $portCount,

            'articleCount' => $articleCount,

            'positiveCount' => $positiveCount,

            'negativeCount' => $negativeCount,

            'apiLogCount' => $apiLogCount,

            'apiSuccess' => $apiSuccess,

            'apiFailed' => $apiFailed,

            'apiSuccessRate' => $apiSuccessRate,

            'logs' => $logs,

            'recentUsers' => $recentUsers,

            'recentArticles' => $recentArticles,

            'lastApiRequest' => $lastApiRequest,
        ]);
    }
}