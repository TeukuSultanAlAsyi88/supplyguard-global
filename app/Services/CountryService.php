<?php

namespace App\Services;

use App\Models\Country;
use App\Models\CountryEconomic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CountryService
{
    public function syncCountries(): int
    {
        $key = config('services.rest_countries.key');

        if ($key) {
            $count = $this->syncFromV5($key);
            if ($count > 0) {
                return $count;
            }
        }

        return $this->syncFromLegacy();
    }

    private function syncFromV5(string $key): int
    {
        $baseUrl = rtrim(config('services.rest_countries.url'), '/');
        $offset = 0;
        $count = 0;
        $more = true;

        while ($more && $offset < 500) {
            $url = $baseUrl;
            $params = [
                'limit' => 100,
                'offset' => $offset,
                'response_fields_omit' => 'names.translations,leaders',
            ];
            $start = microtime(true);

            try {
                $response = Http::withToken($key)->timeout(30)->retry(2, 500)->get($url, $params);
                ApiLogger::record('REST Countries v5', $url.'?'.http_build_query($params), $response->status(), $response->successful(), $start);
                $response->throw();

                $objects = $response->json('data.objects', []);
                foreach ($objects as $item) {
                    if ($this->saveCountry($item)) {
                        $count++;
                    }
                }

                $more = (bool) $response->json('data.meta.more', false);
                $offset += 100;
            } catch (\Throwable $e) {
                ApiLogger::record('REST Countries v5', $url, 0, false, $start, $e->getMessage());
                break;
            }
        }

        if ($count > 0) {
            Cache::forget('countries.list');
        }

        return $count;
    }

    private function syncFromLegacy(): int
    {
        $url = rtrim(config('services.rest_countries.legacy_url'), '/').'/all';
        $params = ['fields' => 'name,cca2,cca3,region,subregion,capital,currencies,languages,flags,latlng,population'];
        $start = microtime(true);

        try {
            $response = Http::timeout(30)->retry(2, 500)->get($url, $params);
            ApiLogger::record('REST Countries Legacy', $url.'?'.http_build_query($params), $response->status(), $response->successful(), $start);
            $response->throw();

            $count = 0;
            foreach ($response->json() as $item) {
                if ($this->saveCountry($item)) {
                    $count++;
                }
            }

            Cache::forget('countries.list');
            return $count;
        } catch (\Throwable $e) {
            ApiLogger::record('REST Countries Legacy', $url, 0, false, $start, $e->getMessage());
            return 0;
        }
    }

    private function saveCountry(array $item): bool
    {
        $code = data_get($item, 'codes.alpha_2') ?: ($item['cca2'] ?? null);
        if (! $code || strlen($code) !== 2) {
            return false;
        }

        $currencies = $item['currencies'] ?? [];
        $currencyCode = null;
        $currencyName = null;

        if (is_array($currencies) && $currencies !== []) {
            if (array_is_list($currencies)) {
                $currencyCode = data_get($currencies, '0.code') ?: data_get($currencies, '0.iso_code');
                $currencyName = data_get($currencies, '0.name');
            } else {
                $currencyCode = array_key_first($currencies);
                $currencyName = data_get($currencies, $currencyCode.'.name');
            }
        }

        $languages = $item['languages'] ?? [];
        if (array_is_list($languages)) {
            $languageNames = collect($languages)->map(fn ($language) => is_array($language) ? ($language['name'] ?? $language['english_name'] ?? null) : $language)->filter();
        } else {
            $languageNames = collect(array_values($languages))->map(fn ($language) => is_array($language) ? ($language['name'] ?? null) : $language)->filter();
        }

        $capital = $item['capitals'] ?? ($item['capital'] ?? []);
        $capital = is_array($capital) ? collect($capital)->map(fn ($value) => is_array($value) ? ($value['name'] ?? null) : $value)->filter()->implode(', ') : $capital;

        $latitude = data_get($item, 'coordinates.lat') ?? data_get($item, 'latlng.0');
        $longitude = data_get($item, 'coordinates.lng') ?? data_get($item, 'latlng.1');

        $flagUrl = data_get($item, 'flag.url_svg')
            ?: data_get($item, 'flag.url_png')
            ?: data_get($item, 'flag.svg')
            ?: data_get($item, 'flag.png')
            ?: data_get($item, 'flags.svg')
            ?: data_get($item, 'flags.png')
            ?: 'https://flagcdn.com/'.strtolower($code).'.svg';

        Country::updateOrCreate(
            ['code' => strtoupper($code)],
            [
                'name' => data_get($item, 'names.common') ?: data_get($item, 'name.common') ?: $code,
                'official_name' => data_get($item, 'names.official') ?: data_get($item, 'name.official'),
                'cca3' => data_get($item, 'codes.alpha_3') ?: ($item['cca3'] ?? null),
                'region' => $item['region'] ?? null,
                'subregion' => $item['subregion'] ?? null,
                'capital' => $capital,
                'currency_code' => $currencyCode,
                'currency_name' => $currencyName,
                'language' => $languageNames->implode(', '),
                'flag_url' => $flagUrl,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'population' => $item['population'] ?? null,
            ]
        );

        return true;
    }

    public function economic(Country $country): ?CountryEconomic
    {
        return $this->economicHistory($country, 10)->sortByDesc('year')->first();
    }

    public function economicHistory(Country $country, int $years = 10, bool $force = false): Collection
    {
        $years = max(3, min($years, 30));
        $existing = $country->economics()->orderBy('year')->get();
        $fresh = $existing->count() >= min(5, $years)
            && optional($existing->max('updated_at'))->gt(now()->subDays(7));

        if (! $force && $fresh) {
            return $existing->take(-$years)->values();
        }

        $endYear = (int) now()->format('Y');
        $startYear = $endYear - $years;
        $indicators = [
            'NY.GDP.MKTP.CD' => 'gdp',
            'FP.CPI.TOTL.ZG' => 'inflation',
            'NE.EXP.GNFS.CD' => 'exports',
            'NE.IMP.GNFS.CD' => 'imports',
            'SP.POP.TOTL' => 'population',
        ];
        $byYear = [];

        foreach ($indicators as $indicator => $field) {
            $url = rtrim(config('services.world_bank.url'), '/').'/country/'.$country->code.'/indicator/'.$indicator;
            $params = [
                'format' => 'json',
                'date' => $startYear.':'.$endYear,
                'per_page' => 100,
            ];
            $start = microtime(true);

            try {
                $response = Http::timeout(25)->retry(2, 400)->get($url, $params);
                ApiLogger::record('World Bank', $url.'?'.http_build_query($params), $response->status(), $response->successful(), $start);
                if (! $response->successful()) {
                    continue;
                }

                foreach ($response->json('1', []) as $row) {
                    if (($row['value'] ?? null) === null || ! is_numeric($row['date'] ?? null)) {
                        continue;
                    }
                    $year = (int) $row['date'];
                    $byYear[$year][$field] = $row['value'];
                }
            } catch (\Throwable $e) {
                ApiLogger::record('World Bank', $url, 0, false, $start, $e->getMessage());
            }
        }

        foreach ($byYear as $year => $values) {
            CountryEconomic::updateOrCreate(
                ['country_id' => $country->id, 'year' => $year],
                array_merge($values, ['country_id' => $country->id, 'year' => $year])
            );
        }

        return $country->economics()->orderBy('year')->get()->take(-$years)->values();
    }
}
