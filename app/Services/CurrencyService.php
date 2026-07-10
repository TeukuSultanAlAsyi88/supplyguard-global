<?php

namespace App\Services;

use App\Models\CurrencyRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    public function rate(string $base = 'USD', string $target = 'IDR', bool $force = false): ?CurrencyRate
    {
        $base = strtoupper($base);
        $target = strtoupper($target);
        $cached = CurrencyRate::where('base_currency', $base)
            ->where('target_currency', $target)
            ->latest('recorded_at')
            ->first();

        if (! $force
            && $cached
            && $cached->source !== 'Data Demo Seeder'
            && $cached->recorded_at->gt(now()->subHours(6))) {
            return $cached;
        }

        $url = rtrim(config('services.exchange_rate.url'), '/').'/latest/'.$base;
        $start = microtime(true);

        try {
            $response = Http::timeout(20)->retry(2, 400)->get($url);
            ApiLogger::record('ExchangeRate-API', $url, $response->status(), $response->successful(), $start);
            $response->throw();

            $rate = $response->json('rates.'.$target);
            if (! $rate) {
                return $cached;
            }

            $previous = $cached;
            $change = $previous && $previous->rate > 0
                ? (($rate - $previous->rate) / $previous->rate) * 100
                : 0;
            $updatedAt = $response->json('time_last_update_unix');
            $recordedAt = $updatedAt ? now()->setTimestamp((int) $updatedAt) : now();

            return CurrencyRate::updateOrCreate(
                [
                    'base_currency' => $base,
                    'target_currency' => $target,
                    'rate_date' => $recordedAt->toDateString(),
                    'source' => 'ExchangeRate-API',
                ],
                [
                    'rate' => $rate,
                    'change_percent' => round($change, 4),
                    'recorded_at' => $recordedAt,
                ]
            );
        } catch (\Throwable $e) {
            ApiLogger::record('ExchangeRate-API', $url, 0, false, $start, $e->getMessage());
            return $cached;
        }
    }

    public function history(string $base = 'USD', string $target = 'IDR', int $days = 30, bool $force = false): Collection
    {
        $base = strtoupper($base);
        $target = strtoupper($target);
        $days = max(7, min($days, 365));
        $from = now()->subDays($days)->toDateString();
        $to = now()->toDateString();

        $existing = $this->storedHistory($base, $target, $from);
        $hasExternalHistory = $existing->contains(fn (CurrencyRate $rate) => $rate->source !== 'Data Demo Seeder');

        if (! $force && $hasExternalHistory && $existing->count() >= min(10, (int) ($days / 2))) {
            return $existing;
        }

        $url = rtrim(config('services.frankfurter.url'), '/').'/rates';
        $params = ['from' => $from, 'to' => $to, 'base' => $base, 'quotes' => $target];
        $start = microtime(true);

        try {
            $response = Http::timeout(25)->retry(2, 400)->get($url, $params);
            ApiLogger::record('Frankfurter Historical FX', $url.'?'.http_build_query($params), $response->status(), $response->successful(), $start);
            $response->throw();

            $payload = $response->json();
            $rows = array_is_list($payload) ? $payload : [];

            // Compatibility with Frankfurter v1-style nested responses.
            if (! $rows && isset($payload['rates']) && is_array($payload['rates'])) {
                foreach ($payload['rates'] as $date => $rates) {
                    $rows[] = ['date' => $date, 'base' => $payload['base'] ?? $base, 'quote' => $target, 'rate' => $rates[$target] ?? null];
                }
            }

            $previousRate = null;
            foreach ($rows as $row) {
                $quote = strtoupper($row['quote'] ?? $row['currency'] ?? $target);
                $rowBase = strtoupper($row['base'] ?? $base);
                $date = $row['date'] ?? null;
                $value = $row['rate'] ?? null;

                if ($rowBase !== $base || $quote !== $target || ! $date || ! is_numeric($value)) {
                    continue;
                }

                $change = $previousRate && $previousRate > 0 ? (($value - $previousRate) / $previousRate) * 100 : 0;
                CurrencyRate::updateOrCreate(
                    ['base_currency' => $base, 'target_currency' => $target, 'rate_date' => $date, 'source' => 'Frankfurter'],
                    ['rate' => $value, 'change_percent' => round($change, 4), 'recorded_at' => $date.' 12:00:00']
                );
                $previousRate = (float) $value;
            }
        } catch (\Throwable $e) {
            ApiLogger::record('Frankfurter Historical FX', $url, 0, false, $start, $e->getMessage());
        }

        $this->rate($base, $target);

        return $this->storedHistory($base, $target, $from);
    }

    private function storedHistory(string $base, string $target, string $from): Collection
    {
        return CurrencyRate::where('base_currency', $base)
            ->where('target_currency', $target)
            ->whereDate('rate_date', '>=', $from)
            ->orderBy('rate_date')
            ->get()
            ->groupBy(fn (CurrencyRate $rate) => (string) $rate->rate_date)
            ->map(fn (Collection $sameDate) => $sameDate
                ->sortBy(fn (CurrencyRate $rate) => $rate->source === 'Data Demo Seeder' ? 1 : 0)
                ->first())
            ->sortBy('rate_date')
            ->values();
    }

    public function convert(float $amount, string $base, string $target): ?float
    {
        $rate = $this->rate($base, $target);
        return $rate ? round($amount * $rate->rate, 2) : null;
    }
}
