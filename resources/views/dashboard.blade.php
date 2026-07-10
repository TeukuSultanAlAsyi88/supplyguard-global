@extends('layouts.app')

@section('title', 'Dasbor')
@section('heading', 'Global Risk Command Center')
@section('body-class', 'dashboard-command-center')

@push('styles')
<style>
    .dashboard-command-center .hero-status-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 9px;
        margin-top: 20px;
    }

    .dashboard-command-center .hero-status-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 34px;
        padding: 7px 11px;
        font-size: 0.67rem;
        color: #94a3b8;
        background: rgba(15, 23, 42, 0.55);
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }

    .dashboard-command-center .hero-status-item strong {
        color: #dbeafe;
        font-weight: 650;
    }

    .dashboard-command-center .dashboard-section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        font-size: 0.64rem;
        font-weight: 750;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.13em;
    }

    .dashboard-command-center .dashboard-section-label::before {
        width: 18px;
        height: 2px;
        content: "";
        background: linear-gradient(
            90deg,
            #3b82f6,
            #22d3ee
        );
        border-radius: 99px;
    }

    .dashboard-command-center .dashboard-card-header {
        padding: 22px 22px 15px;
        background: transparent !important;
        border-bottom: 1px solid rgba(148, 163, 184, 0.09);
    }

    .dashboard-command-center .dashboard-table-icon {
        display: grid;
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        place-items: center;
        color: #67e8f9;
        background: linear-gradient(
            145deg,
            rgba(59, 130, 246, 0.12),
            rgba(34, 211, 238, 0.05)
        );
        border: 1px solid rgba(34, 211, 238, 0.14);
        border-radius: 11px;
    }

    .dashboard-command-center .risk-score {
        min-width: 62px;
        font-size: 0.83rem;
        font-weight: 750;
        color: #f8fafc;
    }

    .dashboard-command-center .risk-score-meter {
        width: 72px;
        height: 4px;
        margin-top: 6px;
        overflow: hidden;
        background: rgba(148, 163, 184, 0.12);
        border-radius: 99px;
    }

    .dashboard-command-center .risk-score-meter span {
        display: block;
        height: 100%;
        background: linear-gradient(
            90deg,
            #22c55e,
            #f59e0b,
            #ef4444
        );
        border-radius: inherit;
    }

    .dashboard-command-center .news-item {
        position: relative;
        padding: 17px 0;
        border-bottom: 1px solid rgba(148, 163, 184, 0.09);
    }

    .dashboard-command-center .news-item:last-child {
        border-bottom: 0;
    }

    .dashboard-command-center .news-title {
        display: -webkit-box;
        overflow: hidden;
        font-size: 0.79rem;
        font-weight: 630;
        line-height: 1.55;
        color: #e2e8f0;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .dashboard-command-center .news-title:hover {
        color: #67e8f9;
    }

    .dashboard-command-center .news-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        margin-top: 9px;
        font-size: 0.66rem;
        color: #64748b;
    }

    .dashboard-command-center .api-service-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .dashboard-command-center .api-service-dot.success {
        background: #22c55e;
        box-shadow: 0 0 12px rgba(34, 197, 94, 0.45);
    }

    .dashboard-command-center .api-service-dot.failed {
        background: #ef4444;
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.42);
    }

    .dashboard-command-center .chart-wrapper {
        position: relative;
        height: 285px;
    }

    .dashboard-command-center .chart-empty {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: grid;
        place-items: center;
        padding: 25px;
        text-align: center;
        background: rgba(8, 15, 29, 0.64);
        border-radius: 14px;
        backdrop-filter: blur(4px);
    }

    .dashboard-command-center .chart-empty-icon {
        display: grid;
        width: 52px;
        height: 52px;
        margin: 0 auto 12px;
        place-items: center;
        font-size: 1.35rem;
        color: #67e8f9;
        background: rgba(34, 211, 238, 0.07);
        border: 1px solid rgba(34, 211, 238, 0.15);
        border-radius: 15px;
    }

    .dashboard-command-center .stat-card-blue::before {
        background: linear-gradient(
            90deg,
            #2563eb,
            #22d3ee
        );
    }

    .dashboard-command-center .stat-card-teal::before {
        background: linear-gradient(
            90deg,
            #14b8a6,
            #22d3ee
        );
    }

    .dashboard-command-center .stat-card-orange::before {
        background: linear-gradient(
            90deg,
            #f97316,
            #f59e0b
        );
    }

    .dashboard-command-center .stat-card-purple::before {
        background: linear-gradient(
            90deg,
            #8b5cf6,
            #d946ef
        );
    }

    @media (max-width: 767.98px) {
        .dashboard-command-center .hero-status-group {
            display: grid;
            grid-template-columns: 1fr;
        }

        .dashboard-command-center .hero-status-item {
            width: 100%;
        }

        .dashboard-command-center .dashboard-card-header {
            padding: 18px 17px 13px;
        }

        .dashboard-command-center .chart-wrapper {
            height: 250px;
        }
    }
