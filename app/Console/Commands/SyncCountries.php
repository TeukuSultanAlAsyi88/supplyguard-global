<?php
namespace App\Console\Commands;use Illuminate\Console\Command;use App\Services\CountryService;
class SyncCountries extends Command { protected $signature='app:sync-countries';protected $description='Sinkronkan seluruh negara dari REST Countries';public function handle(CountryService $s):int{$this->info('Mengambil data negara...');$count=$s->syncCountries();$count?$this->info("$count negara berhasil disinkronkan."):$this->error('Sinkronisasi gagal.');return $count?self::SUCCESS:self::FAILURE;} }
