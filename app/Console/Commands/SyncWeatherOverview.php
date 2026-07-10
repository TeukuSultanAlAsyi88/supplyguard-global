<?php

namespace App\Console\Commands;

use App\Services\WeatherService;
use Illuminate\Console\Command;

class SyncWeatherOverview extends Command
{
    protected $signature = 'supplyguard:sync-weather {--limit=20}';
    protected $description = 'Sinkronkan cuaca negara prioritas untuk peta cuaca global.';

    public function handle(WeatherService $service): int
    {
        $data = $service->syncOverview((int) $this->option('limit'));
        $this->info("Cuaca {$data->count()} negara tersedia pada peta global.");
        return self::SUCCESS;
    }
}
