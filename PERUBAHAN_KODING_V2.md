# Daftar Bagian Coding yang Ditambah atau Diubah

Dokumen ini menunjukkan lokasi coding yang perlu dipahami saat menjelaskan project kepada dosen.

## 1. Database

### File baru

`database/migrations/2026_07_09_000200_enhance_supplyguard_features.php`

Perubahan:

- Menambah kelembapan, suhu terasa, hembusan angin, peluang hujan, kondisi, dan status siang/malam pada `weather_data`.
- Menambah tanggal serta sumber kurs pada `currency_rates`.
- Menambah nomor WPI, ukuran, jenis harbor, sumber, dan waktu import pada `ports`.
- Menambah bahasa berita pada `news_cache`.
- Membuat tabel `port_import_batches`.

### Tabel penting

- `risk_components`: rincian nilai asli, normalisasi, bobot, dan kontribusi.
- `news_sentiments`: kata positif dan negatif yang ditemukan.
- `system_settings`: bobot algoritma risiko.

## 2. Integrasi API dan Data Engineering

### `app/Services/CountryService.php`

- Sinkronisasi REST Countries v5 dan fallback legacy.
- Pengambilan GDP, inflasi, populasi, ekspor, dan impor selama 10 tahun.

### `app/Services/WeatherService.php`

- Mengambil cuaca Open-Meteo.
- Menghitung risiko badai dari angin, hembusan, hujan, peluang hujan, dan kode badai.
- Menyediakan data peta cuaca global.

### `app/Services/CurrencyService.php`

- Kurs terbaru dari ExchangeRate-API.
- Riwayat harian dari Frankfurter.
- Konversi biaya impor.

### `app/Services/NewsService.php`

- Mengambil berita GNews.
- Menjalankan sentiment analysis.
- Menyimpan hasil ke `news_cache` dan `news_sentiments`.

### `app/Services/SentimentService.php`

- Membersihkan teks.
- Mencocokkan kata dengan tabel positif/negatif.
- Menentukan Positif, Netral, atau Negatif.

### `app/Services/RiskScoringService.php`

- Weighted Risk Model.
- Bobot dinormalisasi agar total selalu 100%.
- Menyimpan komponen dan alasan perhitungan.

### `app/Services/PortImportService.php`

- Import CSV World Port Index.
- Mendukung variasi nama header.
- Mengubah koordinat derajat/menit menjadi desimal bila diperlukan.
- Mencatat jumlah masuk dan dilewati.

## 3. AJAX dan JavaScript ES6

### `resources/views/layouts/app.blade.php`

Menambah helper global:

- `sg.fetchJson()`
- CSRF header
- loading overlay
- toast berhasil/gagal
- format angka Indonesia
- escape HTML

### Halaman yang menggunakan AJAX

- `resources/views/countries/index.blade.php`
- `resources/views/countries/show.blade.php`
- `resources/views/weather/index.blade.php`
- `resources/views/currency/index.blade.php`
- `resources/views/news/index.blade.php`
- `resources/views/ports/index.blade.php`
- `resources/views/risk/index.blade.php`
- `resources/views/comparison/index.blade.php`
- `resources/views/watchlists/index.blade.php`
- `resources/views/dashboard.blade.php`

## 4. Peta dan Grafik

### Cuaca

`resources/views/weather/index.blade.php`

- Marker global.
- Warna risiko rendah, sedang, tinggi.
- Hujan, angin, hembusan, kelembapan, dan risiko badai.

### Pelabuhan

`resources/views/ports/index.blade.php`

- Maksimal 1.000 marker.
- Pencarian nama, kota, negara, UN/LOCODE, dan nomor WPI.

### Visualisasi

`resources/views/visualization/index.blade.php`

- GDP 10 tahun.
- Inflasi 10 tahun.
- Kurs 30 hari.
- Riwayat skor risiko.

## 5. Admin

### `app/Http/Controllers/Admin/PortController.php`

- CRUD pelabuhan.
- Import CSV.
- Download contoh CSV.
- Riwayat batch import.

### `resources/views/admin/ports/index.blade.php`

- Form import.
- Tambah, ubah, hapus.
- Riwayat import.

Admin lainnya tetap mencakup pengguna, artikel, kata sentimen, dan log API.

## 6. REST API

`routes/api.php` sekarang mempunyai 35 endpoint, dikelompokkan menjadi:

- 8 endpoint negara
- 3 endpoint cuaca
- 4 endpoint kurs
- 5 endpoint risiko
- 4 endpoint pelabuhan
- 5 endpoint berita/sentimen
- 4 endpoint analitik
- 2 endpoint artikel

Controller terkait berada di `app/Http/Controllers/Api`.

## 7. Command dan Scheduler

### Command baru

- `supplyguard:sync-economics`
- `supplyguard:sync-weather`
- `supplyguard:sync-currency`
- `supplyguard:import-ports`

### Scheduler

`routes/console.php`

- Cuaca setiap jam.
- Kurs setiap hari.
- Ekonomi setiap minggu.

## 8. Testing dan GitHub

### Test

- `tests/Feature/CoreApiTest.php`
- `tests/Feature/WatchlistTest.php`
- `tests/Feature/AccessControlTest.php`

### GitHub Actions

`.github/workflows/laravel-tests.yml`

Test dijalankan otomatis ketika push atau pull request.

## 9. Konfigurasi `.env`

`.env.example` ditambah dan dirapikan:

```env
REST_COUNTRIES_API_KEY=
REST_COUNTRIES_URL=https://api.restcountries.com/countries/v5
REST_COUNTRIES_LEGACY_URL=https://restcountries.com/v3.1
WORLD_BANK_URL=https://api.worldbank.org/v2
OPEN_METEO_URL=https://api.open-meteo.com/v1
EXCHANGE_RATE_URL=https://open.er-api.com/v6
FRANKFURTER_URL=https://api.frankfurter.dev/v2
GNEWS_API_KEY=
GNEWS_URL=https://gnews.io/api/v4
```

## 10. Data Demo dan Ketahanan Saat Presentasi

### `database/seeders/DatabaseSeeder.php`

Seeder diperluas agar setelah `migrate --seed` seluruh halaman langsung mempunyai isi:

- Riwayat GDP, inflasi, ekspor, impor, dan populasi selama 10 tahun.
- Riwayat kurs 30 hari untuk mata uang negara contoh.
- Data cuaca dan tingkat risiko badai.
- Berita positif, netral, dan negatif.
- Riwayat skor risiko tujuh hari beserta komponen bobot.
- 100 lokasi pelabuhan lintas dunia.

Data ini diberi status data demo dan dapat diganti/diperbarui melalui command sinkronisasi API.

### `app/Services/CurrencyService.php`

- Data eksternal diprioritaskan daripada data demo pada tanggal yang sama.
- Mencegah dua titik grafik pada tanggal yang sama ketika data ExchangeRate, Frankfurter, dan seeder bertemu.
- Tetap memakai data demo sebagai fallback jika layanan kurs tidak dapat dijangkau.
