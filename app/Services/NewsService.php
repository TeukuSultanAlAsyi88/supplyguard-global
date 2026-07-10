<?php

namespace App\Services;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\NewsSentiment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class NewsService
{
    public function __construct(
        private SentimentService $sentiment
    ) {
    }

    /**
     * Mengambil berita berdasarkan negara.
     *
     * Jika berita negara tersebut masih baru, data akan diambil
     * dari database agar kuota GNews tidak cepat habis.
     */
    public function fetch(
        ?Country $country = null,
        string $query = 'logistics OR trade OR shipping OR economy OR inflation OR export OR import',
        int $limit = 10,
        bool $force = false
    ): Collection {
        /*
         * Paket gratis GNews maksimal mengembalikan
         * 10 artikel per request.
         */
        $limit = max(1, min($limit, 10));

        /*
         * Jangan memanggil GNews berulang kali jika berita
         * negara tersebut sudah diperbarui dalam 12 jam terakhir.
         */
        if (! $force && $this->hasFreshCache($country)) {
            return $this->cached($country, $limit);
        }

        $apiKey = config('services.gnews.key');

        /*
         * Jika API key belum diisi, tampilkan data yang
         * sudah tersimpan di database.
         */
        if (blank($apiKey)) {
            return $this->cached($country, $limit);
        }

        $baseUrl = rtrim(
            config('services.gnews.url', 'https://gnews.io/api/v4'),
            '/'
        );

        $url = $baseUrl.'/search';

        $query = trim($query);

        if ($query === '') {
            $query =
                'logistics OR trade OR shipping OR economy '
                .'OR inflation OR export OR import';
        }

        /*
         * Contoh query:
         *
         * "Indonesia" AND
         * (logistics OR trade OR shipping OR economy)
         */
        if ($country) {
            $countryName = str_replace(
                '"',
                '',
                $country->name
            );

            $search = sprintf(
                '"%s" AND (%s)',
                $countryName,
                $query
            );
        } else {
            $search = '('.$query.')';
        }

        $params = [
            'q' => $search,
            'lang' => 'en',
            'max' => $limit,
            'in' => 'title,description',
            'sortby' => 'publishedAt',
            'apikey' => $apiKey,
        ];

        $startedAt = microtime(true);

        try {
            $response = Http::timeout(30)
                ->retry(2, 500)
                ->acceptJson()
                ->get($url, $params);

            ApiLogger::record(
                'GNews',
                $url.'?q='.urlencode($search),
                $response->status(),
                $response->successful(),
                $startedAt
            );

            $response->throw();

            $articles = $response->json('articles', []);

            foreach ($articles as $article) {
                $title = trim(
                    $article['title'] ?? 'Tanpa Judul'
                );

                $description = trim(
                    $article['description'] ?? ''
                );

                $analysis = $this->sentiment->analyze(
                    $title.' '.$description
                );

                $articleUrl = $article['url'] ?? null;

                /*
                 * URL digunakan sebagai identitas utama agar
                 * artikel yang sama tidak tersimpan berulang kali.
                 */
                $identity = filled($articleUrl)
                    ? ['url' => $articleUrl]
                    : [
                        'title' => $title,
                        'country_id' => $country?->id,
                    ];

                $news = NewsCache::updateOrCreate(
                    $identity,
                    [
                        'country_id' => $country?->id,
                        'title' => $title,
                        'description' => $description ?: null,
                        'url' => $articleUrl,
                        'image_url' => $article['image'] ?? null,
                        'source' =>
                            $article['source']['name']
                            ?? 'GNews',
                        'published_at' =>
                            $article['publishedAt']
                            ?? now(),
                        'sentiment' => $analysis['sentiment'],
                        'positive_score' =>
                            $analysis['positive'],
                        'negative_score' =>
                            $analysis['negative'],
                        'query' => $search,
                        'language' =>
                            $article['lang'] ?? 'en',
                    ]
                );

                NewsSentiment::updateOrCreate(
                    [
                        'news_cache_id' => $news->id,
                    ],
                    [
                        'sentiment' =>
                            $analysis['sentiment'],

                        'positive_count' =>
                            $analysis['positive'],

                        'negative_count' =>
                            $analysis['negative'],

                        'neutral_count' =>
                            $analysis['positive'] === 0
                            && $analysis['negative'] === 0
                                ? 1
                                : 0,

                        'matched_positive' =>
                            $analysis['matched_positive'],

                        'matched_negative' =>
                            $analysis['matched_negative'],
                    ]
                );
            }
        } catch (\Throwable $exception) {
            ApiLogger::record(
                'GNews',
                $url,
                0,
                false,
                $startedAt,
                $exception->getMessage()
            );

            report($exception);
        }

        return $this->cached($country, $limit);
    }

    /**
     * Memeriksa apakah berita negara sudah diperbarui
     * dalam 12 jam terakhir.
     */
    private function hasFreshCache(
        ?Country $country
    ): bool {
        return NewsCache::query()
            ->when(
                $country,
                fn ($query) =>
                    $query->where(
                        'country_id',
                        $country->id
                    ),
                fn ($query) =>
                    $query->whereNull('country_id')
            )
            ->where(
                'updated_at',
                '>=',
                now()->subHours(12)
            )
            ->exists();
    }

    /**
     * Mengambil berita yang sudah disimpan di database.
     */
    private function cached(
        ?Country $country,
        int $limit
    ): Collection {
        return NewsCache::with([
            'country',
            'analysis',
        ])
            ->when(
                $country,
                fn ($query) =>
                    $query->where(
                        'country_id',
                        $country->id
                    ),
                fn ($query) =>
                    $query->whereNull('country_id')
            )
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}