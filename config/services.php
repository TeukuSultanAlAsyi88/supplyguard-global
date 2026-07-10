<?php

return [
    'gnews' => [
        'key' => env('GNEWS_API_KEY'),
        'url' => env('GNEWS_URL', 'https://gnews.io/api/v4'),
    ],
    'rest_countries' => [
        'key' => env('REST_COUNTRIES_API_KEY'),
        'url' => env('REST_COUNTRIES_URL', 'https://api.restcountries.com/countries/v5'),
        'legacy_url' => env('REST_COUNTRIES_LEGACY_URL', 'https://restcountries.com/v3.1'),
    ],
    'world_bank' => ['url' => env('WORLD_BANK_URL', 'https://api.worldbank.org/v2')],
    'open_meteo' => ['url' => env('OPEN_METEO_URL', 'https://api.open-meteo.com/v1')],
    'exchange_rate' => ['url' => env('EXCHANGE_RATE_URL', 'https://open.er-api.com/v6')],
    'frankfurter' => ['url' => env('FRANKFURTER_URL', 'https://api.frankfurter.dev/v2')],
];
