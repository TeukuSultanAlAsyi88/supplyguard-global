# Mulai SupplyGuard Indonesia

Urutan singkat:

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Login admin:

```text
admin@supplyguard.test
password
```

Panduan sangat lengkap tersedia pada `PANDUAN_MENJALANKAN_LENGKAP.md`.
