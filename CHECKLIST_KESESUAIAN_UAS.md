# Checklist Kesesuaian Project Final

## Teknologi

- [x] PHP
- [x] Laravel
- [x] MySQL
- [x] Bootstrap 5
- [x] AJAX menggunakan Fetch API
- [x] JavaScript ES6
- [x] Chart.js
- [x] Leaflet.js
- [x] Struktur repository dan workflow GitHub Actions sudah disiapkan
- [ ] Repository perlu dipush ke akun GitHub pengguna
- [x] Docker tidak digunakan karena bersifat opsional

## API dan sumber data

- [x] Open-Meteo
- [x] World Bank
- [x] REST Countries
- [x] ExchangeRate-API
- [x] Frankfurter untuk kebutuhan tren kurs
- [x] GNews
- [x] World Port Index CSV
- [x] OpenStreetMap

## Sepuluh fitur utama

- [x] Global Country Dashboard
- [x] Risk Scoring Engine
- [x] Global Weather Monitoring
- [x] Currency Impact Dashboard
- [x] News Intelligence
- [x] Port Location Dashboard
- [x] Data Visualization Dashboard
- [x] Country Comparison Engine
- [x] Favorite Monitoring List
- [x] Admin Dashboard

## Data science sederhana

- [x] Tabel kata positif
- [x] Tabel kata negatif
- [x] Lexicon Based Sentiment Analysis dengan PHP
- [x] Jumlah kata yang cocok
- [x] Persentase sentimen
- [x] Weighted Risk Model
- [x] Rincian kontribusi bobot

## Database

Project memiliki 18 tabel domain SupplyGuard ditambah 8 tabel infrastruktur Laravel. Struktur melampaui kebutuhan contoh 15–20 tabel tanpa menggabungkan data yang seharusnya terpisah.

## REST API

- [x] `/api/countries`
- [x] `/api/risk`
- [x] `/api/ports`
- [x] `/api/news`
- [x] `/api/currency`
- [x] Total 35 endpoint API

## Hal yang perlu dilakukan di laptop sebelum pengumpulan

Bagian berikut bukan kekurangan coding, tetapi konfigurasi/runtime:

- [ ] Menjalankan `composer install`
- [ ] Membuat database MySQL
- [ ] Menjalankan `php artisan migrate --seed`
- [ ] Mengisi GNews API key
- [ ] Mengisi REST Countries v5 API key atau memakai data seeder/fallback
- [ ] Mengimpor dataset World Port Index lengkap bila dosen meminta seluruh record
- [ ] Menjalankan seluruh menu dan mengambil screenshot
- [ ] Menjalankan `php artisan test`
- [ ] Push repository ke GitHub
- [ ] Menyiapkan presentasi dan dokumentasi hasil

## Kesimpulan

Dari sisi rancangan dan coding, seluruh komponen yang tertulis pada spesifikasi PDF sudah mempunyai implementasi. Seeder juga menyediakan data demo lengkap agar dashboard tidak kosong sebelum sinkronisasi API. Hasil data real-time tetap bergantung pada internet, ketersediaan layanan eksternal, dan API key pengguna.
