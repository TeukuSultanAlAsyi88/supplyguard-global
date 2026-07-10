<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    ApiDocsController,
    AuthController,
    ComparisonController,
    CountryController,
    CurrencyController,
    DashboardController,
    NewsController,
    PortController,
    RiskController,
    VisualizationController,
    WatchlistController,
    WeatherController
};

use App\Http\Controllers\Admin;

/*
|--------------------------------------------------------------------------
| Halaman Awal
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/dashboard');

/*
|--------------------------------------------------------------------------
| Autentikasi Pengguna
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/masuk', [AuthController::class, 'login'])
        ->name('login.store');

    Route::get('/daftar', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/daftar', [AuthController::class, 'register'])
        ->name('register.store');
});

Route::post('/keluar', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Halaman Pengguna
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dasbor
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
     * Route ini dipanggil melalui AJAX untuk memperbarui isi dasbor
     * tanpa melakukan reload seluruh halaman.
     */
    Route::get('/dashboard/live', [DashboardController::class, 'live'])
        ->name('dashboard.live');

    /*
    |--------------------------------------------------------------------------
    | Data Negara
    |--------------------------------------------------------------------------
    */

    Route::get('/negara', [CountryController::class, 'index'])
        ->name('countries.index');

    Route::get('/negara/{country}', [CountryController::class, 'show'])
        ->name('countries.show');

    Route::post('/negara-sinkron', [CountryController::class, 'sync'])
        ->name('countries.sync');

    /*
    |--------------------------------------------------------------------------
    | Pemantauan Cuaca
    |--------------------------------------------------------------------------
    */

    Route::get('/cuaca', [WeatherController::class, 'index'])
        ->name('weather.index');

    /*
    |--------------------------------------------------------------------------
    | Nilai Tukar Mata Uang
    |--------------------------------------------------------------------------
    */

    Route::get('/nilai-tukar', [CurrencyController::class, 'index'])
        ->name('currency.index');

    /*
    |--------------------------------------------------------------------------
    | Intelijen Berita
    |--------------------------------------------------------------------------
    */

    Route::get('/berita', [NewsController::class, 'index'])
        ->name('news.index');

    /*
    |--------------------------------------------------------------------------
    | Lokasi Pelabuhan
    |--------------------------------------------------------------------------
    */

    Route::get('/pelabuhan', [PortController::class, 'index'])
        ->name('ports.index');

    /*
    |--------------------------------------------------------------------------
    | Analisis Risiko
    |--------------------------------------------------------------------------
    */

    Route::get('/analisis-risiko', [RiskController::class, 'index'])
        ->name('risk.index');

    Route::post(
        '/analisis-risiko/{country}',
        [RiskController::class, 'calculate']
    )->name('risk.calculate');

    /*
    |--------------------------------------------------------------------------
    | Perbandingan Negara
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/perbandingan-negara',
        [ComparisonController::class, 'index']
    )->name('comparison.index');

    /*
    |--------------------------------------------------------------------------
    | Visualisasi Data
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/visualisasi-data',
        [VisualizationController::class, 'index']
    )->name('visualization.index');

    /*
    |--------------------------------------------------------------------------
    | Daftar Pemantauan
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Dokumentasi REST API
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dokumentasi-api',
        [ApiDocsController::class, 'index']
    )->name('api-docs.index');

    /*
    |--------------------------------------------------------------------------
    | Halaman Administrator
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dasbor Administrator
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/',
                [Admin\DashboardController::class, 'index']
            )->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Kelola Pengguna
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/pengguna',
                [Admin\UserController::class, 'index']
            )->name('users.index');

            Route::put(
                '/pengguna/{user}',
                [Admin\UserController::class, 'update']
            )->name('users.update');

            Route::delete(
                '/pengguna/{user}',
                [Admin\UserController::class, 'destroy']
            )->name('users.destroy');

            /*
            |--------------------------------------------------------------------------
            | Kelola Pelabuhan
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/pelabuhan',
                [Admin\PortController::class, 'index']
            )->name('ports.index');

            Route::post(
                '/pelabuhan',
                [Admin\PortController::class, 'store']
            )->name('ports.store');

            /*
             * Route contoh CSV dan import harus diletakkan
             * sebelum route yang menggunakan parameter {port}.
             */
            Route::get(
                '/pelabuhan/contoh-csv',
                [Admin\PortController::class, 'sample']
            )->name('ports.sample');

            Route::post(
                '/pelabuhan/import',
                [Admin\PortController::class, 'import']
            )->name('ports.import');

            Route::put(
                '/pelabuhan/{port}',
                [Admin\PortController::class, 'update']
            )->name('ports.update');

            Route::delete(
                '/pelabuhan/{port}',
                [Admin\PortController::class, 'destroy']
            )->name('ports.destroy');

            /*
            |--------------------------------------------------------------------------
            | Kelola Artikel
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/artikel',
                [Admin\ArticleController::class, 'index']
            )->name('articles.index');

            Route::post(
                '/artikel',
                [Admin\ArticleController::class, 'store']
            )->name('articles.store');

            Route::put(
                '/artikel/{article}',
                [Admin\ArticleController::class, 'update']
            )->name('articles.update');

            Route::delete(
                '/artikel/{article}',
                [Admin\ArticleController::class, 'destroy']
            )->name('articles.destroy');

            /*
            |--------------------------------------------------------------------------
            | Kelola Kamus Sentimen
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/kata-sentimen',
                [Admin\WordController::class, 'index']
            )->name('words.index');

            Route::post(
                '/kata-sentimen',
                [Admin\WordController::class, 'store']
            )->name('words.store');

            Route::delete(
                '/kata-sentimen/{type}/{id}',
                [Admin\WordController::class, 'destroy']
            )->name('words.destroy');

            /*
            |--------------------------------------------------------------------------
            | Log Integrasi API
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/log-api',
                [Admin\ApiLogController::class, 'index']
            )->name('api-logs.index');

            Route::delete(
                '/log-api',
                [Admin\ApiLogController::class, 'clear']
            )->name('api-logs.clear');
        });
});