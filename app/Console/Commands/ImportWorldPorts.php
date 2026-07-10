<?php

namespace App\Console\Commands;

use App\Services\PortImportService;
use Illuminate\Console\Command;

class ImportWorldPorts extends Command
{
    protected $signature = 'supplyguard:import-ports {file=database/data/world_port_index_sample.csv}';
    protected $description = 'Import CSV World Port Index ke tabel ports.';

    public function handle(PortImportService $service): int
    {
        $file = base_path($this->argument('file'));
        if (! is_file($file)) {
            $file = $this->argument('file');
        }
        if (! is_file($file)) {
            $this->error('File tidak ditemukan: '.$this->argument('file'));
            return self::FAILURE;
        }

        $batch = $service->import($file);
        $this->info("Selesai: {$batch->imported_rows} masuk, {$batch->skipped_rows} dilewati.");
        return self::SUCCESS;
    }
}
