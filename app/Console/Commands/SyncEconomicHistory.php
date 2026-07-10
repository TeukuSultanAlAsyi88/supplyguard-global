<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Console\Command;

class SyncEconomicHistory extends Command
{
    protected $signature = 'supplyguard:sync-economics {country? : Kode ISO2 negara} {--years=10} {--all}';
    protected $description = 'Sinkronkan riwayat GDP, inflasi, populasi, ekspor, dan impor dari World Bank.';

    public function handle(CountryService $service): int
    {
        $query = Country::query();
        if ($code = $this->argument('country')) {
            $query->where('code', strtoupper($code));
        } elseif (! $this->option('all')) {
            $query->whereIn('code', ['ID', 'CN', 'DE', 'AU', 'US', 'JP', 'SG', 'MY', 'IN', 'GB']);
        }

        $countries = $query->orderBy('name')->get();
        if ($countries->isEmpty()) {
            $this->error('Negara tidak ditemukan.');
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($countries->count());
        foreach ($countries as $country) {
            $service->economicHistory($country, (int) $this->option('years'), true);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);
        $this->info('Riwayat ekonomi berhasil disinkronkan.');
        return self::SUCCESS;
    }
}
