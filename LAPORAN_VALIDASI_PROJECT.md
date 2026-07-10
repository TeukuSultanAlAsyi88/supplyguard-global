# Laporan Validasi SupplyGuard Indonesia V2

Tanggal validasi source: 9 Juli 2026.

## Hasil pemeriksaan statis

- 87 file PHP pada `app`, `config`, `database`, `routes`, dan `tests` lolos `php -l`.
- 15 blok JavaScript dari Blade lolos pemeriksaan sintaks `node --check` setelah ekspresi Blade dinetralkan.
- Seluruh pemanggilan view dari controller mempunyai file Blade yang sesuai.
- Seluruh nama route yang dipanggil dari view ditemukan pada `routes/web.php`.
- Seluruh controller dan method yang disebut oleh route ditemukan.
- `routes/api.php` berisi 35 endpoint REST API.
- Migration membuat 18 tabel domain SupplyGuard dan 8 tabel infrastruktur Laravel (total 26 tabel).
- Dataset contoh pelabuhan berisi 100 record ditambah header.

## Cakupan kebutuhan PDF

- Laravel, PHP, MySQL, Bootstrap 5, JavaScript ES6, AJAX.
- Chart.js, Leaflet, dan OpenStreetMap.
- REST Countries, World Bank, Open-Meteo, ExchangeRate-API, Frankfurter, GNews, serta World Port Index CSV.
- Country Dashboard, Weather Monitoring, Currency Dashboard, News Intelligence, Port Dashboard.
- Weighted Risk Engine, Country Comparison, Data Visualization, Watchlist, dan Admin Dashboard.
- Lexicon Based Sentiment Analysis dan persentase sentimen.
- 35 endpoint REST API dan dokumentasinya.
- Scheduler, automated test, serta GitHub Actions.

## Batas validasi di lingkungan pembuatan

Composer dan folder `vendor` tidak tersedia di lingkungan pembuatan, sehingga `php artisan migrate --seed`, `php artisan test`, dan pengujian browser penuh belum dapat dijalankan di sini. Pemeriksaan runtime tersebut wajib dilakukan setelah `composer install` pada laptop pengguna.

Project menyediakan data demo agar halaman tetap berisi ketika API key atau koneksi internet belum tersedia. Data real-time tetap bergantung pada layanan eksternal dan konfigurasi `.env`.
