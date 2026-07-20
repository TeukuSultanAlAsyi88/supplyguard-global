<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Pagination View
        |--------------------------------------------------------------------------
        |
        | SupplyGuard memakai Bootstrap/custom CSS, bukan Tailwind.
        | Tanpa ini, ikon pagination Laravel bisa tampil sangat besar
        | seperti tanda panah raksasa pada halaman Data Negara,
        | Analisis Risiko, dan Lokasi Pelabuhan.
        |
        */
        Paginator::useBootstrapFive();

        /*
        |--------------------------------------------------------------------------
        | Force HTTPS On Production
        |--------------------------------------------------------------------------
        |
        | Ini menjaga form login dan asset tetap menggunakan HTTPS
        | ketika aplikasi berjalan di Railway/hosting production.
        |
        */
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}