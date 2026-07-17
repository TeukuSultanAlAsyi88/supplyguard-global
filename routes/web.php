<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\VisualizationController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\WeatherController;

use App\Http\Controllers\Admin\ApiLogController as AdminApiLogController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PortController as AdminPortController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WordController as AdminWordController;

/*
|--------------------------------------------------------------------------
| Halaman Awal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if (
        method_exists($user, 'isAdmin')
        && $user->isAdmin()
    ) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Autentikasi Pengguna
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get(
        '/masuk',
        [AuthController::class, 'showLogin']
    )->name('login');

    Route::post(
        '/masuk',
        [AuthController::class, 'login']
    )->name('login.store');

    Route::get(
        '/daftar',
        [AuthController::class, 'showRegister']
    )->name('register');

    Route::post(
        '/daftar',
        [AuthController::class, 'register']
    )->name('register.store');
});

Route::post(
    '/keluar',
    [AuthController::class, 'logout']
)
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Halaman Pengguna
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::get(
        '/dashboard/live',
        [DashboardController::class, 'live']
    )->name('dashboard.live');

    Route::get(
        '/negara',
        [CountryController::class, 'index']
    )->name('countries.index');

    Route::get(
        '/negara/{country}',
        [CountryController::class, 'show']
    )->name('countries.show');

    Route::post(
        '/negara-sinkron',
        [CountryController::class, 'sync']
    )->name('countries.sync');

    Route::get(
        '/cuaca',
        [WeatherController::class, 'index']
    )->name('weather.index');

    Route::get(
        '/nilai-tukar',
        [CurrencyController::class, 'index']
    )->name('currency.index');

    Route::get(
        '/berita',
        [NewsController::class, 'index']
    )->name('news.index');

    Route::get(
        '/pelabuhan',
        [PortController::class, 'index']
    )->name('ports.index');

    Route::get(
        '/analisis-risiko',
        [RiskController::class, 'index']
    )->name('risk.index');

    Route::post(
        '/analisis-risiko/{country}',
        [RiskController::class, 'calculate']
    )->name('risk.calculate');

    Route::get(
        '/perbandingan-negara',
        [ComparisonController::class, 'index']
    )->name('comparison.index');

    Route::get(
        '/visualisasi-data',
        [VisualizationController::class, 'index']
    )->name('visualization.index');

    Route::get(
        '/daftar-pemantauan',
        [WatchlistController::class, 'index']
    )->name('watchlists.index');

    Route::post(
        '/daftar-pemantauan/{country}',
        [WatchlistController::class, 'store']
    )->name('watchlists.store');

    Route::delete(
        '/daftar-pemantauan/{watchlist}',
        [WatchlistController::class, 'destroy']
    )->name('watchlists.destroy');

    Route::get(
        '/dokumentasi-api',
        [ApiDocsController::class, 'index']
    )->name('api-docs.index');
});

/*
|--------------------------------------------------------------------------
| Halaman Administrator
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'admin',
    ])
    ->group(function () {
        Route::get(
            '/',
            [AdminDashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/pengguna',
            [AdminUserController::class, 'index']
        )->name('users.index');

        Route::put(
            '/pengguna/{user}',
            [AdminUserController::class, 'update']
        )->name('users.update');

        Route::delete(
            '/pengguna/{user}',
            [AdminUserController::class, 'destroy']
        )->name('users.destroy');

        Route::get(
            '/pelabuhan',
            [AdminPortController::class, 'index']
        )->name('ports.index');

        Route::post(
            '/pelabuhan',
            [AdminPortController::class, 'store']
        )->name('ports.store');

        Route::get(
            '/pelabuhan/contoh-csv',
            [AdminPortController::class, 'sample']
        )->name('ports.sample');

        Route::post(
            '/pelabuhan/import',
            [AdminPortController::class, 'import']
        )->name('ports.import');

        Route::put(
            '/pelabuhan/{port}',
            [AdminPortController::class, 'update']
        )->name('ports.update');

        Route::delete(
            '/pelabuhan/{port}',
            [AdminPortController::class, 'destroy']
        )->name('ports.destroy');

        Route::get(
            '/artikel',
            [AdminArticleController::class, 'index']
        )->name('articles.index');

        Route::post(
            '/artikel',
            [AdminArticleController::class, 'store']
        )->name('articles.store');

        Route::put(
            '/artikel/{article}',
            [AdminArticleController::class, 'update']
        )->name('articles.update');

        Route::delete(
            '/artikel/{article}',
            [AdminArticleController::class, 'destroy']
        )->name('articles.destroy');

        Route::get(
            '/kata-sentimen',
            [AdminWordController::class, 'index']
        )->name('words.index');

        Route::post(
            '/kata-sentimen',
            [AdminWordController::class, 'store']
        )->name('words.store');

        Route::delete(
            '/kata-sentimen/{type}/{id}',
            [AdminWordController::class, 'destroy']
        )->name('words.destroy');

        Route::get(
            '/log-api',
            [AdminApiLogController::class, 'index']
        )->name('api-logs.index');

        Route::delete(
            '/log-api',
            [AdminApiLogController::class, 'clear']
        )->name('api-logs.clear');
    });