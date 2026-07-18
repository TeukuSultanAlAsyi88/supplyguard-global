@extends('layouts.app')

@section('title', 'Perbandingan Negara')
@section('heading', 'Perbandingan Negara')

@php
    $countries = $countries ?? collect();

    $selectedAId = request('a');
    $selectedBId = request('b');

    $countryA = $countryA
        ?? $firstCountry
        ?? $aCountry
        ?? ($countries instanceof \Illuminate\Support\Collection
            ? $countries->firstWhere('id', (int) $selectedAId)
            : null);

    $countryB = $countryB
        ?? $secondCountry
        ?? $bCountry
        ?? ($countries instanceof \Illuminate\Support\Collection
            ? $countries->firstWhere('id', (int) $selectedBId)
            : null);

    if (! $countryA && $countries instanceof \Illuminate\Support\Collection) {
        $countryA = $countries->first();
    }

    if (! $countryB && $countries instanceof \Illuminate\Support\Collection) {
        $countryB = $countries->skip(1)->first() ?? $countries->first();
    }

    $flag = function ($country) {
        if (! $country) {
            return '🌐';
        }

        if (! empty($country->flag_emoji)) {
            return $country->flag_emoji;
        }

        if (! empty($country->flag)) {
            return $country->flag;
        }

        $code = strtoupper((string) ($country->code ?? ''));

        if (strlen($code) === 2) {
            return mb_chr(127397 + ord($code[0])) . mb_chr(127397 + ord($code[1]));
        }

        return '🌐';
    };

    $latestEconomic = function ($country) {
        if (! $country) {
            return null;
        }

        if (isset($country->latestEconomic)) {
            return $country->latestEconomic;
        }

        if (method_exists($country, 'latestEconomic')) {
            return $country->latestEconomic()->first();
        }

        if (method_exists($country, 'economics')) {
            return $country->economics()->latest('year')->first();
        }

        return null;
    };

    $latestWeather = function ($country) {
        if (! $country) {
            return null;
        }

        if (isset($country->latestWeather)) {
            return $country->latestWeather;
        }

        if (method_exists($country, 'latestWeather')) {
            return $country->latestWeather()->first();
        }

        if (method_exists($country, 'weatherHistory')) {
            return $country->weatherHistory()->latest('observed_at')->first();
        }

        return null;
    };

    $latestRisk = function ($country) {
        if (! $country) {
            return null;
        }

        if (isset($country->latestRisk)) {
            return $country->latestRisk;
        }

        if (method_exists($country, 'latestRisk')) {
            return $country->latestRisk()->first();
        }

        if (method_exists($country, 'risks')) {
            return $country->risks()->latest('calculated_at')->first();
        }

        return null;
    };

    $safeColumns = function (string $table) {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
                return [];
            }

            return \Illuminate\Support\Facades\Schema::getColumnListing($table);
        } catch (\Throwable $exception) {
            return [];
        }
    };

    $currencyFallbackMap = [
        'AF' => 'AFN',
        'AL' => 'ALL',
        'DZ' => 'DZD',
        'AD' => 'EUR',
        'AO' => 'AOA',
        'AR' => 'ARS',
        'AM' => 'AMD',
        'AU' => 'AUD',
        'AT' => 'EUR',
        'AZ' => 'AZN',
        'BH' => 'BHD',
        'BD' => 'BDT',
        'BY' => 'BYN',
        'BE' => 'EUR',
        'BZ' => 'BZD',
        'BJ' => 'XOF',
        'BT' => 'BTN',
        'BO' => 'BOB',
        'BA' => 'BAM',
        'BW' => 'BWP',
        'BR' => 'BRL',
        'BN' => 'BND',
        'BG' => 'BGN',
        'BF' => 'XOF',
        'BI' => 'BIF',
        'KH' => 'KHR',
        'CM' => 'XAF',
        'CA' => 'CAD',
        'CV' => 'CVE',
        'CF' => 'XAF',
        'TD' => 'XAF',
        'CL' => 'CLP',
        'CN' => 'CNY',
        'CO' => 'COP',
        'KM' => 'KMF',
        'CG' => 'XAF',
        'CD' => 'CDF',
        'CR' => 'CRC',
        'CI' => 'XOF',
        'HR' => 'EUR',
        'CU' => 'CUP',
        'CY' => 'EUR',
        'CZ' => 'CZK',
        'DK' => 'DKK',
        'DJ' => 'DJF',
        'DO' => 'DOP',
        'EC' => 'USD',
        'EG' => 'EGP',
        'SV' => 'USD',
        'GQ' => 'XAF',
        'ER' => 'ERN',
        'EE' => 'EUR',
        'ET' => 'ETB',
        'FJ' => 'FJD',
        'FI' => 'EUR',
        'FR' => 'EUR',
        'GA' => 'XAF',
        'GM' => 'GMD',
        'GE' => 'GEL',
        'DE' => 'EUR',
        'GH' => 'GHS',
        'GR' => 'EUR',
        'GT' => 'GTQ',
        'GN' => 'GNF',
        'GW' => 'XOF',
        'GY' => 'GYD',
        'HT' => 'HTG',
        'HN' => 'HNL',
        'HU' => 'HUF',
        'IS' => 'ISK',
        'IN' => 'INR',
        'ID' => 'IDR',
        'IR' => 'IRR',
        'IQ' => 'IQD',
        'IE' => 'EUR',
        'IL' => 'ILS',
        'IT' => 'EUR',
        'JM' => 'JMD',
        'JP' => 'JPY',
        'JO' => 'JOD',
        'KZ' => 'KZT',
        'KE' => 'KES',
        'KR' => 'KRW',
        'KW' => 'KWD',
        'KG' => 'KGS',
        'LA' => 'LAK',
        'LV' => 'EUR',
        'LB' => 'LBP',
        'LS' => 'LSL',
        'LR' => 'LRD',
        'LY' => 'LYD',
        'LT' => 'EUR',
        'LU' => 'EUR',
        'MG' => 'MGA',
        'MW' => 'MWK',
        'MY' => 'MYR',
        'MV' => 'MVR',
        'ML' => 'XOF',
        'MT' => 'EUR',
        'MR' => 'MRU',
        'MU' => 'MUR',
        'MX' => 'MXN',
        'MD' => 'MDL',
        'MN' => 'MNT',
        'ME' => 'EUR',
        'MA' => 'MAD',
        'MZ' => 'MZN',
        'MM' => 'MMK',
        'NA' => 'NAD',
        'NP' => 'NPR',
        'NL' => 'EUR',
        'NZ' => 'NZD',
        'NI' => 'NIO',
        'NE' => 'XOF',
        'NG' => 'NGN',
        'MK' => 'MKD',
        'NO' => 'NOK',
        'OM' => 'OMR',
        'PK' => 'PKR',
        'PA' => 'PAB',
        'PG' => 'PGK',
        'PY' => 'PYG',
        'PE' => 'PEN',
        'PH' => 'PHP',
        'PL' => 'PLN',
        'PT' => 'EUR',
        'QA' => 'QAR',
        'RO' => 'RON',
        'RU' => 'RUB',
        'RW' => 'RWF',
        'SA' => 'SAR',
        'SN' => 'XOF',
        'RS' => 'RSD',
        'SC' => 'SCR',
        'SL' => 'SLE',
        'SG' => 'SGD',
        'SK' => 'EUR',
        'SI' => 'EUR',
        'SO' => 'SOS',
        'ZA' => 'ZAR',
        'ES' => 'EUR',
        'LK' => 'LKR',
        'SD' => 'SDG',
        'SE' => 'SEK',
        'CH' => 'CHF',
        'SY' => 'SYP',
        'TW' => 'TWD',
        'TJ' => 'TJS',
        'TZ' => 'TZS',
        'TH' => 'THB',
        'TG' => 'XOF',
        'TN' => 'TND',
        'TR' => 'TRY',
        'TM' => 'TMT',
        'UG' => 'UGX',
        'UA' => 'UAH',
        'AE' => 'AED',
        'GB' => 'GBP',
        'US' => 'USD',
        'UY' => 'UYU',
        'UZ' => 'UZS',
        'VE' => 'VES',
        'VN' => 'VND',
        'YE' => 'YER',
        'ZM' => 'ZMW',
        'ZW' => 'ZWL',
    ];

    $localCurrencyCode = function ($country) use ($currencyFallbackMap) {
        if (! $country) {
            return null;
        }

        $directCandidates = [
            $country->currency_code ?? null,
            $country->currency ?? null,
            $country->currencies ?? null,
            $country->currency_iso ?? null,
            $country->currency_iso_code ?? null,
        ];

        foreach ($directCandidates as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            if (is_array($candidate)) {
                $firstKey = array_key_first($candidate);

                if ($firstKey && strlen((string) $firstKey) === 3) {
                    return strtoupper((string) $firstKey);
                }

                $firstValue = reset($candidate);

                if (is_string($firstValue) && strlen($firstValue) === 3) {
                    return strtoupper($firstValue);
                }
            }

            if (is_string($candidate)) {
                $trimmed = trim($candidate);

                $decoded = json_decode($trimmed, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $firstKey = array_key_first($decoded);

                    if ($firstKey && strlen((string) $firstKey) === 3) {
                        return strtoupper((string) $firstKey);
                    }

                    foreach ($decoded as $key => $value) {
                        if (is_string($key) && strlen($key) === 3) {
                            return strtoupper($key);
                        }

                        if (is_string($value) && strlen($value) === 3) {
                            return strtoupper($value);
                        }
                    }
                }

                if (preg_match('/\b[A-Z]{3}\b/i', $trimmed, $match)) {
                    return strtoupper($match[0]);
                }
            }
        }

        $code = strtoupper((string) ($country->code ?? ''));

        if ($code && isset($currencyFallbackMap[$code])) {
            return $currencyFallbackMap[$code];
        }

        return null;
    };

    $latestCurrency = function ($country) use ($safeColumns, $localCurrencyCode) {
        if (! $country) {
            return null;
        }

        $columns = $safeColumns('currency_rates');

        if (empty($columns)) {
            return null;
        }

        $orderColumn = 'id';

        foreach (['recorded_at', 'rate_date', 'created_at', 'updated_at', 'id'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                $orderColumn = $candidate;
                break;
            }
        }

        $baseQuery = \Illuminate\Support\Facades\DB::table('currency_rates');

        if (in_array('base_currency', $columns, true)) {
            $baseQuery->where(function ($query) {
                $query
                    ->where('base_currency', 'USD')
                    ->orWhereNull('base_currency');
            });
        }

        if (in_array('country_id', $columns, true)) {
            $byCountry = (clone $baseQuery)
                ->where('country_id', $country->id)
                ->orderByDesc($orderColumn)
                ->first();

            if ($byCountry) {
                return $byCountry;
            }
        }

        $currencyCode = $localCurrencyCode($country);

        if (
            $currencyCode
            && in_array('target_currency', $columns, true)
        ) {
            $byTargetCurrency = (clone $baseQuery)
                ->where('target_currency', $currencyCode)
                ->orderByDesc($orderColumn)
                ->first();

            if ($byTargetCurrency) {
                return $byTargetCurrency;
            }
        }

        if (
            $currencyCode
            && in_array('currency', $columns, true)
        ) {
            $byCurrency = (clone $baseQuery)
                ->where('currency', $currencyCode)
                ->orderByDesc($orderColumn)
                ->first();

            if ($byCurrency) {
                return $byCurrency;
            }
        }

        if (method_exists($country, 'currencyRates')) {
            try {
                return $country
                    ->currencyRates()
                    ->latest($orderColumn)
                    ->first();
            } catch (\Throwable $exception) {
                return null;
            }
        }

        return null;
    };

    $economicA = $latestEconomic($countryA);
    $economicB = $latestEconomic($countryB);

    $weatherA = $latestWeather($countryA);
    $weatherB = $latestWeather($countryB);

    $riskA = $latestRisk($countryA);
    $riskB = $latestRisk($countryB);

    $currencyA = $latestCurrency($countryA);
    $currencyB = $latestCurrency($countryB);

    $currencyCodeA = $localCurrencyCode($countryA)
        ?? ($currencyA->target_currency ?? null)
        ?? ($currencyA->currency ?? null);

    $currencyCodeB = $localCurrencyCode($countryB)
        ?? ($currencyB->target_currency ?? null)
        ?? ($currencyB->currency ?? null);

    $formatNumber = function ($value, $decimal = 2) {
        if ($value === null || $value === '') {
            return '-';
        }

        return number_format((float) $value, $decimal, ',', '.');
    };

    $formatMoney = function ($value) use ($formatNumber) {
        if ($value === null || $value === '') {
            return '-';
        }

        $value = (float) $value;

        if (abs($value) >= 1000000000000) {
            return '$' . $formatNumber($value / 1000000000000, 2) . ' triliun USD';
        }

        if (abs($value) >= 1000000000) {
            return '$' . $formatNumber($value / 1000000000, 2) . ' miliar USD';
        }

        if (abs($value) >= 1000000) {
            return '$' . $formatNumber($value / 1000000, 2) . ' juta USD';
        }

        return '$' . $formatNumber($value, 2);
    };

    $riskLabel = function ($risk) use ($formatNumber) {
        if (! $risk) {
            return '-';
        }

        $score = $risk->total_score
            ?? $risk->score
            ?? null;

        $level = $risk->risk_level
            ?? $risk->risk_label
            ?? $risk->level
            ?? null;

        if ($score === null) {
            return $level ?: '-';
        }

        return $formatNumber($score, 1) . ($level ? ' — ' . $level : '');
    };

    $rateLabel = function ($currency, $country, $currencyCode = null) use ($formatNumber, $localCurrencyCode) {
        if (! $currency) {
            return '-';
        }

        $rate = $currency->rate
            ?? $currency->exchange_rate
            ?? $currency->value
            ?? null;

        if ($rate === null) {
            return '-';
        }

        $displayCurrency = $currency->target_currency
            ?? $currency->currency
            ?? $currencyCode
            ?? $localCurrencyCode($country)
            ?? '';

        return $formatNumber($rate, 4)
            . ($displayCurrency ? ' ' . strtoupper((string) $displayCurrency) : '');
    };

    $gdpA = $economicA->gdp ?? null;
    $gdpB = $economicB->gdp ?? null;

    $inflationA = $economicA->inflation ?? null;
    $inflationB = $economicB->inflation ?? null;

    $temperatureA = $weatherA->temperature ?? null;
    $temperatureB = $weatherB->temperature ?? null;

    $windA = $weatherA->wind_speed ?? null;
    $windB = $weatherB->wind_speed ?? null;

    $rateA = $currencyA->rate
        ?? $currencyA->exchange_rate
        ?? $currencyA->value
        ?? null;

    $rateB = $currencyB->rate
        ?? $currencyB->exchange_rate
        ?? $currencyB->value
        ?? null;

    $riskScoreA = $riskA->total_score ?? $riskA->score ?? null;
    $riskScoreB = $riskB->total_score ?? $riskB->score ?? null;

    $scalePair = function ($a, $b) {
        $a = $a === null ? 0 : (float) $a;
        $b = $b === null ? 0 : (float) $b;

        $max = max(abs($a), abs($b), 1);

        return [
            round(($a / $max) * 100, 2),
            round(($b / $max) * 100, 2),
        ];
    };

    [$chartGdpA, $chartGdpB] = $scalePair($gdpA, $gdpB);
    [$chartInflationA, $chartInflationB] = $scalePair($inflationA, $inflationB);
    [$chartTemperatureA, $chartTemperatureB] = $scalePair($temperatureA, $temperatureB);
    [$chartWindA, $chartWindB] = $scalePair($windA, $windB);
    [$chartRateA, $chartRateB] = $scalePair($rateA, $rateB);
    [$chartRiskA, $chartRiskB] = $scalePair($riskScoreA, $riskScoreB);

    $riskScoreValue = function ($risk) {
        if (! $risk) {
            return null;
        }

        return $risk->total_score
            ?? $risk->score
            ?? null;
    };

    $riskLevelText = function ($risk) {
        if (! $risk) {
            return 'Belum tersedia';
        }

        return $risk->risk_level
            ?? $risk->risk_label
            ?? $risk->level
            ?? 'Belum tersedia';
    };

    $riskBadgeClass = function ($risk) use ($riskScoreValue, $riskLevelText) {
        $level = strtolower((string) $riskLevelText($risk));
        $score = $riskScoreValue($risk);

        if (str_contains($level, 'kritis') || str_contains($level, 'critical') || ((float) $score >= 80 && $score !== null)) {
            return 'badge-critical';
        }

        if (str_contains($level, 'tinggi') || str_contains($level, 'high') || ((float) $score >= 60 && $score !== null)) {
            return 'badge-high';
        }

        if (str_contains($level, 'sedang') || str_contains($level, 'medium') || ((float) $score >= 30 && $score !== null)) {
            return 'badge-medium';
        }

        if (str_contains($level, 'rendah') || str_contains($level, 'low') || ($score !== null && (float) $score > 0)) {
            return 'badge-low';
        }

        return 'text-bg-secondary';
    };

    $formatPopulation = function ($value) use ($formatNumber) {
        if ($value === null || $value === '') {
            return '-';
        }

        $value = (float) $value;

        if ($value >= 1000000000) {
            return $formatNumber($value / 1000000000, 2) . ' miliar';
        }

        if ($value >= 1000000) {
            return $formatNumber($value / 1000000, 2) . ' juta';
        }

        if ($value >= 1000) {
            return $formatNumber($value / 1000, 2) . ' ribu';
        }

        return $formatNumber($value, 0);
    };

    $countrySummary = function ($country, $risk, $weather, $currencyCode) use ($flag, $riskLabel, $riskBadgeClass, $formatPopulation) {
        return [
            'flag' => $flag($country),
            'name' => $country?->name ?? '-',
            'code' => strtoupper((string) ($country?->code ?? $country?->cca3 ?? '-')),
            'region' => $country?->region
                ?? $country?->subregion
                ?? $country?->continent
                ?? 'Wilayah tidak tersedia',
            'population' => $formatPopulation($country?->population ?? null),
            'currency' => $currencyCode ? strtoupper((string) $currencyCode) : '-',
            'risk' => $riskLabel($risk),
            'risk_level' => $risk ? ($risk->risk_level ?? $risk->risk_label ?? $risk->level ?? 'Risiko') : 'Belum tersedia',
            'risk_class' => $riskBadgeClass($risk),
            'weather' => $weather?->condition ?? 'Belum tersedia',
        ];
    };

    $countryCards = [
        'A' => $countrySummary($countryA, $riskA, $weatherA, $currencyCodeA),
        'B' => $countrySummary($countryB, $riskB, $weatherB, $currencyCodeB),
    ];

    $comparisonInsightText = 'Perbandingan ini membantu memahami perbedaan kondisi ekonomi, cuaca, nilai tukar, dan risiko antara dua negara untuk mendukung evaluasi strategi rantai pasok.';

    if ($riskScoreA !== null && $riskScoreB !== null && $countryA && $countryB) {
        $difference = abs((float) $riskScoreA - (float) $riskScoreB);

        if ($difference < 5) {
            $comparisonInsightText = 'Kedua negara memiliki tingkat risiko yang relatif berdekatan. Evaluasi lanjutan dapat difokuskan pada indikator cuaca, inflasi, dan nilai tukar sebelum menentukan keputusan rantai pasok.';
        } elseif ((float) $riskScoreA > (float) $riskScoreB) {
            $comparisonInsightText = $countryA->name . ' memiliki skor risiko lebih tinggi dibandingkan ' . $countryB->name . '. Sistem menyarankan pemantauan lebih ketat pada negara pertama sebelum dijadikan prioritas pemasok atau rute distribusi.';
        } else {
            $comparisonInsightText = $countryB->name . ' memiliki skor risiko lebih tinggi dibandingkan ' . $countryA->name . '. Sistem menyarankan pemantauan lebih ketat pada negara kedua sebelum dijadikan prioritas pemasok atau rute distribusi.';
        }
    }

    $updatedAt = now()->format('d M Y, H:i') . ' WIB';
