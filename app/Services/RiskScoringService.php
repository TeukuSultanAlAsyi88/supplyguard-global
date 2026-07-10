<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class RiskScoringService
{
    public function __construct(
        private CountryService $countries,
        private WeatherService $weather,
        private CurrencyService $currency,
        private NewsService $news,
    ) {
    }

    public function calculate(Country $country): RiskScore
    {
        $economic = $this->countries->economic($country);
        $weather = $this->weather->current($country);
        $rate = $country->currency_code ? $this->currency->rate('USD', $country->currency_code) : null;
        $news = $this->news->fetch($country, 'logistics trade shipping economy geopolitics', 20);

        $weatherScore = min(100, max(0, (float) ($weather?->storm_risk ?? 20)));
        $inflation = (float) ($economic?->inflation ?? 5);
        $inflationScore = min(100, max(0, $inflation * 8));
        $currencyChange = abs((float) ($rate?->change_percent ?? 0));
        $currencyScore = min(100, $currencyChange * 20);
        $negative = $news->where('sentiment', 'Negatif')->count();
        $newsScore = $news->count() ? ($negative / $news->count()) * 100 : 25;

        $weights = [
            'Cuaca' => (float) SystemSetting::where('key', 'risk_weather_weight')->value('value') ?: 30,
            'Inflasi' => (float) SystemSetting::where('key', 'risk_inflation_weight')->value('value') ?: 20,
            'Berita' => (float) SystemSetting::where('key', 'risk_news_weight')->value('value') ?: 40,
            'Mata Uang' => (float) SystemSetting::where('key', 'risk_currency_weight')->value('value') ?: 10,
        ];
        $weightTotal = array_sum($weights) ?: 100;
        $weights = array_map(fn ($weight) => ($weight / $weightTotal) * 100, $weights);

        $total = round(
            $weatherScore * ($weights['Cuaca'] / 100)
            + $inflationScore * ($weights['Inflasi'] / 100)
            + $newsScore * ($weights['Berita'] / 100)
            + $currencyScore * ($weights['Mata Uang'] / 100),
            2
        );
        $level = $total <= 30 ? 'Rendah' : ($total <= 60 ? 'Sedang' : 'Tinggi');

        return DB::transaction(function () use (
            $country, $weatherScore, $inflationScore, $currencyScore, $newsScore,
            $total, $level, $weights, $weather, $inflation, $currencyChange, $negative, $news
        ) {
            $score = RiskScore::create([
                'country_id' => $country->id,
                'weather_score' => round($weatherScore, 2),
                'inflation_score' => round($inflationScore, 2),
                'currency_score' => round($currencyScore, 2),
                'news_score' => round($newsScore, 2),
                'total_score' => $total,
                'risk_level' => $level,
                'calculated_at' => now(),
            ]);

            $components = [
                ['Cuaca', $weather?->storm_risk, $weatherScore, $weights['Cuaca'], 'Risiko badai, hujan, angin, dan hembusan angin.'],
                ['Inflasi', $inflation, $inflationScore, $weights['Inflasi'], 'Inflasi dinormalisasi ke skala 0–100.'],
                ['Berita', $negative, $newsScore, $weights['Berita'], $negative.' berita negatif dari '.$news->count().' berita.'],
                ['Mata Uang', $currencyChange, $currencyScore, $weights['Mata Uang'], 'Perubahan kurs absolut dibanding data sebelumnya.'],
            ];

            foreach ($components as [$name, $raw, $normalized, $weight, $notes]) {
                $score->components()->create([
                    'component' => $name,
                    'raw_value' => $raw,
                    'normalized_score' => round($normalized, 2),
                    'weight' => round($weight, 2),
                    'weighted_score' => round($normalized * ($weight / 100), 2),
                    'notes' => $notes,
                ]);
            }

            return $score->load(['country', 'components']);
        });
    }
}
