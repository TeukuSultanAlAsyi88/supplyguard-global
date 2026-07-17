@extends('layouts.app')

@section('title', 'Analisis Risiko')
@section('heading', 'Analisis Risiko')

@php
    $initialScorePayload = null;

    if ($score) {
        $initialScorePayload = $score->toArray();

        $initialScorePayload['country'] = [
            'id' => $selected?->id,
            'name' => $selected?->name,
            'code' => $selected?->code,
            'cca3' => $selected?->cca3,
        ];

        $initialScorePayload['components'] = $score->components
            ? $score->components->values()->toArray()
            : [];

        $initialScorePayload['risk_level_label'] =
            $score->risk_level_label;

        $initialScorePayload['risk_badge_class'] =
            $score->risk_badge_class;

        $initialScorePayload['dominant_component'] =
            $score->dominant_component;

        $initialScorePayload['dominant_component_label'] =
            $score->dominant_component_label;

        $initialScorePayload['dominant_component_score'] =
            $score->dominant_component_score;

        $initialScorePayload['analysis_summary'] =
            $score->analysis_summary;

        $initialScorePayload['decision_recommendation'] =
            $score->decision_recommendation;

        $initialScorePayload['transport_analysis'] =
            $transportAnalysis;
    }
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="page-title mb-1">
            Mesin Skor Risiko
        </h2>

        <p class="text-muted mb-0">
            Weighted Risk Model: cuaca 30%, inflasi 20%,
            berita 40%, dan mata uang 10%.
        </p>
    </div>

    <div class="small text-muted text-lg-end">
        <div>Rentang penilaian</div>
        <strong class="text-body">0–100</strong>
    </div>
</div>

{{-- =====================================================
     RINGKASAN ANALISIS
===================================================== --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Total Perhitungan
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $summary['total_calculations'] ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-calculator fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Negara Dianalisis
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $summary['countries_analyzed'] ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-globe2 fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Risiko Tinggi
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $summary['high_risk_countries'] ?? 0,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-exclamation-triangle fs-3 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Rata-rata Skor
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{
                            number_format(
                                $summary['average_score'] ?? 0,
                                1,
                                ',',
                                '.'
                            )
                        }}
                    </h3>

                    <i class="bi bi-graph-up-arrow fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     FORM PERHITUNGAN
===================================================== --}}
<div class="card mb-4">
    <div class="card-body">
        <form
            id="riskForm"
            class="row g-3 align-items-end"
        >
            <div class="col-lg-9">
                <label for="riskCountry" class="form-label">
                    Negara
                </label>

                <select
                    id="riskCountry"
                    class="form-select"
                    required
                >
                    <option value="">
                        Pilih negara yang akan dianalisis
                    </option>

                    @foreach($countries as $country)
                        <option
                            value="{{ $country->id }}"
                            @selected($selected?->id === $country->id)
                        >
                            {{ $country->name }}
                            @if($country->code)
                                ({{ $country->code }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 d-grid">
                <button
                    id="calculateRiskButton"
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bi bi-calculator me-1"></i>
                    Hitung Risiko
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================
     HASIL RISIKO UTAMA
===================================================== --}}
<div
    id="riskResult"
    class="row g-4 mb-4 {{ $score ? '' : 'd-none' }}"
