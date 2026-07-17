<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function current(
        Country $country,
        bool $force = false
    ): ?WeatherData {
        $cached = $country
            ->weatherHistory()
            ->latest('observed_at')
            ->first();

        if (
            ! $force
            && $cached
            && $cached->observed_at?->gt(now()->subMinutes(30))
        ) {
            return $cached;
        }

        if (
            $country->latitude === null
            || $country->longitude === null
        ) {
            return $cached;
        }

        $latitude = (float) $country->latitude;
        $longitude = (float) $country->longitude;

        if (
            $latitude < -90
            || $latitude > 90
            || $longitude < -180
            || $longitude > 180
        ) {
            return $cached;
        }

        $baseUrl = config(
            'services.open_meteo.url',
            'https://api.open-meteo.com/v1'
        );

        $url = rtrim($baseUrl, '/') . '/forecast';

        $params = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,wind_speed_10m,wind_gusts_10m,weather_code,is_day',
            'hourly' => 'precipitation_probability',
            'forecast_hours' => 1,
            'timezone' => 'auto',
        ];

        $start = microtime(true);

        try {
            $response = Http::timeout(20)
                ->retry(2, 400)
                ->get($url, $params);

            ApiLogger::record(
                'Open-Meteo',
                $url . '?' . http_build_query($params),
                $response->status(),
                $response->successful(),
                $start
            );

            $response->throw();

            $current = $response->json('current', []);

            $wind = (float) (
                $current['wind_speed_10m']
                ?? 0
            );

            $gust = (float) (
                $current['wind_gusts_10m']
                ?? $wind
            );

            $rain = (float) (
                $current['precipitation']
                ?? 0
            );

            $probability = (float) (
                $response->json(
                    'hourly.precipitation_probability.0'
                )
                ?? 0
            );

            $weatherCode = (int) (
                $current['weather_code']
                ?? 0
            );

            $stormCodeBonus = in_array(
                $weatherCode,
                [95, 96, 99],
                true
            ) ? 30 : 0;

            $storm = min(
                100,
                ($wind * 0.55)
                + ($gust * 0.35)
                + min(20, $rain * 4)
                + ($probability * 0.15)
                + $stormCodeBonus
            );

            return WeatherData::create([
                'country_id' => $country->id,
                'temperature' => $current['temperature_2m'] ?? null,
                'apparent_temperature' => $current['apparent_temperature'] ?? null,
                'humidity' => $current['relative_humidity_2m'] ?? null,
                'precipitation' => $rain,
                'precipitation_probability' => $probability,
                'wind_speed' => $wind,
                'wind_gust' => $gust,
                'weather_code' => $weatherCode,
                'condition' => $this->condition($weatherCode),
                'is_day' => isset($current['is_day'])
                    ? (bool) $current['is_day']
                    : null,
                'storm_risk' => round($storm, 2),
                'observed_at' => $current['time'] ?? now(),
            ]);
        } catch (\Throwable $e) {
            ApiLogger::record(
                'Open-Meteo',
                $url . '?' . http_build_query($params),
                0,
                false,
                $start,
                $e->getMessage()
            );

            return $cached;
        }
    }

    public function overview(
        int $limit = 50
    ): Collection {
        $query = WeatherData::query()
            ->with('country')
            ->whereIn('id', function ($query) {
                $query
                    ->selectRaw('MAX(id)')
                    ->from('weather_data')
                    ->groupBy('country_id');
            })
            ->latest('observed_at');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function syncOverview(
        int $limit = 250
    ): Collection {
        /*
         * Dulu method ini hanya mengambil daftar negara prioritas
         * seperti ID, CN, US, JP, dan beberapa negara lain.
         *
         * Sekarang dibuat lebih global:
         * - mengambil negara yang punya latitude dan longitude
         * - memprioritaskan negara yang belum punya data cuaca
         * - tetap membatasi jumlah agar command tidak terlalu berat
         */

        if (
            $limit > 0
            && $limit < 150
        ) {
            $limit = 150;
        }

        $latestWeatherSub = WeatherData::query()
            ->selectRaw('country_id, MAX(observed_at) as last_weather_at')
            ->whereNotNull('country_id')
            ->groupBy('country_id');

        $query = Country::query()
            ->select('countries.*')
            ->leftJoinSub(
                $latestWeatherSub,
                'latest_weather',
                function ($join) {
                    $join->on(
                        'latest_weather.country_id',
                        '=',
                        'countries.id'
                    );
                }
            )
            ->whereNotNull('countries.latitude')
            ->whereNotNull('countries.longitude')
            ->whereBetween('countries.latitude', [-90, 90])
            ->whereBetween('countries.longitude', [-180, 180])
            ->orderByRaw(
                'CASE WHEN latest_weather.last_weather_at IS NULL THEN 0 ELSE 1 END'
            )
            ->orderBy('latest_weather.last_weather_at')
            ->orderBy('countries.name');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $countries = $query->get();

        foreach ($countries as $country) {
            $this->current($country);
        }

        return $this->overview(
            $limit > 0 ? $limit : 250
        );
    }

    public function condition(
        int $code
    ): string {
        return match (true) {
            $code === 0 => 'Cerah',

            in_array(
                $code,
                [1, 2, 3],
                true
            ) => 'Berawan',

            in_array(
                $code,
                [45, 48],
                true
            ) => 'Berkabut',

            in_array(
                $code,
                [51, 53, 55, 56, 57],
                true
            ) => 'Gerimis',

            in_array(
                $code,
                [61, 63, 65, 66, 67, 80, 81, 82],
                true
            ) => 'Hujan',

            in_array(
                $code,
                [71, 73, 75, 77, 85, 86],
                true
            ) => 'Salju',

            in_array(
                $code,
                [95, 96, 99],
                true
            ) => 'Badai Petir',

            default => 'Tidak diketahui',
        };
    }
}