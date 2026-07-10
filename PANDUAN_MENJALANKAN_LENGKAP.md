# Panduan Menjalankan SupplyGuard dari Awal sampai Selesai

## 1. Persiapan

Pasang:

- XAMPP atau Laragon
- PHP minimal 8.2
- Composer 2
- MySQL
- VS Code
- Git

Periksa dari terminal:

```powershell
php -v
composer -V
git --version
```

## 2. Buka project

Ekstrak project, misalnya ke:

```text
D:\web\supplyguard-indonesia
```

Buka folder yang langsung mempunyai file `artisan` dan `composer.json`.

## 3. Install dependency

```powershell
cd D:\web\supplyguard-indonesia
composer install
```

## 4. Buat database

Nyalakan MySQL melalui XAMPP, lalu buka `http://localhost/phpmyadmin`.

Buat database:

```text
supplyguard_indonesia
```

Collation yang disarankan:

```text
utf8mb4_unicode_ci
```

## 5. Siapkan `.env`

```powershell
copy .env.example .env
```

Pastikan bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supplyguard_indonesia
DB_USERNAME=root
DB_PASSWORD=
```

## 6. Buat key dan database

```powershell
php artisan key:generate
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
```

Jika project sebelumnya sudah pernah dimigrasikan dan struktur lama bentrok:

```powershell
php artisan migrate:fresh --seed
```

Perintah `migrate:fresh` menghapus tabel lama. Gunakan hanya jika belum ada data penting.

## 7. Jalankan web

```powershell
php artisan serve
```

Buka:

```text
http://127.0.0.1:8000
```

Login admin:

```text
admin@supplyguard.test
password
```

Setelah seeder selesai, dashboard sudah berisi data demo untuk presentasi offline.

## 8. Isi atau perbarui data real-time

Buka terminal kedua tanpa menutup server:

```powershell
php artisan app:sync-countries
php artisan supplyguard:sync-economics --years=10
php artisan supplyguard:sync-weather --limit=20
php artisan supplyguard:sync-currency IDR --days=30
```

## 9. API key

Buka `.env`, lalu isi:

```env
REST_COUNTRIES_API_KEY=KEY_MILIK_KAMU
GNEWS_API_KEY=KEY_MILIK_KAMU
```

Setelah mengubah `.env`:

```powershell
php artisan config:clear
```

## 10. Import pelabuhan

Cara terminal:

```powershell
php artisan supplyguard:import-ports database/data/world_port_index_sample.csv
```

Cara web:

1. Login admin.
2. Buka Dasbor Admin.
3. Buka Kelola Pelabuhan.
4. Pilih file CSV.
5. Klik Import Sekarang.

## 11. Uji halaman

Periksa satu per satu:

1. Dasbor
2. Data Negara
3. Detail Negara
4. Perbandingan Negara
5. Pemantauan Cuaca
6. Dampak Nilai Tukar
7. Analisis Risiko
8. Visualisasi Data
9. Lokasi Pelabuhan
10. Intelijen Berita
11. Daftar Pemantauan
12. Dokumentasi REST API
13. Semua halaman Admin

## 12. Uji REST API

Contoh:

```text
http://127.0.0.1:8000/api/countries
http://127.0.0.1:8000/api/weather
http://127.0.0.1:8000/api/currency?base=USD&target=IDR
http://127.0.0.1:8000/api/ports/map
http://127.0.0.1:8000/api/news/summary
http://127.0.0.1:8000/api/risk/summary
```

Daftar lengkap tersedia di menu **Dokumentasi REST API**.

## 13. Jalankan test

```powershell
php artisan test
```

## 14. Upload GitHub

Jangan upload `.env` karena berisi password dan API key.

```powershell
git init
git add .
git commit -m "Membuat fondasi SupplyGuard Indonesia"
git branch -M main
git remote add origin URL_REPOSITORY_KAMU
git push -u origin main
```

Commit lanjutan yang disarankan:

```powershell
git add .
git commit -m "Menambahkan integrasi API dan data ekonomi"
git push

git add .
git commit -m "Menambahkan AJAX peta grafik dan risk scoring"
git push

git add .
git commit -m "Melengkapi admin REST API testing dan dokumentasi"
git push
```

## 15. Menjalankan kembali di hari berikutnya

1. Nyalakan MySQL.
2. Buka project di VS Code.
3. Jalankan `php artisan serve`.
4. Opsional jalankan `php artisan schedule:work` di terminal lain.
