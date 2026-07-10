<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\CurrencyService;
use Illuminate\Console\Command;

class SyncCurrencyHistory extends Command
{
    protected $signature = 'supplyguard:sync-currency {target=IDR} {--base=USD} {--days=30} {--all}';
    protected $description = 'Sinkronkan kurs terbaru dan riwayat kurs untuk grafik tren.';

    public function handle(CurrencyService $service): int
    {
        $targets = $this->option('all')
            ? Country::whereNotNull('currency_code')->distinct()->pluck('currency_code')->filter()->unique()
            : collect([strtoupper($this->argument('target'))]);

        foreach ($targets as $target) {
            $this->line("Sinkronisasi {$this->option('base')}/{$target}...");
            $service->history($this->option('base'), $target, (int) $this->option('days'), true);
        }

        $this->info('Sinkronisasi kurs selesai.');
        return self::SUCCESS;
    }
}
