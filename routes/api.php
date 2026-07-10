<?php

use App\Http\Controllers\Api\{
    ArticleApiController,
    ComparisonApiController,
    CountryApiController,
    CurrencyApiController,
    DashboardApiController,
    NewsApiController,
    PortApiController,
    RiskApiController,
    SentimentApiController,
    WeatherApiController
};
use Illuminate\Support\Facades\Route;

// Negara (8 endpoint)
Route::get('/countries', [CountryApiController::class, 'index']);
Route::get('/countries/regions', [CountryApiController::class, 'regions']);
Route::get('/countries/{country}', [CountryApiController::class, 'show']);
Route::get('/countries/{country}/economics', [CountryApiController::class, 'economics']);
Route::get('/countries/{country}/economics/history', [CountryApiController::class, 'economicHistory']);
Route::get('/countries/{country}/weather', [CountryApiController::class, 'weather']);
Route::get('/countries/{country}/ports', [CountryApiController::class, 'ports']);
Route::get('/countries/{country}/risks', [CountryApiController::class, 'riskHistory']);

// Cuaca (3 endpoint)
Route::get('/weather', [WeatherApiController::class, 'index']);
Route::get('/weather/{country}', [WeatherApiController::class, 'show']);
Route::get('/weather/{country}/history', [WeatherApiController::class, 'history']);

// Nilai tukar (4 endpoint)
Route::get('/currency', [CurrencyApiController::class, 'index']);
Route::get('/currency/history', [CurrencyApiController::class, 'history']);
Route::get('/currency/convert', [CurrencyApiController::class, 'convert']);
Route::get('/currency/pairs', [CurrencyApiController::class, 'pairs']);

// Risiko (5 endpoint)
Route::get('/risk', [RiskApiController::class, 'index']);
Route::get('/risk/summary', [RiskApiController::class, 'summary']);
Route::get('/risk/country/{country}', [RiskApiController::class, 'show']);
Route::post('/risk/country/{country}/calculate', [RiskApiController::class, 'calculate']);
Route::get('/risk/{riskScore}/components', [RiskApiController::class, 'components']);

// Pelabuhan (4 endpoint)
Route::get('/ports', [PortApiController::class, 'index']);
Route::get('/ports/map', [PortApiController::class, 'map']);
Route::get('/ports/countries', [PortApiController::class, 'countries']);
Route::get('/ports/{port}', [PortApiController::class, 'show']);

// Berita dan sentimen (5 endpoint)
Route::get('/news', [NewsApiController::class, 'index']);
Route::get('/news/summary', [NewsApiController::class, 'summary']);
Route::post('/news/analyze', [NewsApiController::class, 'analyze']);
Route::get('/news/{news}', [NewsApiController::class, 'show']);
Route::get('/sentiment/words', [SentimentApiController::class, 'words']);

// Analitik dan pendukung keputusan (4 endpoint)
Route::get('/comparison', [ComparisonApiController::class, 'compare']);
Route::get('/dashboard/summary', [DashboardApiController::class, 'summary']);
Route::get('/dashboard/charts', [DashboardApiController::class, 'charts']);
Route::get('/integrations/status', [DashboardApiController::class, 'integrations']);

// Artikel analisis (2 endpoint)
Route::get('/articles', [ArticleApiController::class, 'index']);
Route::get('/articles/{article}', [ArticleApiController::class, 'show']);
