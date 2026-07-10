# SupplyGuard Indonesia

**Global Supply Chain Risk Intelligence Platform** adalah project UAS Pemrograman Web berbasis Laravel untuk memantau risiko rantai pasok global dari data negara, ekonomi, cuaca, nilai tukar, berita, sentimen, dan lokasi pelabuhan.

Seluruh antarmuka utama menggunakan **Bahasa Indonesia**.

## Teknologi

- PHP 8.2+
- Laravel 12
- MySQL
- Bootstrap 5
- JavaScript ES6 dan Fetch API (AJAX)
- Chart.js
- Leaflet.js dan OpenStreetMap
- PHPUnit
- GitHub Actions

## Integrasi data

1. REST Countries: profil negara
2. World Bank API: GDP, inflasi, populasi, ekspor, impor
3. Open-Meteo: temperatur, kelembapan, hujan, angin, risiko badai
4. ExchangeRate-API: kurs terbaru
5. Frankfurter: riwayat kurs harian
6. GNews: berita logistik, perdagangan, ekonomi, dan geopolitik
7. World Port Index: dataset lokasi serta karakteristik pelabuhan
8. OpenStreetMap: peta dasar Leaflet

## Fitur utama

- Dasbor analitik dan status integrasi
- Dasbor data negara
- Riwayat ekonomi 10 tahun
- Pemantauan cuaca global dengan marker risiko
- Kurs terkini, tren 30 hari, dan simulasi biaya impor
- Intelijen berita dan persentase sentimen
- Peta pelabuhan global dan pencarian AJAX
- Import CSV World Port Index
- Weighted Risk Model dan rincian bobot setiap komponen
- Perbandingan dua negara
- Visualisasi GDP, inflasi, kurs, dan risiko
- Daftar pemantauan pengguna
- Admin pengguna, pelabuhan, artikel, kamus sentimen, dan log API
- 35 endpoint REST API
- Scheduler sinkronisasi data
- Automated test dan GitHub Actions
- Data demo offline: ekonomi 10 tahun, kurs 30 hari, cuaca, berita, risiko, dan 100 pelabuhan

## Rumus risiko

```text
Total Risiko =
(Cuaca × 30%) +
(Inflasi × 20%) +
(Berita × 40%) +
(Mata Uang × 10%)
```

Kategori:

- 0–30: Risiko Rendah
- >30–60: Risiko Sedang
- >60–100: Risiko Tinggi

Bobot disimpan pada tabel `system_settings`, sedangkan rincian hasil per komponen disimpan pada `risk_components`.

## Instalasi cepat

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Akun admin:

```text
Email: admin@supplyguard.test
Kata sandi: password
```

Akun pengguna:

```text
Email: user@supplyguard.test
Kata sandi: password
```

## Sinkronisasi data

```powershell
php artisan app:sync-countries
php artisan supplyguard:sync-economics --years=10
php artisan supplyguard:sync-weather --limit=20
php artisan supplyguard:sync-currency IDR --days=30
php artisan supplyguard:import-ports database/data/world_port_index_sample.csv
```

Untuk semua negara ekonomi atau mata uang:

```powershell
php artisan supplyguard:sync-economics --all --years=10
php artisan supplyguard:sync-currency IDR --all --days=30
```

## Scheduler

Jalankan selama pengembangan:

```powershell
php artisan schedule:work
```

Jadwal bawaan:

- Cuaca negara prioritas: setiap jam
- Kurs IDR: setiap hari pukul 01.00
- Riwayat ekonomi: setiap Senin pukul 02.00

## API key

Isi pada `.env`:

```env
REST_COUNTRIES_API_KEY=
GNEWS_API_KEY=
```

Tanpa API key, data awal seeder tetap tersedia agar seluruh grafik, peta, sentimen, dan skor risiko dapat didemonstrasikan. Command sinkronisasi dengan opsi paksa akan mengambil serta memperbarui data dari sumber eksternal. Berita real-time memerlukan `GNEWS_API_KEY`. Integrasi REST Countries v5 memakai key; aplikasi juga memiliki fallback legacy.

## Dataset pelabuhan

Seeder memasukkan 100 data contoh lintas dunia dari file:

```text
database/data/world_port_index_sample.csv
```

Untuk dataset yang lebih lengkap:

1. Login sebagai admin.
2. Buka **Dasbor Admin → Kelola Pelabuhan**.
3. Pilih file CSV World Port Index.
4. Klik **Import Sekarang**.

Importer mengenali variasi kolom umum dan mencatat hasil import pada tabel `port_import_batches`.

## Pengujian

```powershell
php artisan test
```

Test mencakup API utama, analisis sentimen, kontrol akses admin, dan watchlist AJAX.

## Dokumentasi tambahan

- `PANDUAN_MENJALANKAN_LENGKAP.md`
- `PERUBAHAN_KODING_V2.md`
- `CHECKLIST_KESESUAIAN_UAS.md`
