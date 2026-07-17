@extends('layouts.app')

@section('title', $country->name)
@section('heading', 'Detail Negara')

@php
    $chartEconomics = $economics
        ->sortBy('year')
        ->values();

    $riskLevel = $risk
        ? ($risk->risk_level_label ?? $risk->risk_level)
        : null;

    $riskClass = match ($riskLevel) {
        'Rendah' => 'badge-low',
        'Sedang' => 'badge-medium',
        'Tinggi', 'Kritis' => 'badge-high',
        default => 'text-bg-secondary',
    };

    $transportLevel = $transportAnalysis['level'] ?? null;

    $transportClass = match ($transportLevel) {
        'Rendah' => 'badge-low',
        'Sedang' => 'badge-medium',
        'Tinggi', 'Kritis' => 'badge-high',
        default => 'text-bg-secondary',
    };

    $initialRiskPayload = null;

    if ($risk) {
        $initialRiskPayload = $risk->toArray();

        $initialRiskPayload['country'] = [
            'id' => $country->id,
            'name' => $country->name,
            'code' => $country->code,
            'cca3' => $country->cca3,
        ];

        $initialRiskPayload['components'] = $risk->components
            ? $risk->components->values()->toArray()
            : [];

        $initialRiskPayload['risk_level_label'] =
            $risk->risk_level_label;

        $initialRiskPayload['risk_badge_class'] =
            $risk->risk_badge_class;

        $initialRiskPayload['dominant_component_label'] =
            $risk->dominant_component_label;

        $initialRiskPayload['dominant_component_score'] =
            $risk->dominant_component_score;

        $initialRiskPayload['analysis_summary'] =
            $risk->analysis_summary;

        $initialRiskPayload['decision_recommendation'] =
            $risk->decision_recommendation;

        $initialRiskPayload['transport_analysis'] =
            $transportAnalysis;
    }

    $portMapData = $ports
        ->filter(
            fn ($port) =>
                $port->latitude !== null
                && $port->longitude !== null
        )
        ->map(
            fn ($port) => [
                'id' => $port->id,
                'name' => $port->name,
                'city' => $port->city,
                'country_name' => $port->country?->name
                    ?? $port->country_name,
                'unlocode' => $port->unlocode,
                'wpi_number' => $port->wpi_number,
                'latitude' => $port->latitude,
                'longitude' => $port->longitude,
                'status' => $port->status_label,
            ]
        )
        ->values();
@endphp

@section('content')
{{-- =====================================================
     HEADER NEGARA
===================================================== --}}
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <img
        src="{{ $country->flag_url }}"
        alt="Bendera {{ $country->name }}"
        style="
            width: 88px;
            height: 58px;
            object-fit: cover;
            border-radius: 10px;
        "
        loading="lazy"
        onerror="this.style.display='none'"
    >

    <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h2 class="page-title mb-0">
                {{ $country->name }}
            </h2>

            <span class="badge text-bg-secondary">
                {{ $country->code ?: '-' }}
                /
                {{ $country->cca3 ?: '-' }}
            </span>
        </div>

        <p class="text-muted mb-0 mt-1">
            {{ $country->official_name ?: 'Nama resmi tidak tersedia' }}
        </p>
    </div>

    <div class="ms-auto d-flex flex-wrap gap-2">
        <button
            id="watchCountry"
            type="button"
            class="btn btn-outline-warning"
        >
            <i class="bi bi-star me-1"></i>
            Pantau Negara
        </button>

        <button
            id="calculateCountryRisk"
            type="button"
            class="btn btn-primary"
        >
            <i class="bi bi-calculator me-1"></i>
            Hitung Risiko
        </button>
    </div>
</div>