@endphp

@section('content')
    <div class="comparison-page">
        <div class="comparison-hero">
            <h1>Bandingkan Dua Negara</h1>
            <p>
                Bandingkan indikator ekonomi, cuaca, nilai tukar, dan risiko secara langsung
                antara dua negara.
            </p>
        </div>

        <form
            method="GET"
            action="{{ url('/perbandingan-negara') }}"
            class="comparison-selector"
        >
            <div class="comparison-field">
                <label for="country_a">Negara pertama</label>

                <select
                    id="country_a"
                    name="a"
                    class="comparison-select"
                >
                    @foreach ($countries as $country)
                        <option
                            value="{{ $country->id }}"
                            @selected((int) request('a', $countryA?->id) === (int) $country->id)
                        >
                            {{ $flag($country) }} {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="comparison-vs">
                VS
            </div>

            <div class="comparison-field">
                <label for="country_b">Negara kedua</label>

                <select
                    id="country_b"
                    name="b"
                    class="comparison-select"
                >
                    @foreach ($countries as $country)
                        <option
                            value="{{ $country->id }}"
                            @selected((int) request('b', $countryB?->id) === (int) $country->id)
                        >
                            {{ $flag($country) }} {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="comparison-action">
                <button
                    type="submit"
                    class="btn-compare-country"
                >
                    <span>▰</span>
                    Bandingkan Negara
                </button>
            </div>
        </form>

        <div class="comparison-summary-grid">
            @foreach ($countryCards as $position => $summary)
                <article class="country-summary-card">
                    <div class="country-summary-top">
                        <div class="country-summary-flag">
                            {{ $summary['flag'] }}
                        </div>

                        <div class="country-summary-title">
                            <span>
                                {{ $position === 'A' ? 'Negara pertama' : 'Negara kedua' }}
                            </span>

                            <strong>
                                {{ $summary['name'] }}
                            </strong>
                        </div>

                        <span class="badge {{ $summary['risk_class'] }}">
                            {{ $summary['risk_level'] }}
                        </span>
                    </div>

                    <div class="country-summary-details">
                        <div>
                            <small>Kode</small>
                            <strong>{{ $summary['code'] }}</strong>
                        </div>

                        <div>
                            <small>Wilayah</small>
                            <strong>{{ $summary['region'] }}</strong>
                        </div>

                        <div>
                            <small>Populasi</small>
                            <strong>{{ $summary['population'] }}</strong>
                        </div>

                        <div>
                            <small>Mata uang</small>
                            <strong>{{ $summary['currency'] }}</strong>
                        </div>
                    </div>

                    <div class="country-summary-bottom">
                        <span>
                            <i class="bi bi-cloud-sun"></i>
                            {{ $summary['weather'] }}
                        </span>

                        <span>
                            <i class="bi bi-shield-check"></i>
                            {{ $summary['risk'] }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="comparison-grid">
            <section class="comparison-card comparison-table-card">
                <div class="comparison-card-header">
                    <h2>Perbandingan Indikator</h2>
                </div>

                <div class="comparison-table-wrapper">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th>Indikator</th>
                                <th>
                                    Negara pertama
                                    <span>({{ $countryA?->name ?? '-' }})</span>
                                </th>
                                <th>
                                    Negara kedua
                                    <span>({{ $countryB?->name ?? '-' }})</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    <div class="metric-name">
                                        <span class="metric-icon">▥</span>
                                        <div>
                                            <strong>GDP</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $formatMoney($gdpA) }}</td>
                                <td>{{ $formatMoney($gdpB) }}</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="metric-name">
                                        <span class="metric-icon">%</span>
                                        <div>
                                            <strong>Inflasi</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $formatNumber($inflationA, 2) }}%</td>
                                <td>{{ $formatNumber($inflationB, 2) }}%</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="metric-name">
                                        <span class="metric-icon">♨</span>
                                        <div>
                                            <strong>Temperatur</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $formatNumber($temperatureA, 1) }}°C</td>
                                <td>{{ $formatNumber($temperatureB, 1) }}°C</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="metric-name">
                                        <span class="metric-icon">≈</span>
                                        <div>
                                            <strong>Kecepatan Angin</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $formatNumber($windA, 1) }} km/jam</td>
                                <td>{{ $formatNumber($windB, 1) }} km/jam</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="metric-name">
                                        <span class="metric-icon">$</span>
                                        <div>
                                            <strong>Nilai Tukar</strong>
                                            <small>1 USD ke mata uang lokal</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $rateLabel($currencyA, $countryA, $currencyCodeA) }}</td>
                                <td>{{ $rateLabel($currencyB, $countryB, $currencyCodeB) }}</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="metric-name">
                                        <span class="metric-icon">◇</span>
                                        <div>
                                            <strong>Skor Risiko</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $riskLabel($riskA) }}</td>
                                <td>{{ $riskLabel($riskB) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="comparison-updated">
                    Data diperbarui per {{ $updatedAt }}
                </p>
            </section>

            <section class="comparison-card comparison-chart-card">
                <div class="comparison-card-header">
                    <h2>Grafik Indeks Perbandingan</h2>
                </div>

                <div class="comparison-chart-area">
                    <canvas id="comparisonRadarChart"></canvas>
                </div>
            </section>
        </div>

        <section class="comparison-insight-card">
            <div class="insight-icon">
                i
            </div>

            <div class="insight-content">
                <h2>Insight Perbandingan</h2>
                <p>
                    {{ $comparisonInsightText }}
                </p>
            </div>

            <div class="insight-source">
                <span>Sumber data: World Bank, Open-Meteo, ExchangeRate, GNews, Risk Scoring</span>
                <span class="source-info">ⓘ</span>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .comparison-page {
            padding: 4px 0 28px;
        }

        .comparison-hero {
            margin-bottom: 20px;
        }

        .comparison-hero h1 {
            margin: 0;
            color: #f8fafc;
            font-size: clamp(28px, 3vw, 38px);
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .comparison-hero p {
            margin: 8px 0 0;
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
        }

        .comparison-selector {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 74px minmax(0, 1fr) 220px;
            gap: 20px;
            align-items: end;
            padding: 20px 24px;
            border: 1px solid rgba(59, 130, 246, 0.18);
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.72)),
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.14), transparent 34%);
            box-shadow: 0 20px 60px rgba(2, 8, 23, 0.22);
        }

        .comparison-field label {
            display: block;
            margin-bottom: 10px;
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 700;
        }

        .comparison-select {
            width: 100%;
            min-height: 56px;
            padding: 0 18px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 12px;
            outline: none;
            color: #f8fafc;
            background: rgba(2, 6, 23, 0.52);
            font-size: 15px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .comparison-select:focus {
            border-color: rgba(14, 165, 233, 0.72);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
        }

        .comparison-vs {
            width: 64px;
            height: 64px;
            display: grid;
            place-items: center;
            margin: 0 auto 0;
            border: 1px solid rgba(14, 165, 233, 0.58);
            border-radius: 999px;
            color: #f8fafc;
            background:
                radial-gradient(circle at 50% 20%, rgba(14, 165, 233, 0.26), transparent 58%),
                rgba(15, 23, 42, 0.8);
            font-size: 22px;
            font-weight: 900;
            box-shadow: 0 0 26px rgba(14, 165, 233, 0.18);
        }

        .comparison-action {
            display: flex;
            justify-content: flex-end;
        }

        .comparison-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 18px;
        }

        .country-summary-card {
            padding: 18px;
            border: 1px solid rgba(59, 130, 246, 0.16);
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.86), rgba(15, 23, 42, 0.66)),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 42%);
            box-shadow: 0 16px 44px rgba(2, 8, 23, 0.16);
        }

        .country-summary-top {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
        }

        .country-summary-flag {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border: 1px solid rgba(14, 165, 233, 0.2);
            border-radius: 15px;
            background: rgba(14, 165, 233, 0.08);
            font-size: 1.35rem;
        }

        .country-summary-title {
            min-width: 0;
        }

        .country-summary-title span {
            display: block;
            color: #64748b;
            font-size: 0.62rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .country-summary-title strong {
            display: block;
            overflow: hidden;
            margin-top: 3px;
            color: #f8fafc;
            font-size: 1rem;
            font-weight: 850;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .country-summary-details {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }

        .country-summary-details div {
            min-width: 0;
            padding: 11px 12px;
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            background: rgba(2, 6, 23, 0.28);
        }

        .country-summary-details small {
            display: block;
            color: #64748b;
            font-size: 0.58rem;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .country-summary-details strong {
            display: block;
            overflow: hidden;
            margin-top: 4px;
            color: #e2e8f0;
            font-size: 0.72rem;
            font-weight: 750;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .country-summary-bottom {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .country-summary-bottom span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.11);
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.46);
            font-size: 0.64rem;
            font-weight: 700;
        }

        .country-summary-bottom i {
            color: #38bdf8;
        }

        .comparison-page .badge-low {
            color: #bbf7d0;
            background: rgba(34, 197, 94, 0.16);
            border: 1px solid rgba(34, 197, 94, 0.25);
        }

        .comparison-page .badge-medium {
            color: #fde68a;
            background: rgba(234, 179, 8, 0.16);
            border: 1px solid rgba(234, 179, 8, 0.25);
        }

        .comparison-page .badge-high {
            color: #fed7aa;
            background: rgba(249, 115, 22, 0.16);
            border: 1px solid rgba(249, 115, 22, 0.25);
        }

        .comparison-page .badge-critical {
            color: #fecaca;
            background: rgba(239, 68, 68, 0.17);
            border: 1px solid rgba(239, 68, 68, 0.28);
        }

        .btn-compare-country {
            width: 100%;
            min-height: 56px;
            display: inline-flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(14, 165, 233, 0.78);
            border-radius: 12px;
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s ease;
            box-shadow: 0 16px 38px rgba(37, 99, 235, 0.22);
        }

        .btn-compare-country:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 46px rgba(37, 99, 235, 0.32);
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
            gap: 20px;
            margin-top: 18px;
        }

        .comparison-card,
        .comparison-insight-card {
            border: 1px solid rgba(59, 130, 246, 0.18);
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.7)),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 42%);
            box-shadow: 0 20px 60px rgba(2, 8, 23, 0.22);
        }

        .comparison-card {
            padding: 20px;
        }

        .comparison-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .comparison-card-header h2,
        .comparison-insight-card h2 {
            margin: 0;
            color: #f8fafc;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .comparison-table-wrapper {
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: 14px;
            background: rgba(2, 6, 23, 0.28);
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            color: #cbd5e1;
        }

        .comparison-table th {
            padding: 16px 18px;
            color: #cbd5e1;
            background: rgba(30, 41, 59, 0.42);
            font-size: 13px;
            font-weight: 800;
            text-transform: none;
            letter-spacing: 0.02em;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }

        .comparison-table th span {
            display: block;
            margin-top: 3px;
            color: #94a3b8;
            font-weight: 600;
        }

        .comparison-table td {
            padding: 15px 18px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 15px;
            font-weight: 650;
        }

        .comparison-table tr:last-child td {
            border-bottom: 0;
        }

        .comparison-table td:not(:first-child),
        .comparison-table th:not(:first-child) {
            border-left: 1px solid rgba(148, 163, 184, 0.1);
        }

        .metric-name {
            display: flex;
            gap: 12px;
            align-items: center;
            color: #f8fafc;
        }

        .metric-name small {
            display: block;
            margin-top: 2px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 500;
        }

        .metric-icon {
            width: 32px;
            height: 32px;
            display: inline-grid;
            place-items: center;
            border: 1px solid rgba(14, 165, 233, 0.24);
            border-radius: 10px;
            color: #38bdf8;
            background: rgba(14, 165, 233, 0.1);
            font-size: 15px;
            font-weight: 900;
        }

        .comparison-updated {
            margin: 14px 0 0;
            color: #94a3b8;
            font-size: 13px;
        }

        .comparison-chart-card {
            min-height: 430px;
        }

        .comparison-chart-area {
            position: relative;
            height: 360px;
            padding: 8px;
        }

        .comparison-insight-card {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-top: 18px;
            padding: 24px 26px;
        }

        .insight-icon {
            width: 56px;
            height: 56px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            color: #7dd3fc;
            background:
                radial-gradient(circle at 50% 30%, rgba(14, 165, 233, 0.42), transparent 60%),
                rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.22);
            font-size: 24px;
            font-weight: 900;
        }

        .insight-content p {
            margin: 8px 0 0;
            color: #cbd5e1;
            line-height: 1.75;
            font-size: 15px;
        }

        .insight-source {
            display: flex;
            gap: 10px;
            align-items: center;
            color: #94a3b8;
            font-size: 13px;
            white-space: nowrap;
        }

        .source-info {
            width: 22px;
            height: 22px;
            display: inline-grid;
            place-items: center;
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 999px;
            color: #cbd5e1;
            font-size: 12px;
            font-weight: 800;
        }

        @media (max-width: 1180px) {
            .comparison-selector {
                grid-template-columns: 1fr;
            }

            .comparison-vs {
                width: 52px;
                height: 52px;
            }

            .comparison-grid,
            .comparison-insight-card,
            .comparison-summary-grid {
                grid-template-columns: 1fr;
            }

            .comparison-action {
                justify-content: stretch;
            }

            .insight-source {
                white-space: normal;
            }
        }

        @media (max-width: 760px) {
            .comparison-selector,
            .comparison-card,
            .comparison-insight-card,
            .country-summary-card {
                padding: 16px;
            }

            .country-summary-top {
                grid-template-columns: 42px minmax(0, 1fr);
            }

            .country-summary-top .badge {
                grid-column: 1 / -1;
                width: fit-content;
            }

            .country-summary-details {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .comparison-table th,
            .comparison-table td {
                padding: 13px 12px;
                font-size: 13px;
            }

            .metric-icon {
                width: 28px;
                height: 28px;
            }

            .comparison-chart-area {
                height: 300px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartElement = document.getElementById('comparisonRadarChart');

            if (!chartElement || typeof Chart === 'undefined') {
                return;
            }

            const countryAName = @json($countryA?->name ?? 'Negara pertama');
            const countryBName = @json($countryB?->name ?? 'Negara kedua');

            const chartDataA = [
                @json($chartGdpA),
                @json($chartInflationA),
                @json($chartTemperatureA),
                @json($chartRateA),
                @json($chartWindA),
                @json($chartRiskA),
            ];

            const chartDataB = [
                @json($chartGdpB),
                @json($chartInflationB),
                @json($chartTemperatureB),
                @json($chartRateB),
                @json($chartWindB),
                @json($chartRiskB),
            ];

            new Chart(chartElement, {
                type: 'radar',
                data: {
                    labels: [
                        'GDP',
                        'Inflasi',
                        'Temperatur',
                        'Nilai Tukar',
                        'Kecepatan Angin',
                        'Skor Risiko'
                    ],
                    datasets: [
                        {
                            label: countryAName,
                            data: chartDataA,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 5,
                            borderColor: 'rgba(14, 165, 233, 0.95)',
                            backgroundColor: 'rgba(14, 165, 233, 0.18)',
                            pointBackgroundColor: 'rgba(14, 165, 233, 1)'
                        },
                        {
                            label: countryBName,
                            data: chartDataB,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 5,
                            borderColor: 'rgba(244, 63, 94, 0.95)',
                            backgroundColor: 'rgba(244, 63, 94, 0.16)',
                            pointBackgroundColor: 'rgba(244, 63, 94, 1)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#cbd5e1',
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 18,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.96)',
                            borderColor: 'rgba(59, 130, 246, 0.28)',
                            borderWidth: 1,
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            padding: 12
                        }
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 25,
                                color: '#94a3b8',
                                backdropColor: 'transparent',
                                font: {
                                    size: 11
                                }
                            },
                            angleLines: {
                                color: 'rgba(148, 163, 184, 0.15)'
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.14)'
                            },
                            pointLabels: {
                                color: '#cbd5e1',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush