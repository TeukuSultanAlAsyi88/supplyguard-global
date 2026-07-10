<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\SystemSetting;
use App\Services\NewsService;
use Illuminate\Console\Command;

class SyncCountryNews extends Command
{
    protected $signature = 'supplyguard:sync-news
                            {--limit=80 : Jumlah negara per proses}
                            {--articles=5 : Artikel per negara}
                            {--reset : Mulai lagi dari negara pertama}';

    protected $description =
        'Mengambil berita GNews untuk negara secara bertahap';

    public function handle(
        NewsService $newsService
    ): int {
        if (blank(config('services.gnews.key'))) {
            $this->error(
                'GNEWS_API_KEY belum diisi di file .env.'
            );

            return self::FAILURE;
        }

        $limit = max(
            1,
            min((int) $this->option('limit'), 90)
        );

        $articles = max(
            1,
            min((int) $this->option('articles'), 10)
        );

        $settingKey = 'news_sync_last_country_id';

        if ($this->option('reset')) {
            SystemSetting::updateOrCreate(
                ['key' => $settingKey],
                [
                    'value' => '0',
                    'type' => 'integer',
                    'description' =>
                        'ID negara terakhir sinkronisasi berita',
                ]
            );
        }

        $lastCountryId = (int) (
            SystemSetting::where(
                'key',
                $settingKey
            )->value('value') ?? 0
        );

        $countries = Country::query()
            ->where('id', '>', $lastCountryId)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        /*
         * Jika sudah sampai negara terakhir,
         * mulai lagi dari awal.
         */
        if ($countries->isEmpty()) {
            $lastCountryId = 0;

            $countries = Country::query()
                ->orderBy('id')
                ->limit($limit)
                ->get();
        }

        if ($countries->isEmpty()) {
            $this->warn(
                'Belum ada data negara di database.'
            );

            return self::SUCCESS;
        }

        $searchQuery =
            'logistics OR trade OR shipping OR economy '
            .'OR inflation OR export OR import';

        $progress = $this->output->createProgressBar(
            $countries->count()
        );

        $progress->start();

        foreach ($countries as $country) {
            try {
                /*
                 * force=true agar proses scheduler benar-benar
                 * mencoba mengambil berita dari GNews.
                 */
                $newsService->fetch(
                    country: $country,
                    query: $searchQuery,
                    limit: $articles,
                    force: true
                );

                SystemSetting::updateOrCreate(
                    ['key' => $settingKey],
                    [
                        'value' => (string) $country->id,
                        'type' => 'integer',
                        'description' =>
                            'ID negara terakhir sinkronisasi berita',
                    ]
                );
            } catch (\Throwable $exception) {
                report($exception);

                $this->newLine();

                $this->warn(
                    'Gagal mengambil berita '
                    .$country->name
                    .': '
                    .$exception->getMessage()
                );
            }

            $progress->advance();

            /*
             * Paket gratis dibatasi sekitar satu request
             * per detik. Jeda dua detik dibuat agar aman.
             */
            sleep(2);
        }

        $progress->finish();

        $this->newLine(2);

        $this->info(
            'Sinkronisasi berita selesai untuk '
            .$countries->count()
            .' negara.'
        );

        $this->comment(
            'Negara terakhir: '
            .$countries->last()->name
        );

        return self::SUCCESS;
    }
}