</style>
@endpush

@section('content')

{{-- =====================================================
     GLOBAL RISK COMMAND CENTER HERO
===================================================== --}}
<section class="dashboard-hero">
    <div class="dashboard-hero-content">

        <div class="dashboard-hero-label">
            <span class="live-dot"></span>
            Global Supply Chain Intelligence
        </div>

        <h1 class="dashboard-hero-title">
            Pantau risiko dunia melalui
            <span>satu pusat kendali.</span>
        </h1>

        <p class="dashboard-hero-description">
            SupplyGuard mengintegrasikan data ekonomi, cuaca,
            nilai tukar, berita, sentimen, dan pelabuhan untuk
            membantu memantau perubahan risiko rantai pasok global
            secara lebih cepat dan terstruktur.
        </p>

        <div class="dashboard-hero-actions">
            <button
                id="refreshDashboard"
                type="button"
                class="btn btn-outline-primary"
            >
                <i
                    id="refreshDashboardIcon"
                    class="bi bi-arrow-repeat me-1"
                ></i>

                <span id="refreshDashboardText">
                    Perbarui Data
                </span>
            </button>

            <a
                href="{{ route('risk.index') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-shield-fill-check me-1"></i>
                Analisis Risiko
            </a>

            <a
                href="{{ route('visualization.index') }}"
                class="btn btn-light"
            >
                <i class="bi bi-bar-chart-fill me-1"></i>
                Visualisasi
            </a>
        </div>

        <div class="hero-status-group">

            <div class="hero-status-item">
                <span
                    id="liveStatus"
                    class="badge text-bg-success"
                >
                    <span class="live-dot"></span>
                    Sistem Aktif
                </span>
            </div>

            <div class="hero-status-item">
                <i class="bi bi-arrow-clockwise text-info"></i>
                Auto-refresh setiap 5 menit
            </div>

            <div class="hero-status-item">
                <i class="bi bi-clock-history text-info"></i>

                <span>
                    Tampilan:
                    <strong id="lastRefreshed">
                        {{ now()->translatedFormat('d M Y, H:i:s') }}
                    </strong>
                </span>
            </div>

            <div
                id="latestDataWrapper"
                class="hero-status-item
                    {{ empty($latestDataAt) ? 'd-none' : '' }}"
            >
                <i class="bi bi-database-check text-info"></i>

                <span>
                    Data terbaru:
                    <strong id="latestDataAt">
                        {{
                            $latestDataAt
                                ?->translatedFormat('d M Y, H:i:s')
                        }}
                    </strong>
                </span>
            </div>
        </div>
    </div>

    <div
        class="dashboard-hero-visual"
        aria-hidden="true"
    >
        <div class="command-orbit">
            <i class="bi bi-globe-asia-australia"></i>
        </div>
    </div>
</section>

{{-- =====================================================
     INTELLIGENCE OVERVIEW
===================================================== --}}
<div class="dashboard-section-label">
    Intelligence Overview
</div>

