<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Perintah Informasi SupplyGuard
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment('SupplyGuard Indonesia siap digunakan.');
})->purpose('Menampilkan pesan SupplyGuard Indonesia');

/*
|--------------------------------------------------------------------------
| Status Scheduler SupplyGuard
|--------------------------------------------------------------------------
|
| Jalankan:
| php artisan supplyguard:scheduler-status
|
| Perintah ini hanya menampilkan informasi jadwal pembaruan.
|
*/

Artisan::command('supplyguard:scheduler-status', function () {
    $this->info('Status jadwal otomatis SupplyGuard Indonesia');
    $this->newLine();

    $this->table(
        ['Data', 'Jadwal pembaruan', 'Sumber'],
        [
            [
                'Data negara',
                'Setiap Minggu pukul 00.30 WIB',
                'REST Countries API',
            ],
            [
                'Cuaca',
                'Setiap 30 menit',
                'Open-Meteo API',
            ],
            [
                'Nilai tukar',
                'Setiap 1 jam',
                'Exchange Rate API dan Frankfurter',
            ],
            [
                'Data ekonomi',
                'Setiap Senin pukul 02.00 WIB',
                'World Bank API',
            ],
            [
                'Dashboard',
                'Setiap 5 menit melalui AJAX',
                'Database SupplyGuard',
            ],
        ]
    );

    $this->newLine();
    $this->comment(
        'Jalankan "php artisan schedule:work" agar jadwal otomatis aktif.'
    );
})->purpose('Menampilkan status jadwal otomatis SupplyGuard');

/*
|--------------------------------------------------------------------------
| Sinkronisasi Data Negara
|--------------------------------------------------------------------------
|
| Data negara tidak berubah terlalu sering, sehingga cukup diperbarui
| satu kali setiap minggu.
|
*/

Schedule::command('app:sync-countries')
    ->weeklyOn(7, '00:30')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(60)
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->name('supplyguard-sync-countries');

/*
|--------------------------------------------------------------------------
| Sinkronisasi Cuaca
|--------------------------------------------------------------------------
|
| Cuaca diperbarui setiap 30 menit agar informasi temperatur, hujan,
| angin, dan risiko badai tetap mendekati kondisi terbaru.
|
*/

Schedule::command('supplyguard:sync-weather --limit=20')
    ->everyThirtyMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->name('supplyguard-sync-weather');

/*
|--------------------------------------------------------------------------
| Sinkronisasi Nilai Tukar
|--------------------------------------------------------------------------
|
| Nilai tukar diperbarui setiap jam. Menit ke-5 digunakan agar proses
| tidak berjalan bersamaan tepat pada pergantian jam.
|
*/

Schedule::command('supplyguard:sync-currency IDR --days=30')
    ->hourlyAt(5)
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(60)
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->name('supplyguard-sync-currency');

/*
|--------------------------------------------------------------------------
| Sinkronisasi Data Ekonomi
|--------------------------------------------------------------------------
|
| GDP, inflasi, populasi, ekspor, dan impor bukan data per menit.
| Data tersebut cukup diperbarui satu kali setiap minggu.
|
*/

Schedule::command('supplyguard:sync-economics --years=10')
    ->weeklyOn(1, '02:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(180)
    ->appendOutputTo(storage_path('logs/scheduler.log'))
    ->name('supplyguard-sync-economics');

/*
|--------------------------------------------------------------------------
| Sinkronisasi Berita Negara
|--------------------------------------------------------------------------
|
| Mengambil berita untuk maksimal 80 negara setiap hari.
| Dengan cara ini kuota gratis tidak langsung habis.
|
*/

Schedule::command(
    'supplyguard:sync-news --limit=80 --articles=5'
)
    ->dailyAt('03:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(180)
    ->appendOutputTo(
        storage_path('logs/scheduler.log')
    )
    ->name('supplyguard-sync-news');