>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div
                    id="riskCountryName"
                    class="text-muted mb-2"
                >
                    Skor Risiko {{ $selected?->name }}
                </div>

                <div
                    id="riskTotal"
                    class="display-2 fw-bold text-primary"
                >
                    {{
                        $score
                            ? number_format(
                                $score->total_score,
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

                <div>
                    <span
                        id="riskLevel"
                        class="badge fs-6 {{
                            $score
                                ? (
                                    in_array(
                                        $score->risk_level_label,
                                        ['Tinggi', 'Kritis'],
                                        true
                                    )
                                        ? 'badge-high'
                                        : (
                                            $score->risk_level_label === 'Sedang'
                                                ? 'badge-medium'
                                                : 'badge-low'
                                        )
                                )
                                : ''
                        }}"
                    >
                        {{
                            $score
                                ? 'Risiko ' . $score->risk_level_label
                                : '-'
                        }}
                    </span>
                </div>

                <div
                    id="riskTime"
                    class="small text-muted mt-3"
                >
                    {{ $score?->calculated_at?->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="mb-1">
                        Komponen dan Bobot Risiko
                    </h5>

                    <p class="small text-muted mb-0">
                        Grafik memperlihatkan skor normalisasi dan kontribusi
                        berbobot setiap komponen.
                    </p>
                </div>

                <div style="min-height: 300px;">
                    <canvas id="riskChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     KARTU KOMPONEN RISIKO
===================================================== --}}
<div
    id="componentCards"
    class="row g-3 mb-4"
></div>

{{-- =====================================================
     RINGKASAN DAN REKOMENDASI KEPUTUSAN
===================================================== --}}
<div
    id="decisionPanel"
    class="row g-4 mb-4 {{ $score ? '' : 'd-none' }}"
>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-1">
                    Ringkasan Analisis Risiko
                </h5>

                <p class="small text-muted mb-0">
                    Interpretasi skor untuk membantu keputusan impor.
                </p>
            </div>

            <div class="card-body">
                <p
                    id="analysisSummary"
                    class="mb-4"
                >
                    {{ $score?->analysis_summary }}
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">
                                Faktor Dominan
                            </div>

                            <strong id="dominantComponent">
                                {{ $score?->dominant_component_label ?? '-' }}
                            </strong>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">
                                Skor Faktor Dominan
                            </div>

                            <strong id="dominantComponentScore">
                                {{
                                    $score
                                        ? number_format(
                                            $score->dominant_component_score,
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
                    Tindakan yang disarankan berdasarkan hasil analisis.
                </p>
            </div>

            <div class="card-body">
                <div class="d-flex gap-3">
                    <i class="bi bi-lightbulb fs-3 text-warning"></i>

                    <p
                        id="decisionRecommendation"
                        class="mb-0"
                    >
                        {{ $score?->decision_recommendation }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     ANALISIS GANGGUAN TRANSPORTASI
===================================================== --}}
<div
    id="transportPanel"
    class="card mb-4 {{ $transportAnalysis ? '' : 'd-none' }}"
>
    <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h5 class="mb-1">
                Potensi Gangguan Transportasi
            </h5>

            <p class="small text-muted mb-0">
                Analisis tambahan berdasarkan cuaca, berita logistik,
                geopolitik, dan ketersediaan pelabuhan.
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
                        class="badge fs-6 {{
                            isset($transportAnalysis['level'])
                                ? (
                                    in_array(
                                        $transportAnalysis['level'],
                                        ['Tinggi', 'Kritis'],
                                        true
                                    )
                                        ? 'badge-high'
                                        : (
                                            $transportAnalysis['level'] === 'Sedang'
                                                ? 'badge-medium'
                                                : 'badge-low'
                                        )
                                )
                                : ''
                        }}"
                    >
                        {{ $transportAnalysis['level'] ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="col-lg-9">
                <div
                    id="transportComponents"
                    class="row g-3 mb-4"
                ></div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <h6>
                            Faktor yang Ditemukan
                        </h6>

                        <ul
                            id="transportFactors"
                            class="mb-0 ps-3"
                        >
                            @foreach(($transportAnalysis['factors'] ?? []) as $factor)
                                <li class="mb-1">
                                    {{ $factor }}
                                </li>
                            @endforeach
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
     RIWAYAT PERHITUNGAN
===================================================== --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-1">
            Riwayat Perhitungan
        </h5>

        <p class="small text-muted mb-0">
            Riwayat skor risiko terbaru dari seluruh negara.
        </p>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Negara</th>
                    <th>Cuaca</th>
                    <th>Inflasi</th>
                    <th>Mata Uang</th>
                    <th>Berita</th>
                    <th>Total</th>
                    <th>Tingkat</th>
                </tr>
            </thead>

            <tbody id="riskHistory">
                @forelse($latest as $riskItem)
                    @php
                        $historyLevel =
                            $riskItem->risk_level_label
                            ?? $riskItem->risk_level;

                        $historyClass = in_array(
                            $historyLevel,
                            ['Tinggi', 'Kritis'],
                            true
                        )
                            ? 'badge-high'
                            : (
                                $historyLevel === 'Sedang'
                                    ? 'badge-medium'
                                    : 'badge-low'
                            );
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{ $riskItem->country?->name ?? '-' }}
                            </strong>
                        </td>

                        <td>
                            {{ number_format($riskItem->weather_score, 1, ',', '.') }}
                        </td>

                        <td>
                            {{ number_format($riskItem->inflation_score, 1, ',', '.') }}
                        </td>

                        <td>
                            {{ number_format($riskItem->currency_score, 1, ',', '.') }}
                        </td>

                        <td>
                            {{ number_format($riskItem->news_score, 1, ',', '.') }}
                        </td>

                        <td>
                            <strong>
                                {{ number_format($riskItem->total_score, 1, ',', '.') }}
                            </strong>
                        </td>

                        <td>
                            <span class="badge {{ $historyClass }}">
                                {{ $historyLevel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyRiskHistory">
                        <td
                            colspan="7"
                            class="text-center text-muted py-5"
                        >
                            Belum ada perhitungan risiko.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $latest->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let riskChart = null;

    const riskForm =
        document.getElementById('riskForm');

    const riskCountry =
        document.getElementById('riskCountry');

    const riskResult =
        document.getElementById('riskResult');

    const decisionPanel =
        document.getElementById('decisionPanel');

    const transportPanel =
        document.getElementById('transportPanel');

    const componentCards =
        document.getElementById('componentCards');

    const transportComponents =
        document.getElementById('transportComponents');

    const riskHistory =
        document.getElementById('riskHistory');

    const initialScore =
        @json($initialScorePayload);

    const riskWeights = {
        weather: 30,
        inflation: 20,
        news: 40,
        currency: 10,
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

    function selectedCountryName() {
        const option =
            riskCountry.options[
                riskCountry.selectedIndex
            ];

        if (!option || !option.value) {
            return '-';
        }

        return option.textContent
            .replace(/\s+/g, ' ')
            .trim();
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

        if (raw.includes('currency') || raw.includes('mata uang') || raw.includes('kurs')) {
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
                        rawValue:
                            Number(
                                component.raw_value
                                ?? component.original_value
                                ?? normalizedScore
                            ),
                        normalizedScore,
                        weight,
                        weightedScore,
                        notes:
                            component.notes
                            ?? component.description
                            ?? '',
                    };
                }
            );
        }

        return [
            {
                key: 'weather',
                label: 'Cuaca',
                rawValue: Number(score?.weather_score ?? 0),
                normalizedScore: Number(score?.weather_score ?? 0),
                weight: 30,
                weightedScore:
                    Number(score?.weather_score ?? 0) * 0.30,
                notes:
                    'Risiko cuaca berdasarkan kondisi cuaca terbaru.',
            },
            {
                key: 'inflation',
                label: 'Inflasi',
                rawValue: Number(score?.inflation_score ?? 0),
                normalizedScore: Number(score?.inflation_score ?? 0),
                weight: 20,
                weightedScore:
                    Number(score?.inflation_score ?? 0) * 0.20,
                notes:
                    'Risiko biaya produksi berdasarkan indikator inflasi.',
            },
            {
                key: 'currency',
                label: 'Mata Uang',
                rawValue: Number(score?.currency_score ?? 0),
                normalizedScore: Number(score?.currency_score ?? 0),
                weight: 10,
                weightedScore:
                    Number(score?.currency_score ?? 0) * 0.10,
                notes:
                    'Risiko perubahan biaya impor akibat nilai tukar.',
            },
            {
                key: 'news',
                label: 'Berita',
                rawValue: Number(score?.news_score ?? 0),
                normalizedScore: Number(score?.news_score ?? 0),
                weight: 40,
                weightedScore:
                    Number(score?.news_score ?? 0) * 0.40,
                notes:
                    'Risiko berita negatif, logistik, dan geopolitik.',
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

    function renderChart(components) {
        const chartElement =
            document.getElementById('riskChart');

        if (!chartElement) {
            return;
        }

        if (riskChart) {
            riskChart.destroy();
        }

        riskChart = new Chart(
            chartElement,
            {
                type: 'bar',
                data: {
                    labels:
                        components.map(
                            component => component.label
                        ),

                    datasets: [
                        {
                            label: 'Skor Normalisasi',
                            data:
                                components.map(
                                    component =>
                                        component.normalizedScore
                                ),
                        },
                        {
                            label: 'Kontribusi Berbobot',
                            data:
                                components.map(
                                    component =>
                                        component.weightedScore
                                ),
                        },
                    ],
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,

                    plugins: {
                        legend: {
                            position: 'bottom',
                        },

                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `${context.dataset.label}: ${number(context.raw, 2)}`;
                                },
                            },
                        },
                    },

                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: value => `${value}`,
                            },
                        },
                    },
                },
            }
        );
    }

    function renderComponentCards(components) {
        componentCards.innerHTML =
            components.map(function (component) {
                return `
                    <div class="col-md-6 col-xl-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <strong>
                                        ${sg.escape(component.label)}
                                    </strong>

                                    <span class="badge text-bg-secondary">
                                        Bobot ${number(component.weight, 0)}%
                                    </span>
                                </div>

                                <div class="fs-3 fw-bold text-primary my-3">
                                    ${number(component.weightedScore, 2)}
                                </div>

                                <div class="small">
                                    Nilai asli:
                                    <strong>
                                        ${number(component.rawValue, 2)}
                                    </strong>

                                    <br>

                                    Normalisasi:
                                    <strong>
                                        ${number(component.normalizedScore, 2)}
                                    </strong>
                                </div>

                                <p class="small text-muted mt-3 mb-0">
                                    ${sg.escape(component.notes || '-')}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
    }

    function transportComponentList(analysis) {
        const components =
            analysis?.components || {};

        return Object.entries(components)
            .map(function ([key, component]) {
                return {
                    key,
                    label:
                        component?.label
                        ?? componentLabels[key]
                        ?? key,
                    score:
                        Number(component?.score ?? 0),
                    weight:
                        Number(component?.weight ?? 0),
                    weightedScore:
                        Number(
                            component?.weighted_score
                            ?? 0
                        ),
                };
            });
    }

    function renderTransportAnalysis(analysis) {
        if (!analysis) {
            transportPanel.classList.add('d-none');
            return;
        }

        transportPanel.classList.remove('d-none');

        const level =
            normalizeRiskLevel(
                analysis.level,
                analysis.score
            );

        document.getElementById(
            'transportScore'
        ).textContent =
            number(analysis.score, 1);

        const levelElement =
            document.getElementById(
                'transportLevel'
            );

        levelElement.textContent =
            level;

        levelElement.className =
            `badge fs-6 ${riskClass(level)}`;

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
            transportComponentList(analysis);

        transportComponents.innerHTML =
            components.map(function (component) {
                return `
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted mb-1">
                                ${sg.escape(component.label)}
                            </div>

                            <div class="fs-4 fw-bold text-primary">
                                ${number(component.score, 1)}
                            </div>

                            <div class="small text-muted mt-2">
                                Bobot ${number(component.weight, 0)}%
                                ·
                                Kontribusi ${number(component.weightedScore, 2)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
    }

    function renderScore(score) {
        if (!score) {
            return;
        }

        riskResult.classList.remove('d-none');
        decisionPanel.classList.remove('d-none');

        const countryName =
            score?.country?.name
            || selectedCountryName();

        const level =
            normalizeRiskLevel(
                score.risk_level_label
                ?? score.risk_level,
                score.total_score
            );

        document.getElementById(
            'riskCountryName'
        ).textContent =
            `Skor Risiko ${countryName}`;

        document.getElementById(
            'riskTotal'
        ).textContent =
            number(score.total_score, 1);

        const levelElement =
            document.getElementById(
                'riskLevel'
            );

        levelElement.textContent =
            `Risiko ${level}`;

        levelElement.className =
            `badge fs-6 ${riskClass(level)}`;

        document.getElementById(
            'riskTime'
        ).textContent =
            score.calculated_at
                ? new Date(
                    score.calculated_at
                ).toLocaleString('id-ID')
                : '-';

        const components =
            buildRiskComponents(score);

        renderChart(components);
        renderComponentCards(components);

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

        const summary =
            score.analysis_summary
            || `Skor risiko ${countryName} adalah ${number(score.total_score, 1)}/100 dengan tingkat ${level}. Faktor dominan adalah ${dominant} (${number(dominantScore, 1)}/100).`;

        document.getElementById(
            'analysisSummary'
        ).textContent =
            summary;

        document.getElementById(
            'dominantComponent'
        ).textContent =
            dominant;

        document.getElementById(
            'dominantComponentScore'
        ).textContent =
            `${number(dominantScore, 1)}/100`;

        document.getElementById(
            'decisionRecommendation'
        ).textContent =
            score.decision_recommendation
            || fallbackRecommendation(
                level,
                dominant
            );

        renderTransportAnalysis(
            score.transport_analysis
        );
    }

    function prependHistory(score) {
        document.getElementById(
            'emptyRiskHistory'
        )?.remove();

        const countryName =
            score?.country?.name
            || selectedCountryName();

        const level =
            normalizeRiskLevel(
                score.risk_level_label
                ?? score.risk_level,
                score.total_score
            );

        riskHistory.insertAdjacentHTML(
            'afterbegin',
            `
                <tr>
                    <td>
                        <strong>
                            ${sg.escape(countryName)}
                        </strong>
                    </td>

                    <td>
                        ${number(score.weather_score, 1)}
                    </td>

                    <td>
                        ${number(score.inflation_score, 1)}
                    </td>

                    <td>
                        ${number(score.currency_score, 1)}
                    </td>

                    <td>
                        ${number(score.news_score, 1)}
                    </td>

                    <td>
                        <strong>
                            ${number(score.total_score, 1)}
                        </strong>
                    </td>

                    <td>
                        <span class="badge ${riskClass(level)}">
                            ${sg.escape(level)}
                        </span>
                    </td>
                </tr>
            `
        );
    }

    if (initialScore) {
        renderScore(initialScore);
    } else {
        renderTransportAnalysis(
            @json($transportAnalysis)
        );
    }

    riskForm.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            const countryId =
                riskCountry.value;

            if (!countryId) {
                sg.toast(
                    'Silakan pilih negara terlebih dahulu.',
                    false
                );

                return;
            }

            sg.loading(true);

            try {
                const payload =
                    await sg.fetchJson(
                        `{{ url('/api/risk/country') }}/${countryId}/calculate`,
                        {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                            },
                        }
                    );

                const score =
                    payload?.data;

                if (!score) {
                    throw new Error(
                        'Hasil perhitungan tidak tersedia.'
                    );
                }

                renderScore(score);
                prependHistory(score);

                sg.toast(
                    payload.message
                    || 'Skor risiko berhasil dihitung.'
                );

                history.replaceState(
                    {},
                    '',
                    `{{ route('risk.index') }}?country=${countryId}`
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