<div class="row g-4 mb-4">

    {{-- Total negara --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-card-blue h-100">
            <div
                class="card-body d-flex align-items-center
                       justify-content-between gap-3"
            >
                <div>
                    <div class="stat-label">
                        Total Negara
                    </div>

                    <div
                        id="countryCount"
                        class="stat-value"
                    >
                        {{ number_format($countryCount) }}
                    </div>

                    <div class="stat-meta">
                        <i class="bi bi-database-check"></i>
                        Negara dalam sistem
                    </div>
                </div>

                <div class="stat-icon">
                    <i class="bi bi-flag-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Total pelabuhan --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-card-teal h-100">
            <div
                class="card-body d-flex align-items-center
                       justify-content-between gap-3"
            >
                <div>
                    <div class="stat-label">
                        Total Pelabuhan
                    </div>

                    <div
                        id="portCount"
                        class="stat-value"
                    >
                        {{ number_format($portCount) }}
                    </div>

                    <div class="stat-meta">
                        <i class="bi bi-geo-alt"></i>
                        Titik logistik global
                    </div>
                </div>

                <div class="stat-icon stat-icon-teal">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Total berita --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-card-orange h-100">
            <div
                class="card-body d-flex align-items-center
                       justify-content-between gap-3"
            >
                <div>
                    <div class="stat-label">
                        Berita Intelijen
                    </div>

                    <div
                        id="newsCount"
                        class="stat-value"
                    >
                        {{ number_format($newsCount) }}
                    </div>

                    <div class="stat-meta">
                        <i class="bi bi-newspaper"></i>
                        Berita dan sentimen
                    </div>
                </div>

                <div class="stat-icon stat-icon-orange">
                    <i class="bi bi-newspaper"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Watchlist --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card stat-card-purple h-100">
            <div
                class="card-body d-flex align-items-center
                       justify-content-between gap-3"
            >
                <div>
                    <div class="stat-label">
                        Negara Dipantau
                    </div>

                    <div
                        id="watchlistCount"
                        class="stat-value"
                    >
                        {{ number_format($watchlistCount) }}
                    </div>

                    <div class="stat-meta">
                        <i class="bi bi-star"></i>
                        Watchlist pengguna
                    </div>
                </div>

                <div class="stat-icon stat-icon-purple">
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     GLOBAL RISK MONITORING
===================================================== --}}
<div class="dashboard-section-label">
    Global Risk Monitoring
</div>

<div class="row g-4 mb-4">

    {{-- Risk table --}}
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header dashboard-card-header">
                <div
                    class="d-flex align-items-start
                           justify-content-between gap-3"
                >
                    <div>
                        <h2 class="card-title mb-0">
                            Negara dengan Risiko Tertinggi
                        </h2>

                        <p class="card-description">
                            Lima negara berdasarkan hasil perhitungan
                            risiko terbaru.
                        </p>
                    </div>

                    <a
                        href="{{ route('risk.index') }}"
                        class="btn btn-sm btn-light"
                    >
                        <i class="bi bi-arrow-up-right me-1"></i>
                        Lihat Semua
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Negara</th>
                            <th>Skor Risiko</th>
                            <th>Tingkat</th>
                            <th>Terakhir Dihitung</th>
                        </tr>
                    </thead>

                    <tbody id="topRiskBody">
                        @forelse($topRisks as $risk)
                            @php
                                $riskScore = (float) (
                                    $risk->total_score ?? 0
                                );

                                $riskWidth = min(
                                    max($riskScore, 0),
                                    100
                                );

                                $riskBadgeClass =
                                    $risk->risk_level === 'Rendah'
                                        ? 'badge-low'
                                        : (
                                            $risk->risk_level === 'Sedang'
                                                ? 'badge-medium'
                                                : 'badge-high'
                                        );
                            @endphp

                            <tr>
                                <td>
                                    <div
                                        class="d-flex align-items-center
                                               gap-2"
                                    >
                                        <div class="dashboard-table-icon">
                                            <i class="bi bi-globe2"></i>
                                        </div>

                                        <div>
                                            <strong class="d-block">
                                                {{
                                                    $risk->country?->name
                                                        ?? '-'
                                                }}
                                            </strong>

                                            <span
                                                class="text-muted"
                                                style="font-size: 0.65rem;"
                                            >
                                                Global monitoring
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="risk-score">
                                        {{
                                            number_format(
                                                $riskScore,
                                                2
                                            )
                                        }}
                                    </div>

                                    <div class="risk-score-meter">
                                        <span
                                            style="width: {{ $riskWidth }}%;"
                                        ></span>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="badge
                                               {{ $riskBadgeClass }}"
                                    >
                                        {{ $risk->risk_level }}
                                    </span>
                                </td>

                                <td class="text-muted">
                                    {{
                                        $risk->calculated_at
                                            ?->diffForHumans()
                                            ?? '-'
                                    }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="4"
                                    class="py-5"
                                >
                                    <div class="empty-state py-3">
                                        <div class="empty-state-icon">
                                            <i
                                                class="bi
                                                       bi-shield-check"
                                            ></i>
                                        </div>

                                        <h5>
                                            Belum ada data risiko
                                        </h5>

                                        <p>
                                            Jalankan perhitungan untuk
                                            menampilkan skor risiko negara.
                                        </p>

                                        <a
                                            href="{{ route('risk.index') }}"
                                            class="btn btn-primary"
                                        >
                                            Hitung Risiko
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Risk chart --}}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header dashboard-card-header">
                <h2 class="card-title mb-0">
                    Distribusi Risiko Global
                </h2>

                <p class="card-description">
                    Komposisi negara dengan risiko rendah,
                    sedang, dan tinggi.
                </p>
            </div>

            <div class="card-body p-4">
                <div class="chart-wrapper">
                    <canvas id="riskDistribution"></canvas>

                    <div
                        id="riskChartEmpty"
                        class="chart-empty d-none"
                    >
                        <div>
                            <div class="chart-empty-icon">
                                <i class="bi bi-pie-chart"></i>
                            </div>

                            <div class="fw-semibold text-white">
                                Grafik belum tersedia
                            </div>

                            <div
                                class="text-muted mt-1"
                                style="font-size: 0.72rem;"
                            >
                                Hitung risiko beberapa negara untuk
                                menampilkan distribusi.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     NEWS INTELLIGENCE AND API HEALTH