{{-- =====================================================
     KARTU RINGKASAN
===================================================== --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Populasi
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $country->population ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-people fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Pelabuhan
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $summary['ports_count'] ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-buildings fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Berita Tersimpan
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $summary['news_count'] ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-newspaper fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Riwayat Risiko
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $summary['risk_history_count'] ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-clock-history fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     INFORMASI UTAMA
===================================================== --}}
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Informasi Umum
                </h5>

                <p class="small text-muted mb-0">
                    Identitas dan informasi dasar negara.
                </p>
            </div>

            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 py-2">Ibu Kota</dt>
                    <dd class="col-7 py-2">{{ $country->capital ?: '-' }}</dd>

                    <dt class="col-5 py-2">Wilayah</dt>
                    <dd class="col-7 py-2">{{ $country->region ?: '-' }}</dd>

                    <dt class="col-5 py-2">Subwilayah</dt>
                    <dd class="col-7 py-2">{{ $country->subregion ?: '-' }}</dd>

                    <dt class="col-5 py-2">Bahasa</dt>
                    <dd class="col-7 py-2">{{ $country->language ?: '-' }}</dd>

                    <dt class="col-5 py-2">Mata Uang</dt>
                    <dd class="col-7 py-2">
                        {{ $country->currency_code ?: '-' }}

                        @if($country->currency_name)
                            — {{ $country->currency_name }}
                        @endif
                    </dd>

                    <dt class="col-5 py-2">Koordinat</dt>
                    <dd class="col-7 py-2">
                        @if($country->hasCoordinates())
                            {{ number_format($country->latitude, 4) }},
                            {{ number_format($country->longitude, 4) }}
                        @else
                            -
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Ekonomi Terbaru
                </h5>

                <p class="small text-muted mb-0">
                    Tahun {{ $economic?->year ?: '-' }}
                </p>
            </div>

            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 py-2">GDP</dt>
                    <dd class="col-7 py-2">
                        {{
                            $economic?->gdp
                                ? '$' . number_format(
                                    $economic->gdp / 1e9,
                                    2,
                                    ',',
                                    '.'
                                ) . ' Miliar'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-5 py-2">Inflasi</dt>
                    <dd class="col-7 py-2">
                        {{
                            $economic?->inflation !== null
                                ? number_format(
                                    $economic->inflation,
                                    2,
                                    ',',
                                    '.'
                                ) . '%'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-5 py-2">Ekspor</dt>
                    <dd class="col-7 py-2">
                        {{
                            $economic?->exports
                                ? '$' . number_format(
                                    $economic->exports / 1e9,
                                    2,
                                    ',',
                                    '.'
                                ) . ' Miliar'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-5 py-2">Impor</dt>
                    <dd class="col-7 py-2">
                        {{
                            $economic?->imports
                                ? '$' . number_format(
                                    $economic->imports / 1e9,
                                    2,
                                    ',',
                                    '.'
                                ) . ' Miliar'
                                : '-'
                        }}
                    </dd>
                </dl>

                @if(!$economic)
                    <div class="alert alert-warning small mb-0 mt-3">
                        Data ekonomi negara ini belum tersedia.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Cuaca Saat Ini
                </h5>

                <p class="small text-muted mb-0">
                    {{ $weather?->condition_label ?? $weather?->condition ?? 'Belum tersedia' }}
                </p>
            </div>

            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6 py-2">Temperatur</dt>
                    <dd class="col-6 py-2">
                        {{
                            $weather?->temperature !== null
                                ? number_format(
                                    $weather->temperature,
                                    1,
                                    ',',
                                    '.'
                                ) . '°C'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-6 py-2">Terasa</dt>
                    <dd class="col-6 py-2">
                        {{
                            $weather?->apparent_temperature !== null
                                ? number_format(
                                    $weather->apparent_temperature,
                                    1,
                                    ',',
                                    '.'
                                ) . '°C'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-6 py-2">Kelembapan</dt>
                    <dd class="col-6 py-2">
                        {{
                            $weather?->humidity !== null
                                ? number_format(
                                    $weather->humidity,
                                    0,
                                    ',',
                                    '.'
                                ) . '%'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-6 py-2">Curah Hujan</dt>
                    <dd class="col-6 py-2">
                        {{
                            $weather?->precipitation !== null
                                ? number_format(
                                    $weather->precipitation,
                                    1,
                                    ',',
                                    '.'
                                ) . ' mm'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-6 py-2">Angin</dt>
                    <dd class="col-6 py-2">
                        {{
                            $weather?->wind_speed !== null
                                ? number_format(
                                    $weather->wind_speed,
                                    1,
                                    ',',
                                    '.'
                                ) . ' km/jam'
                                : '-'
                        }}
                    </dd>

                    <dt class="col-6 py-2">Risiko Badai</dt>
                    <dd class="col-6 py-2">
                        {{
                            $weather?->storm_risk !== null
                                ? number_format(
                                    $weather->storm_risk,
                                    1,
                                    ',',
                                    '.'
                                ) . '/100'
                                : '-'
                        }}
                    </dd>
                </dl>

                @if($weather)
                    <div class="border rounded p-3 mt-3">
                        <div class="small text-muted">
                            Dampak Cuaca terhadap Transportasi
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <strong>
                                {{
                                    number_format(
                                        $weather->weather_disruption_score,
                                        1,
                                        ',',
                                        '.'
                                    )
                                }}/100
                            </strong>

                            <span class="badge {{
                                match ($weather->weather_disruption_level) {
                                    'Rendah' => 'badge-low',
                                    'Sedang' => 'badge-medium',
                                    'Tinggi', 'Kritis' => 'badge-high',
                                    default => 'text-bg-secondary',
                                }
                            }}">
                                {{ $weather->weather_disruption_level }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     GRAFIK EKONOMI DAN RISIKO TERBARU
===================================================== --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Tren GDP dan Inflasi 10 Tahun
                </h5>

                <p class="small text-muted mb-0">
                    Perubahan indikator ekonomi untuk mendukung keputusan impor.
                </p>
            </div>

            <div class="card-body">
                @if($chartEconomics->isNotEmpty())
                    <div style="min-height: 330px;">
                        <canvas id="economicChart"></canvas>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        Riwayat data ekonomi belum tersedia.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Risiko Terakhir
                </h5>

                <p class="small text-muted mb-0">
                    Weighted Risk Model SupplyGuard.
                </p>
            </div>

            <div class="card-body text-center">
                <div
                    id="countryRiskScore"
                    class="display-3 fw-bold text-primary"
                >
                    {{
                        $risk
                            ? number_format(
                                $risk->total_score,
                                1,
                                ',',
                                '.'
                            )
                            : '-'
                    }}
                </div>

                <div class="small text-muted mb-3">
                    dari 100
                </div>

                <span
                    id="countryRiskLevel"
                    class="badge fs-6 {{ $riskClass }}"
                >
                    {{
                        $risk
                            ? 'Risiko ' . $riskLevel
                            : 'Belum dihitung'
                    }}
                </span>

                <div
                    id="riskComponents"
                    class="text-start mt-4 small"
                >
                    @foreach($risk?->components ?? [] as $component)
                        <div class="d-flex justify-content-between border-bottom py-2 gap-3">
                            <span>
                                {{ $component->component }}
                                ({{ number_format($component->weight, 0) }}%)
                            </span>

                            <strong>
                                {{ number_format($component->weighted_score, 1) }}
                            </strong>
                        </div>
                    @endforeach
                </div>

                <div
                    id="riskCalculatedAt"
                    class="small text-muted mt-3"
                >
                    {{
                        $risk?->calculated_at
                            ? 'Dihitung ' . $risk->calculated_at->format('d/m/Y H:i')
                            : ''
                    }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     REKOMENDASI KEPUTUSAN
===================================================== --}}
<div
    id="decisionSection"
    class="row g-4 mb-4 {{ $risk ? '' : 'd-none' }}"
>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Ringkasan Analisis Risiko
                </h5>

                <p class="small text-muted mb-0">
                    Interpretasi hasil perhitungan untuk keputusan bisnis.
                </p>
            </div>

            <div class="card-body">
                <p
                    id="countryAnalysisSummary"
                    class="mb-4"
                >
                    {{ $risk?->analysis_summary }}
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">
                                Faktor Dominan
                            </div>

                            <strong id="countryDominantComponent">
                                {{ $risk?->dominant_component_label ?? '-' }}
                            </strong>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">
                                Skor Faktor
                            </div>

                            <strong id="countryDominantScore">
                                {{
                                    $risk
                                        ? number_format(
                                            $risk->dominant_component_score,
                                            1,
                                            ',',
                                            '.'
                                        ) . '/100'
                                        : '-'
                                }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Rekomendasi Keputusan
                </h5>

                <p class="small text-muted mb-0">
                    Tindakan yang disarankan berdasarkan skor risiko.
                </p>
            </div>

            <div class="card-body">
                <div class="d-flex gap-3">
                    <i class="bi bi-lightbulb fs-3 text-warning"></i>

                    <p
                        id="countryDecisionRecommendation"
                        class="mb-0"
                    >
                        {{ $risk?->decision_recommendation }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     GANGGUAN TRANSPORTASI
===================================================== --}}
<div
    id="transportSection"
    class="card mb-4 {{ $transportAnalysis ? '' : 'd-none' }}"
>
    <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h5 class="mb-1">
                Potensi Gangguan Transportasi
            </h5>

            <p class="small text-muted mb-0">
                Analisis berdasarkan cuaca, berita logistik dan geopolitik,
                serta ketersediaan pelabuhan.
            </p>
        </div>

        <div class="text-lg-end">
            <div class="small text-muted">
                Tingkat kepercayaan data
            </div>

            <strong id="transportConfidence">
                {{ $transportAnalysis['confidence'] ?? '-' }}
            </strong>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="text-center border rounded p-4 h-100">
                    <div class="small text-muted mb-2">
                        Skor Gangguan
                    </div>

                    <div
                        id="transportScore"
                        class="display-5 fw-bold text-primary"
                    >
                        {{
                            isset($transportAnalysis['score'])
                                ? number_format(
                                    $transportAnalysis['score'],
                                    1,
                                    ',',
                                    '.'
                                )
                                : '-'
                        }}
                    </div>

                    <div class="small text-muted mb-3">
                        dari 100
                    </div>

                    <span
                        id="transportLevel"
                        class="badge fs-6 {{ $transportClass }}"
                    >
                        {{ $transportLevel ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="col-lg-9">
                <div
                    id="transportComponents"
                    class="row g-3 mb-4"
                >
                    @foreach(($transportAnalysis['components'] ?? []) as $component)
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted mb-1">
                                    {{ $component['label'] ?? '-' }}
                                </div>

                                <div class="fs-4 fw-bold text-primary">
                                    {{
                                        number_format(
                                            $component['score'] ?? 0,
                                            1,
                                            ',',
                                            '.'
                                        )
                                    }}
                                </div>

                                <div class="small text-muted mt-2">
                                    Bobot
                                    {{
                                        number_format(
                                            $component['weight'] ?? 0,
                                            0
                                        )
                                    }}%
                                    ·
                                    Kontribusi
                                    {{
                                        number_format(
                                            $component['weighted_score'] ?? 0,
                                            2,
                                            ',',
                                            '.'
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <h6>
                            Faktor yang Ditemukan
                        </h6>

                        <ul
                            id="transportFactors"
                            class="mb-0 ps-3"
                        >
                            @forelse(($transportAnalysis['factors'] ?? []) as $factor)
                                <li class="mb-1">
                                    {{ $factor }}
                                </li>
                            @empty
                                <li>
                                    Tidak ada faktor gangguan dominan.
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="col-md-6">
                        <h6>
                            Rekomendasi Operasional
                        </h6>

                        <p
                            id="transportRecommendation"
                            class="mb-0"
                        >
                            {{ $transportAnalysis['recommendation'] ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer small text-muted">
        Indikator ini bukan data kemacetan pelabuhan secara real-time.
        Nilai dihitung dari data yang tersedia di SupplyGuard.
    </div>
</div>

{{-- =====================================================
     PETA DAN PELABUHAN
===================================================== --}}
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Peta Pelabuhan Negara
                </h5>

                <p class="small text-muted mb-0">
                    Sebaran pelabuhan yang tersedia untuk negara ini.
                </p>
            </div>

            <div class="card-body p-2">
                @if($country->hasCoordinates() || $portMapData->isNotEmpty())
                    <div
                        id="countryPortsMap"
                        class="map"
                        style="min-height: 360px;"
                    ></div>
                @else
                    <div class="text-center text-muted py-5">
                        Koordinat peta belum tersedia.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">
                        Pelabuhan Tersedia
                    </h5>

                    <p class="small text-muted mb-0">
                        Maksimal 10 pelabuhan ditampilkan.
                    </p>
                </div>

                <a
                    href="{{ route('ports.index', ['country' => $country->name]) }}"
                    class="btn btn-sm btn-outline-primary"
                >
                    Lihat Semua
                </a>
            </div>

            <div class="list-group list-group-flush">
                @forelse($ports as $port)
                    <div class="list-group-item bg-transparent">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <strong>
                                    {{ $port->name }}
                                </strong>

                                <div class="small text-muted">
                                    {{ $port->location_label }}
                                </div>
                            </div>

                            <span class="badge text-bg-secondary">
                                {{ $port->code_label }}
                            </span>
                        </div>

                        <div class="small mt-2">
                            {{ $port->type_label }}
                            ·
                            {{ $port->status_label }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        Data pelabuhan belum tersedia.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     BERITA TERBARU
===================================================== --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-1">
            Berita Terbaru
        </h5>

        <p class="small text-muted mb-0">
            Berita ekonomi, logistik, geopolitik, dan rantai pasok.
        </p>
    </div>

    <div class="card-body">
        <div class="row g-4">
            @forelse($recentNews as $news)
                <div class="col-md-6 col-xl-4">
                    <article class="card h-100">
                        @if($news->display_image)
                            <img
                                src="{{ $news->display_image }}"
                                class="card-img-top"
                                alt="{{ $news->title }}"
                                style="
                                    height: 180px;
                                    object-fit: cover;
                                "
                                loading="lazy"
                                onerror="this.style.display='none'"
                            >
                        @endif

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <span class="badge {{ $news->sentiment_badge_class }}">
                                    {{ $news->sentiment_label }}
                                </span>

                                <span class="small text-muted">
                                    {{ $news->source ?: 'Sumber tidak tersedia' }}
                                </span>
                            </div>

                            <h6>
                                {{ $news->title }}
                            </h6>

                            <p class="small text-muted">
                                {{ $news->short_description }}
                            </p>

                            <div class="mt-auto">
                                <div class="small text-muted mb-2">
                                    {{
                                        $news->published_at
                                            ? $news->published_at->format('d M Y, H:i')
                                            : 'Waktu tidak tersedia'
                                    }}
                                </div>

                                @if($news->hasValidUrl())
                                    <a
                                        href="{{ $news->url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Baca Berita
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-5">
                        Berita negara ini belum tersedia.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const initialRisk =
        @json($initialRiskPayload);

    const initialTransport =
        @json($transportAnalysis);

    const portMapData =
        @json($portMapData);

    const countryCoordinate = {
        latitude:
            @json($country->latitude),

        longitude:
            @json($country->longitude),
    };

    const countryName =
        @json($country->name);

    const riskWeights = {
        weather: 30,
        inflation: 20,
        currency: 10,
        news: 40,
    };

    const componentLabels = {
        weather: 'Cuaca',
        inflation: 'Inflasi',
        currency: 'Mata Uang',
        news: 'Berita',
        ports: 'Ketersediaan Pelabuhan',
    };

    function number(value, decimals = 1) {
        const numeric = Number(value ?? 0);

        if (!Number.isFinite(numeric)) {
            return '-';
        }

        return numeric.toLocaleString(
            'id-ID',
            {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }
        );
    }

    function normalizeRiskLevel(level, score = 0) {
        const normalized =
            String(level || '')
                .trim()
                .toLowerCase();

        if (
            [
                'rendah',
                'low',
                'low risk',
            ].includes(normalized)
        ) {
            return 'Rendah';
        }

        if (
            [
                'sedang',
                'medium',
                'moderate',
                'medium risk',
                'moderate risk',
            ].includes(normalized)
        ) {
            return 'Sedang';
        }

        if (
            [
                'tinggi',
                'high',
                'high risk',
            ].includes(normalized)
        ) {
            return 'Tinggi';
        }

        if (
            [
                'kritis',
                'critical',
                'critical risk',
            ].includes(normalized)
        ) {
            return 'Kritis';
        }

        const numericScore =
            Number(score || 0);

        if (numericScore >= 76) {
            return 'Kritis';
        }

        if (numericScore >= 51) {
            return 'Tinggi';
        }

        if (numericScore >= 26) {
            return 'Sedang';
        }

        return 'Rendah';
    }

    function riskClass(level) {
        const normalized =
            normalizeRiskLevel(level);

        if (
            [
                'Tinggi',
                'Kritis',
            ].includes(normalized)
        ) {
            return 'badge-high';
        }

        if (normalized === 'Sedang') {
            return 'badge-medium';
        }

        return 'badge-low';
    }

    function componentKey(component) {
        const raw =
            String(
                component?.key
                ?? component?.component
                ?? component?.name
                ?? ''
            )
                .trim()
                .toLowerCase();

        if (raw.includes('weather') || raw.includes('cuaca')) {
            return 'weather';
        }

        if (raw.includes('inflation') || raw.includes('inflasi')) {
            return 'inflation';
        }

        if (
            raw.includes('currency')
            || raw.includes('mata uang')
            || raw.includes('kurs')
        ) {
            return 'currency';
        }

        if (raw.includes('news') || raw.includes('berita')) {
            return 'news';
        }

        if (raw.includes('port') || raw.includes('pelabuhan')) {
            return 'ports';
        }

        return raw || 'component';
    }

    function buildRiskComponents(score) {
        const relationComponents =
            Array.isArray(score?.components)
                ? score.components
                : [];

        if (relationComponents.length) {
            return relationComponents.map(
                function (component) {
                    const key =
                        componentKey(component);

                    const normalizedScore =
                        Number(
                            component.normalized_score
                            ?? component.score
                            ?? component.value
                            ?? score?.[`${key}_score`]
                            ?? 0
                        );

                    const weight =
                        Number(
                            component.weight
                            ?? riskWeights[key]
                            ?? 0
                        );

                    const weightedScore =
                        Number(
                            component.weighted_score
                            ?? (
                                normalizedScore
                                * weight
                                / 100
                            )
                        );

                    return {
                        key,
                        label:
                            component.component
                            ?? component.label
                            ?? componentLabels[key]
                            ?? key,
                        normalizedScore,
                        weight,
                        weightedScore,
                    };
                }
            );
        }

        return [
            {
                key: 'weather',
                label: 'Cuaca',
                normalizedScore:
                    Number(score?.weather_score ?? 0),
                weight: 30,
                weightedScore:
                    Number(score?.weather_score ?? 0)
                    * 0.30,
            },
            {
                key: 'inflation',
                label: 'Inflasi',
                normalizedScore:
                    Number(score?.inflation_score ?? 0),
                weight: 20,
                weightedScore:
                    Number(score?.inflation_score ?? 0)
                    * 0.20,
            },
            {
                key: 'currency',
                label: 'Mata Uang',
                normalizedScore:
                    Number(score?.currency_score ?? 0),
                weight: 10,
                weightedScore:
                    Number(score?.currency_score ?? 0)
                    * 0.10,
            },
            {
                key: 'news',
                label: 'Berita',
                normalizedScore:
                    Number(score?.news_score ?? 0),
                weight: 40,
                weightedScore:
                    Number(score?.news_score ?? 0)
                    * 0.40,
            },
        ];
    }

    function fallbackRecommendation(level, dominantFactor) {
        if (level === 'Kritis') {
            return `Tunda keputusan impor yang tidak mendesak dan prioritaskan mitigasi pada faktor ${dominantFactor}.`;
        }

        if (level === 'Tinggi') {
            return `Siapkan pemasok, rute, atau jadwal alternatif dan pantau faktor ${dominantFactor} secara intensif.`;
        }

        if (level === 'Sedang') {
            return `Proses impor masih dapat dipertimbangkan dengan pengawasan berkala pada faktor ${dominantFactor}.`;
        }

        return `Risiko relatif rendah. Pengiriman dapat dipertimbangkan, tetapi perubahan faktor ${dominantFactor} tetap perlu dipantau.`;
    }

    function renderRisk(score) {
        if (!score) {
            return;
        }

        const level =
            normalizeRiskLevel(
                score.risk_level_label
                ?? score.risk_level,
                score.total_score
            );

        const components =
            buildRiskComponents(score);

        const sortedComponents =
            [...components].sort(
                (first, second) =>
                    second.normalizedScore
                    - first.normalizedScore
            );

        const dominant =
            score.dominant_component_label
            || sortedComponents[0]?.label
            || '-';

        const dominantScore =
            Number(
                score.dominant_component_score
                ?? sortedComponents[0]?.normalizedScore
                ?? 0
            );

        document.getElementById(
            'countryRiskScore'
        ).textContent =
            number(score.total_score, 1);

        const badge =
            document.getElementById(
                'countryRiskLevel'
            );

        badge.className =
            `badge fs-6 ${riskClass(level)}`;

        badge.textContent =
            `Risiko ${level}`;

        document.getElementById(
            'riskComponents'
        ).innerHTML =
            components.map(function (component) {
                return `
                    <div class="d-flex justify-content-between border-bottom py-2 gap-3">
                        <span>
                            ${sg.escape(component.label)}
                            (${number(component.weight, 0)}%)
                        </span>

                        <strong>
                            ${number(component.weightedScore, 1)}
                        </strong>
                    </div>
                `;
            }).join('');

        document.getElementById(
            'riskCalculatedAt'
        ).textContent =
            score.calculated_at
                ? `Dihitung ${new Date(score.calculated_at).toLocaleString('id-ID')}`
                : '';

        document.getElementById(
            'decisionSection'
        ).classList.remove('d-none');

        document.getElementById(
            'countryAnalysisSummary'
        ).textContent =
            score.analysis_summary
            || `Skor risiko ${countryName} adalah ${number(score.total_score, 1)}/100 dengan tingkat ${level}. Faktor dominan adalah ${dominant} (${number(dominantScore, 1)}/100).`;

        document.getElementById(
            'countryDominantComponent'
        ).textContent =
            dominant;

        document.getElementById(
            'countryDominantScore'
        ).textContent =
            `${number(dominantScore, 1)}/100`;

        document.getElementById(
            'countryDecisionRecommendation'
        ).textContent =
            score.decision_recommendation
            || fallbackRecommendation(
                level,
                dominant
            );

        renderTransport(
            score.transport_analysis
        );
    }

    function renderTransport(analysis) {
        if (!analysis) {
            return;
        }

        const section =
            document.getElementById(
                'transportSection'
            );

        section.classList.remove('d-none');

        const level =
            normalizeRiskLevel(
                analysis.level,
                analysis.score
            );

        document.getElementById(
            'transportScore'
        ).textContent =
            number(analysis.score, 1);

        const badge =
            document.getElementById(
                'transportLevel'
            );

        badge.className =
            `badge fs-6 ${riskClass(level)}`;

        badge.textContent =
            level;

        document.getElementById(
            'transportConfidence'
        ).textContent =
            analysis.confidence || '-';

        document.getElementById(
            'transportRecommendation'
        ).textContent =
            analysis.recommendation || '-';

        const factors =
            Array.isArray(analysis.factors)
                ? analysis.factors
                : [];

        document.getElementById(
            'transportFactors'
        ).innerHTML =
            factors.length
                ? factors.map(
                    factor => `
                        <li class="mb-1">
                            ${sg.escape(factor)}
                        </li>
                    `
                ).join('')
                : `
                    <li>
                        Tidak ada faktor gangguan dominan.
                    </li>
                `;

        const components =
            Object.values(
                analysis.components || {}
            );

        document.getElementById(
            'transportComponents'
        ).innerHTML =
            components.map(function (component) {
                return `
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">
                                ${sg.escape(component.label || '-')}
                            </div>

                            <div class="fs-4 fw-bold text-primary">
                                ${number(component.score, 1)}
                            </div>

                            <div class="small text-muted mt-2">
                                Bobot ${number(component.weight, 0)}%
                                ·
                                Kontribusi ${number(component.weighted_score, 2)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
    }

    function initializeEconomicChart() {
        const element =
            document.getElementById(
                'economicChart'
            );

        if (!element || typeof Chart === 'undefined') {
            return;
        }

        new Chart(
            element,
            {
                type: 'line',

                data: {
                    labels:
                        @json($chartEconomics->pluck('year')),

                    datasets: [
                        {
                            label: 'GDP (Miliar USD)',
                            data:
                                @json(
                                    $chartEconomics->map(
                                        fn ($item) =>
                                            $item->gdp
                                                ? round(
                                                    $item->gdp / 1e9,
                                                    2
                                                )
                                                : null
                                    )->values()
                                ),
                            yAxisID: 'y',
                            tension: 0.25,
                            spanGaps: true,
                        },
                        {
                            label: 'Inflasi (%)',
                            data:
                                @json(
                                    $chartEconomics
                                        ->pluck('inflation')
                                        ->values()
                                ),
                            yAxisID: 'y1',
                            tension: 0.25,
                            spanGaps: true,
                        },
                    ],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },

                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    },

                    scales: {
                        y: {
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Miliar USD',
                            },
                        },

                        y1: {
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Inflasi (%)',
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                },
            }
        );
    }

    function initializePortMap() {
        const mapElement =
            document.getElementById(
                'countryPortsMap'
            );

        if (
            !mapElement
            || typeof L === 'undefined'
        ) {
            return;
        }

        const latitude =
            Number(countryCoordinate.latitude);

        const longitude =
            Number(countryCoordinate.longitude);

        const hasCountryCoordinate =
            Number.isFinite(latitude)
            && Number.isFinite(longitude);

        const center =
            hasCountryCoordinate
                ? [
                    latitude,
                    longitude,
                ]
                : (
                    portMapData.length
                        ? [
                            Number(portMapData[0].latitude),
                            Number(portMapData[0].longitude),
                        ]
                        : [
                            20,
                            0,
                        ]
                );

        const map =
            L.map(
                mapElement,
                {
                    worldCopyJump: true,
                }
            ).setView(
                center,
                hasCountryCoordinate
                    ? 4
                    : 3
            );

        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution:
                    '&copy; OpenStreetMap contributors',
                maxZoom: 18,
            }
        ).addTo(map);

        const bounds = [];

        if (hasCountryCoordinate) {
            L.circleMarker(
                [
                    latitude,
                    longitude,
                ],
                {
                    radius: 8,
                }
            )
                .bindPopup(
                    `<strong>${sg.escape(countryName)}</strong><br>Pusat koordinat negara`
                )
                .addTo(map);

            bounds.push([
                latitude,
                longitude,
            ]);
        }

        portMapData.forEach(function (port) {
            const portLatitude =
                Number(port.latitude);

            const portLongitude =
                Number(port.longitude);

            if (
                !Number.isFinite(portLatitude)
                || !Number.isFinite(portLongitude)
            ) {
                return;
            }

            L.marker([
                portLatitude,
                portLongitude,
            ])
                .bindPopup(`
                    <strong>${sg.escape(port.name || '-')}</strong>
                    <br>
                    ${sg.escape(port.city || '-')}
                    <br>
                    UN/LOCODE: ${sg.escape(port.unlocode || '-')}
                    <br>
                    WPI: ${sg.escape(port.wpi_number || '-')}
                    <br>
                    Status: ${sg.escape(port.status || '-')}
                `)
                .addTo(map);

            bounds.push([
                portLatitude,
                portLongitude,
            ]);
        });

        if (bounds.length > 1) {
            map.fitBounds(
                bounds,
                {
                    padding: [
                        30,
                        30,
                    ],
                    maxZoom: 7,
                }
            );
        }

        window.addEventListener(
            'resize',
            function () {
                map.invalidateSize();
            }
        );
    }

    initializeEconomicChart();
    initializePortMap();

    if (initialRisk) {
        renderRisk(initialRisk);
    } else if (initialTransport) {
        renderTransport(initialTransport);
    }

    document.getElementById(
        'watchCountry'
    ).addEventListener(
        'click',
        async function () {
            const button = this;

            sg.loading(true);

            try {
                const payload =
                    await sg.fetchJson(
                        '{{ route('watchlists.store', $country) }}',
                        {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                            },
                        }
                    );

                button.innerHTML =
                    '<i class="bi bi-star-fill me-1"></i> Sedang Dipantau';

                button.classList.remove(
                    'btn-outline-warning'
                );

                button.classList.add(
                    'btn-warning'
                );

                sg.toast(
                    payload.message
                    || 'Negara berhasil ditambahkan ke watchlist.'
                );
            } catch (error) {
                console.error(error);

                sg.toast(
                    error.message
                    || 'Negara gagal ditambahkan ke watchlist.',
                    false
                );
            } finally {
                sg.loading(false);
            }
        }
    );

    document.getElementById(
        'calculateCountryRisk'
    ).addEventListener(
        'click',
        async function () {
            sg.loading(true);

            try {
                const payload =
                    await sg.fetchJson(
                        '{{ route('risk.calculate', $country) }}',
                        {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                            },
                        }
                    );

                if (!payload?.data) {
                    throw new Error(
                        'Hasil perhitungan tidak tersedia.'
                    );
                }

                renderRisk(payload.data);

                sg.toast(
                    payload.message
                    || 'Skor risiko berhasil dihitung.'
                );
            } catch (error) {
                console.error(error);

                sg.toast(
                    error.message
                    || 'Perhitungan risiko gagal dilakukan.',
                    false
                );
            } finally {
                sg.loading(false);
            }
        }
    );
});
</script>
@endpush