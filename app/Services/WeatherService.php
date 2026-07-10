<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function current(Country $country, bool $force = false): ?WeatherData
    {
        $cached = $country->weatherHistory()->latest('observed_at')->first();
        if (! $force && $cached && $cached->observed_at?->gt(now()->subMinutes(30))) {
            return $cached;
        }
        if ($country->latitude === null || $country->longitude === null) {
            return $cached;
        }

        $url = rtrim(config('services.open_meteo.url'), '/').'/forecast';
        $params = [
            'latitude' => $country->latitude,
            'longitude' => $country->longitude,
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,wind_speed_10m,wind_gusts_10m,weather_code,is_day',
            'hourly' => 'precipitation_probability',
            'forecast_hours' => 1,
            'timezone' => 'auto',
        ];
        $start = microtime(true);

        try {
            $response = Http::timeout(20)->retry(2, 400)->get($url, $params);
            ApiLogger::record('Open-Meteo', $url.'?'.http_build_query($params), $response->status(), $response->successful(), $start);
            $response->throw();

            $current = $response->json('current', []);
            $wind = (float) ($current['wind_speed_10m'] ?? 0);
            $gust = (float) ($current['wind_gusts_10m'] ?? $wind);
            $rain = (float) ($current['precipitation'] ?? 0);
            $probability = (float) ($response->json('hourly.precipitation_probability.0') ?? 0);
            $weatherCode = (int) ($current['weather_code'] ?? 0);
            $stormCodeBonus = in_array($weatherCode, [95, 96, 99], true) ? 30 : 0;
            $storm = min(100, ($wind * .55) + ($gust * .35) + min(20, $rain * 4) + ($probability * .15) + $stormCodeBonus);

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
                'is_day' => isset($current['is_day']) ? (bool) $current['is_day'] : null,
                'storm_risk' => round($storm, 2),
                'observed_at' => $current['time'] ?? now(),
            ]);
        } catch (\Throwable $e) {
            ApiLogger::record('Open-Meteo', $url, 0, false, $start, $e->getMessage());
            return $cached;
        }
    }

    public function overview(int $limit = 50): Collection
    {
        return WeatherData::query()
            ->with('country')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')->from('weather_data')->groupBy('country_id');
            })
            ->latest('observed_at')
            ->limit($limit)
            ->get();
    }

    public function syncOverview(int $limit = 20): Collection
    {
        $priority = ['ID', 'CN', 'DE', 'AU', 'US', 'JP', 'SG', 'MY', 'IN', 'GB', 'BR', 'ZA', 'AE', 'SA', 'KR', 'FR', 'NL', 'CA', 'MX', 'TH'];
        $countries = Country::whereIn('code', array_slice($priority, 0, $limit))->get();
        foreach ($countries as $country) {
            $this->current($country);
        }
        return $this->overview($limit);
    }

    public function condition(int $code): string
    {
        return match (true) {
            $code === 0 => 'Cerah',
            in_array($code, [1, 2, 3], true) => 'Berawan',
            in_array($code, [45, 48], true) => 'Berkabut',
            in_array($code, [51, 53, 55, 56, 57], true) => 'Gerimis',
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82], true) => 'Hujan',
            in_array($code, [71, 73, 75, 77, 85, 86], true) => 'Salju',
            in_array($code, [95, 96, 99], true) => 'Badai Petir',
            default => 'Tidak diketahui',
        };
    }
}