===================================================== --}}
<div class="dashboard-section-label">
    Intelligence Feed & System Health
</div>

<div class="row g-4">

    {{-- News --}}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header dashboard-card-header">
                <div
                    class="d-flex justify-content-between
                           align-items-start gap-3"
                >
                    <div>
                        <h2 class="card-title mb-0">
                            Intelligence News Feed
                        </h2>

                        <p class="card-description">
                            Berita ekonomi dan rantai pasok terbaru.
                        </p>
                    </div>

                    <a
                        href="{{ route('news.index') }}"
                        class="btn btn-sm btn-light"
                    >
                        <i class="bi bi-newspaper me-1"></i>
                        Semua Berita
                    </a>
                </div>
            </div>

            <div
                id="recentNewsList"
                class="card-body px-4 py-1"
            >
                @forelse($recentNews as $news)
                    @php
                        $sentimentClass =
                            $news->sentiment === 'Positif'
                                ? 'text-bg-success'
                                : (
                                    $news->sentiment === 'Negatif'
                                        ? 'text-bg-danger'
                                        : 'text-bg-secondary'
                                );
                    @endphp

                    <article class="news-item">
                        <div
                            class="d-flex justify-content-between
                                   align-items-start gap-3"
                        >
                            <div class="news-title">
                                {{ $news->title }}
                            </div>

                            <span
                                class="badge flex-shrink-0
                                       {{ $sentimentClass }}"
                            >
                                {{ $news->sentiment ?? 'Netral' }}
                            </span>
                        </div>

                        <div class="news-meta">
                            <span>
                                <i class="bi bi-building me-1"></i>
                                {{ $news->source ?? '-' }}
                            </span>

                            <span>•</span>

                            <span>
                                <i class="bi bi-clock me-1"></i>

                                {{
                                    $news->published_at
                                        ?->diffForHumans()
                                        ?? '-'
                                }}
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-newspaper"></i>
                        </div>

                        <h5>
                            Belum ada berita
                        </h5>

                        <p>
                            Sinkronkan berita untuk menampilkan
                            informasi ekonomi dan rantai pasok.
                        </p>

                        <a
                            href="{{ route('news.index') }}"
                            class="btn btn-primary"
                        >
                            Buka Intelijen Berita
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- API health --}}
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header dashboard-card-header">
                <div
                    class="d-flex justify-content-between
                           align-items-start gap-3"
                >
                    <div>
                        <h2 class="card-title mb-0">
                            External Data System Health
                        </h2>

                        <p class="card-description">
                            Status koneksi layanan sumber data eksternal.
                        </p>
                    </div>

                    @if(auth()->user()?->isAdmin())
                        <a
                            href="{{ route('admin.api-logs.index') }}"
                            class="btn btn-sm btn-light"
                        >
                            <i class="bi bi-activity me-1"></i>
                            Lihat Log
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Respons</th>
                            <th>Pengecekan</th>
                        </tr>
                    </thead>

                    <tbody id="apiHealthBody">
                        @forelse($apiHealth as $log)
                            <tr>
                                <td>
                                    <div
                                        class="d-flex align-items-center
                                               gap-2"
                                    >
                                        <span
                                            class="api-service-dot
                                                {{
                                                    $log->success
                                                        ? 'success'
                                                        : 'failed'
                                                }}"
                                        ></span>

                                        <strong>
                                            {{ $log->service }}
                                        </strong>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="badge
                                            {{
                                                $log->success
                                                    ? 'text-bg-success'
                                                    : 'text-bg-danger'
                                            }}"
                                    >
                                        <i
                                            class="bi
                                                {{
                                                    $log->success
                                                        ? 'bi-check-circle'
                                                        : 'bi-x-circle'
                                                }}
                                                me-1"
                                        ></i>

                                        {{
                                            $log->success
                                                ? 'Online'
                                                : 'Gagal'
                                        }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{
                                            number_format(
                                                $log->response_time_ms
                                                    ?? 0
                                            )
                                        }}
                                    </strong>

                                    <span class="text-muted">
                                        ms
                                    </span>
                                </td>

                                <td class="text-muted">
                                    {{
                                        $log->requested_at
                                            ?->diffForHumans()
                                            ?? '-'
                                    }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="4"
                                    class="py-5"
                                >
                                    <div class="empty-state py-3">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-plug"></i>
                                        </div>

                                        <h5>
                                            Belum ada aktivitas API
                                        </h5>

                                        <p>
                                            Log akan muncul setelah sistem
                                            mengakses sumber data eksternal.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const AUTO_REFRESH_MS =
            5 * 60 * 1000;

        const dashboardLiveUrl =
            @json(route('dashboard.live'));

        const riskLabels = [
            'Rendah',
            'Sedang',
            'Tinggi'
        ];

        let riskChart = null;
        let isRefreshing = false;

        /**
         * Plugin tulisan total pada tengah grafik doughnut.
         */
        const riskCenterTextPlugin = {
            id: 'riskCenterText',

            afterDraw(chart) {
                if (
                    chart.config.type !== 'doughnut' ||
                    !chart.chartArea
                ) {
                    return;
                }

                const values =
                    chart.data.datasets[0]?.data ?? [];

                const total = values.reduce(
                    function (sum, value) {
                        return sum + Number(value || 0);
                    },
                    0
                );

                const context = chart.ctx;

                const centerX =
                    (
                        chart.chartArea.left +
                        chart.chartArea.right
                    ) / 2;

                const centerY =
                    (
                        chart.chartArea.top +
                        chart.chartArea.bottom
                    ) / 2 - 8;

                context.save();

                context.textAlign = 'center';
                context.textBaseline = 'middle';

                context.fillStyle = '#f8fafc';
                context.font =
                    '700 24px Inter, sans-serif';

                context.fillText(
                    String(total),
                    centerX,
                    centerY
                );

                context.fillStyle = '#64748b';
                context.font =
                    '500 10px Inter, sans-serif';

                context.fillText(
                    'NEGARA',
                    centerX,
                    centerY + 24
                );

                context.restore();
            }
        };

        /**
         * Class badge tingkat risiko.
         */
        function riskBadgeClass(level) {
            const normalized =
                String(level ?? '').toLowerCase();

            if (
                normalized === 'rendah' ||
                normalized === 'low'
            ) {
                return 'badge-low';
            }

            if (
                normalized === 'sedang' ||
                normalized === 'medium'
            ) {
                return 'badge-medium';
            }

            return 'badge-high';
        }

        /**
         * Class badge sentimen.
         */
        function sentimentBadgeClass(sentiment) {
            const normalized =
                String(sentiment ?? '').toLowerCase();

            if (normalized === 'positif') {
                return 'text-bg-success';
            }

            if (normalized === 'negatif') {
                return 'text-bg-danger';
            }

            return 'text-bg-secondary';
        }

        /**
         * Membatasi skor menjadi 0 sampai 100.
         */
        function riskWidth(score) {
            return Math.min(
                Math.max(
                    Number(score || 0),
                    0
                ),
                100
            );
        }

        /**
         * Mengubah status koneksi dashboard.
         */
        function setConnectionStatus(success) {
            const status =
                document.getElementById('liveStatus');

            if (!status) {
                return;
            }

            if (success) {
                status.className =
                    'badge text-bg-success';

                status.innerHTML = `
                    <span class="live-dot"></span>
                    Sistem Aktif
                `;

                return;
            }

            status.className =
                'badge text-bg-danger';

            status.innerHTML = `
                <i class="bi bi-wifi-off me-1"></i>
                Koneksi Bermasalah
            `;
        }

        /**
         * Loading tombol refresh.
         */
        function setRefreshButtonLoading(loading) {
            const button =
                document.getElementById(
                    'refreshDashboard'
                );

            const icon =
                document.getElementById(
                    'refreshDashboardIcon'
                );

            const text =
                document.getElementById(
                    'refreshDashboardText'
                );

            if (!button || !icon || !text) {
                return;
            }

            button.disabled = loading;

            if (loading) {
                icon.className =
                    'spinner-border spinner-border-sm me-2';

                text.textContent =
                    'Memperbarui...';

                return;
            }

            icon.className =
                'bi bi-arrow-repeat me-1';

            text.textContent =
                'Perbarui Data';
        }

        /**
         * Membuat atau memperbarui grafik risiko.
         */
        function updateRiskChart(levels = {}) {
            const values = riskLabels.map(
                function (label) {
                    return Number(
                        levels[label] ?? 0
                    );
                }
            );

            const hasData = values.some(
                function (value) {
                    return value > 0;
                }
            );

            const emptyElement =
                document.getElementById(
                    'riskChartEmpty'
                );

            emptyElement?.classList.toggle(
                'd-none',
                hasData
            );

            if (riskChart) {
                riskChart.data.datasets[0].data =
                    values;

                riskChart.update();

                return;
            }

            const chartCanvas =
                document.getElementById(
                    'riskDistribution'
                );

            if (!chartCanvas) {
                return;
            }

            riskChart = new Chart(
                chartCanvas,
                {
                    type: 'doughnut',

                    plugins: [
                        riskCenterTextPlugin
                    ],

                    data: {
                        labels: riskLabels,

                        datasets: [
                            {
                                data: values,

                                backgroundColor: [
                                    'rgba(34, 197, 94, 0.88)',
                                    'rgba(245, 158, 11, 0.88)',
                                    'rgba(239, 68, 68, 0.88)'
                                ],

                                hoverBackgroundColor: [
                                    '#22c55e',
                                    '#f59e0b',
                                    '#ef4444'
                                ],

                                borderColor: '#0d1628',
                                borderWidth: 5,
                                hoverOffset: 9
                            }
                        ]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',

                        animation: {
                            duration: 850
                        },

                        plugins: {
                            legend: {
                                position: 'bottom',

                                labels: {
                                    color: '#94a3b8',
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 18,

                                    font: {
                                        family: 'Inter',
                                        size: 10,
                                        weight: '500'
                                    }
                                }
                            },

                            tooltip: {
                                backgroundColor:
                                    'rgba(8, 15, 29, 0.97)',

                                titleColor: '#f8fafc',
                                bodyColor: '#cbd5e1',

                                borderColor:
                                    'rgba(34, 211, 238, 0.2)',

                                borderWidth: 1,
                                padding: 12,
                                displayColors: true,

                                callbacks: {
                                    label(context) {
                                        return (
                                            ' ' +
                                            context.label +
                                            ': ' +
                                            context.raw +
                                            ' negara'
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            );
        }

        /**
         * Memperbarui ringkasan angka.
         */
        function updateSummaryCards(counts = {}) {
            const elements = {
                countryCount: counts.countries,
                portCount: counts.ports,
                newsCount: counts.news,
                watchlistCount: counts.watchlists
            };

            Object.entries(elements).forEach(
                function ([id, value]) {
                    const element =
                        document.getElementById(id);

                    if (element) {
                        element.textContent =
                            sg.number(value ?? 0);
                    }
                }
            );
        }

        /**
         * Memperbarui tabel risiko.
         */
        function updateTopRisks(topRisks = []) {
            const body =
                document.getElementById(
                    'topRiskBody'
                );

            if (!body) {
                return;
            }

            if (!topRisks.length) {
                body.innerHTML = `
                    <tr>
                        <td colspan="4" class="py-5">
                            <div class="empty-state py-3">
                                <div class="empty-state-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>

                                <h5>Belum ada data risiko</h5>

                                <p>
                                    Jalankan perhitungan untuk
                                    menampilkan skor risiko negara.
                                </p>
                            </div>
                        </td>
                    </tr>
                `;

                return;
            }

            body.innerHTML = topRisks
                .map(function (risk) {
                    const score =
                        Number(risk.score ?? 0);

                    const meterWidth =
                        riskWidth(score);

                    return `
                        <tr>
                            <td>
                                <div
                                    class="d-flex align-items-center
                                           gap-2"
                                >
                                    <div class="dashboard-table-icon">
                                        <i class="bi bi-globe2"></i>
                                    </div>

                                    <div>
                                        <strong class="d-block">
                                            ${sg.escape(
                                                risk.country ?? '-'
                                            )}
                                        </strong>

                                        <span
                                            class="text-muted"
                                            style="font-size: 0.65rem;"
                                        >
                                            Global monitoring
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="risk-score">
                                    ${sg.number(score, 2)}
                                </div>

                                <div class="risk-score-meter">
                                    <span
                                        style="width: ${meterWidth}%;"
                                    ></span>
                                </div>
                            </td>

                            <td>
                                <span
                                    class="badge
                                           ${riskBadgeClass(
                                               risk.level
                                           )}"
                                >
                                    ${sg.escape(
                                        risk.level ?? 'Tinggi'
                                    )}
                                </span>
                            </td>

                            <td class="text-muted">
                                ${sg.escape(
                                    risk.calculated_human ?? '-'
                                )}
                            </td>
                        </tr>
                    `;
                })
                .join('');
        }

        /**
         * Memperbarui daftar berita.
         */
        function updateRecentNews(recentNews = []) {
            const container =
                document.getElementById(
                    'recentNewsList'
                );

            if (!container) {
                return;
            }

            if (!recentNews.length) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-newspaper"></i>
                        </div>

                        <h5>Belum ada berita</h5>

                        <p>
                            Sinkronkan berita untuk menampilkan
                            informasi ekonomi dan rantai pasok.
                        </p>
                    </div>
                `;

                return;
            }

            container.innerHTML = recentNews
                .map(function (news) {
                    return `
                        <article class="news-item">
                            <div
                                class="d-flex justify-content-between
                                       align-items-start gap-3"
                            >
                                <div class="news-title">
                                    ${sg.escape(
                                        news.title ?? '-'
                                    )}
                                </div>

                                <span
                                    class="badge flex-shrink-0
                                    ${sentimentBadgeClass(
                                        news.sentiment
                                    )}"
                                >
                                    ${sg.escape(
                                        news.sentiment ?? 'Netral'
                                    )}
                                </span>
                            </div>

                            <div class="news-meta">
                                <span>
                                    <i
                                        class="bi bi-building me-1"
                                    ></i>

                                    ${sg.escape(
                                        news.source ?? '-'
                                    )}
                                </span>

                                <span>•</span>

                                <span>
                                    <i
                                        class="bi bi-clock me-1"
                                    ></i>

                                    ${sg.escape(
                                        news.published_human ?? '-'
                                    )}
                                </span>
                            </div>
                        </article>
                    `;
                })
                .join('');
        }

        /**
         * Memperbarui status API.
         */
        function updateApiHealth(apiHealth = []) {
            const body =
                document.getElementById(
                    'apiHealthBody'
                );

            if (!body) {
                return;
            }

            if (!apiHealth.length) {
                body.innerHTML = `
                    <tr>
                        <td colspan="4" class="py-5">
                            <div class="empty-state py-3">
                                <div class="empty-state-icon">
                                    <i class="bi bi-plug"></i>
                                </div>

                                <h5>
                                    Belum ada aktivitas API
                                </h5>

                                <p>
                                    Log akan muncul setelah sistem
                                    mengakses sumber data eksternal.
                                </p>
                            </div>
                        </td>
                    </tr>
                `;

                return;
            }

            body.innerHTML = apiHealth
                .map(function (log) {
                    const isSuccess =
                        Boolean(log.success);

                    const statusClass =
                        isSuccess
                            ? 'text-bg-success'
                            : 'text-bg-danger';

                    const statusIcon =
                        isSuccess
                            ? 'bi-check-circle'
                            : 'bi-x-circle';

                    const statusText =
                        isSuccess
                            ? 'Online'
                            : 'Gagal';

                    const dotClass =
                        isSuccess
                            ? 'success'
                            : 'failed';

                    return `
                        <tr>
                            <td>
                                <div
                                    class="d-flex align-items-center
                                           gap-2"
                                >
                                    <span
                                        class="api-service-dot
                                               ${dotClass}"
                                    ></span>

                                    <strong>
                                        ${sg.escape(
                                            log.service ?? '-'
                                        )}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                <span
                                    class="badge ${statusClass}"
                                >
                                    <i
                                        class="bi ${statusIcon} me-1"
                                    ></i>

                                    ${statusText}
                                </span>
                            </td>

                            <td>
                                <strong>
                                    ${sg.number(
                                        log.response_time_ms ?? 0
                                    )}
                                </strong>

                                <span class="text-muted">
                                    ms
                                </span>
                            </td>

                            <td class="text-muted">
                                ${sg.escape(
                                    log.requested_human ?? '-'
                                )}
                            </td>
                        </tr>
                    `;
                })
                .join('');
        }

        /**
         * Memperbarui informasi waktu.
         */
        function updateTimeInformation(data = {}) {
            const lastRefreshed =
                document.getElementById(
                    'lastRefreshed'
                );

            const latestDataAt =
                document.getElementById(
                    'latestDataAt'
                );

            const latestWrapper =
                document.getElementById(
                    'latestDataWrapper'
                );

            if (lastRefreshed) {
                lastRefreshed.textContent =
                    data.refreshed_label ?? '-';
            }

            if (
                latestDataAt &&
                latestWrapper
            ) {
                const latestLabel =
                    data.latest_data_label ?? '';

                latestDataAt.textContent =
                    latestLabel || '-';

                latestWrapper.classList.toggle(
                    'd-none',
                    !latestLabel
                );
            }
        }

        /**
         * Merender seluruh dashboard.
         */
        function renderDashboard(data = {}) {
            updateSummaryCards(
                data.counts ?? {}
            );

            updateTopRisks(
                data.top_risks ?? []
            );

            updateRecentNews(
                data.recent_news ?? []
            );

            updateApiHealth(
                data.api_health ?? []
            );

            updateRiskChart(
                data.risk_levels ?? {}
            );

            updateTimeInformation(data);
        }

        /**
         * Mengambil data dashboard terbaru.
         */
        async function refreshDashboard(
            showNotification = false
        ) {
            if (isRefreshing) {
                return;
            }

            isRefreshing = true;

            setRefreshButtonLoading(true);

            try {
                const payload =
                    await sg.fetchJson(
                        dashboardLiveUrl,
                        {
                            method: 'GET',
                            cache: 'no-store'
                        }
                    );

                if (
                    !payload.success ||
                    !payload.data
                ) {
                    throw new Error(
                        payload.message ??
                        'Respons dashboard tidak valid.'
                    );
                }

                renderDashboard(payload.data);

                setConnectionStatus(true);

                if (showNotification) {
                    sg.toast(
                        'Data command center berhasil diperbarui.'
                    );
                }
            } catch (error) {
                console.error(error);

                setConnectionStatus(false);

                if (showNotification) {
                    sg.toast(
                        error.message ??
                        'Dashboard gagal diperbarui.',
                        false
                    );
                }
            } finally {
                isRefreshing = false;

                setRefreshButtonLoading(false);
            }
        }

        /**
         * Grafik pertama kali dimuat.
         */
        updateRiskChart(
            @json($riskLevels ?? [])
        );

        /**
         * Tombol refresh manual.
         */
        document
            .getElementById('refreshDashboard')
            ?.addEventListener(
                'click',
                function () {
                    refreshDashboard(true);
                }
            );

        /**
         * Auto-refresh setiap lima menit.
         */
        window.setInterval(
            function () {
                if (!document.hidden) {
                    refreshDashboard(false);
                }
            },
            AUTO_REFRESH_MS
        );

        /**
         * Refresh ketika pengguna kembali membuka tab.
         */
        document.addEventListener(
            'visibilitychange',
            function () {
                if (!document.hidden) {
                    refreshDashboard(false);
                }
            }
        );
    }
);
</script>
@endpush