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


    /* =====================================================
       GLOBAL RISK MAP, TOP RISK, DAN BERITA BERGAMBAR
    ====================================================== */

    .dashboard-command-center .risk-intelligence-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.45fr)
            minmax(245px, 0.72fr)
            minmax(280px, 0.88fr);
        gap: 18px;
        margin-bottom: 24px;
    }

    .dashboard-command-center .risk-intelligence-grid > .card {
        min-width: 0;
    }

    .dashboard-command-center .risk-map-shell {
        position: relative;
        min-height: 430px;
        overflow: hidden;
        background:
            radial-gradient(
                circle at 50% 35%,
                rgba(34, 211, 238, 0.07),
                transparent 48%
            ),
            #07111f;
    }

    .dashboard-command-center #globalRiskMap {
        width: 100%;
        height: 430px;
        background: #07111f;
        z-index: 1;
    }

    .dashboard-command-center .risk-map-status {
        position: absolute;
        z-index: 500;
        top: 14px;
        left: 14px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        max-width: calc(100% - 28px);
        padding: 7px 10px;
        font-size: 0.64rem;
        color: #cbd5e1;
        background: rgba(7, 17, 31, 0.88);
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 9px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
        backdrop-filter: blur(10px);
    }

    .dashboard-command-center .risk-map-status i {
        color: #22d3ee;
    }

    .dashboard-command-center .risk-map-legend {
        position: absolute;
        z-index: 500;
        right: 14px;
        bottom: 14px;
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px 11px;
        max-width: calc(100% - 28px);
        padding: 9px 11px;
        font-size: 0.60rem;
        color: #cbd5e1;
        background: rgba(7, 17, 31, 0.90);
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
        backdrop-filter: blur(10px);
    }

    .dashboard-command-center .risk-map-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .dashboard-command-center .risk-map-legend-color {
        width: 9px;
        height: 9px;
        border-radius: 3px;
    }

    .dashboard-command-center .risk-map-legend-color.low {
        background: #22c55e;
    }

    .dashboard-command-center .risk-map-legend-color.medium {
        background: #eab308;
    }

    .dashboard-command-center .risk-map-legend-color.high {
        background: #f97316;
    }

    .dashboard-command-center .risk-map-legend-color.critical {
        background: #ef4444;
    }

    .dashboard-command-center .risk-map-legend-color.unknown {
        background: #64748b;
    }

    .dashboard-command-center .leaflet-container {
        font-family: Inter, system-ui, sans-serif;
    }

    .dashboard-command-center .leaflet-control-zoom a {
        color: #e2e8f0;
        background: rgba(8, 15, 29, 0.95);
        border-color: rgba(148, 163, 184, 0.15);
    }

    .dashboard-command-center .leaflet-control-zoom a:hover {
        color: #67e8f9;
        background: rgba(15, 28, 50, 0.98);
    }

    .dashboard-command-center .leaflet-control-attribution {
        color: #64748b;
        background: rgba(8, 15, 29, 0.82);
    }

    .dashboard-command-center .leaflet-control-attribution a {
        color: #67e8f9;
    }

    .dashboard-command-center .leaflet-popup-content-wrapper,
    .dashboard-command-center .leaflet-popup-tip {
        color: #dbeafe;
        background: #0d192b;
        border: 1px solid rgba(34, 211, 238, 0.17);
    }

    .dashboard-command-center .leaflet-popup-content {
        min-width: 190px;
        margin: 14px;
    }

    .dashboard-command-center .map-popup-title {
        margin-bottom: 7px;
        font-size: 0.82rem;
        font-weight: 750;
        color: #f8fafc;
    }

    .dashboard-command-center .map-popup-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 6px;
        font-size: 0.68rem;
        color: #94a3b8;
    }

    .dashboard-command-center .map-popup-row strong {
        color: #e2e8f0;
    }

    .dashboard-command-center .map-popup-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        margin-top: 12px;
        padding: 8px 10px;
        font-size: 0.68rem;
        font-weight: 700;
        color: #e0f2fe;
        text-decoration: none;
        background: linear-gradient(
            135deg,
            rgba(37, 99, 235, 0.86),
            rgba(6, 182, 212, 0.78)
        );
        border: 1px solid rgba(103, 232, 249, 0.18);
        border-radius: 8px;
    }

    .dashboard-command-center .map-popup-link:hover {
        color: #ffffff;
        filter: brightness(1.08);
    }

    .dashboard-command-center .top-risk-list {
        display: flex;
        flex-direction: column;
    }

    .dashboard-command-center .top-risk-item {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr) auto;
        align-items: center;
        gap: 10px;
        padding: 14px 17px;
        color: inherit;
        text-decoration: none;
        border-bottom: 1px solid rgba(148, 163, 184, 0.09);
        transition:
            background 0.18s ease,
            transform 0.18s ease;
    }

    .dashboard-command-center .top-risk-item:last-child {
        border-bottom: 0;
    }

    .dashboard-command-center .top-risk-item:hover {
        color: inherit;
        background: rgba(59, 130, 246, 0.055);
        transform: translateX(2px);
    }

    .dashboard-command-center .top-risk-rank {
        display: grid;
        width: 26px;
        height: 26px;
        place-items: center;
        font-size: 0.66rem;
        font-weight: 750;
        color: #dbeafe;
        background: rgba(148, 163, 184, 0.11);
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 50%;
    }

    .dashboard-command-center .top-risk-country {
        min-width: 0;
    }

    .dashboard-command-center .top-risk-country strong {
        display: block;
        overflow: hidden;
        font-size: 0.76rem;
        font-weight: 680;
        color: #f1f5f9;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dashboard-command-center .top-risk-country small {
        display: block;
        margin-top: 4px;
        font-size: 0.60rem;
        color: #64748b;
    }

    .dashboard-command-center .top-risk-value {
        text-align: right;
    }

    .dashboard-command-center .top-risk-value strong {
        display: block;
        font-size: 0.78rem;
        color: #f8fafc;
    }

    .dashboard-command-center .top-risk-value .badge {
        margin-top: 5px;
        font-size: 0.55rem;
    }

    .dashboard-command-center .badge-critical {
        color: #fecaca;
        background: rgba(239, 68, 68, 0.17);
        border: 1px solid rgba(239, 68, 68, 0.28);
    }

    .dashboard-command-center .news-feed-compact {
        padding: 2px 17px 8px;
    }

    .dashboard-command-center .news-item-with-image {
        display: grid;
        grid-template-columns: 78px minmax(0, 1fr);
        gap: 11px;
        padding: 13px 0;
        border-bottom: 1px solid rgba(148, 163, 184, 0.09);
    }

    .dashboard-command-center .news-item-with-image:last-child {
        border-bottom: 0;
    }

    .dashboard-command-center .news-thumbnail {
        position: relative;
        width: 78px;
        height: 62px;
        overflow: hidden;
        background:
            linear-gradient(
                145deg,
                rgba(37, 99, 235, 0.18),
                rgba(34, 211, 238, 0.08)
            );
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 10px;
    }

    .dashboard-command-center .news-thumbnail img {
        position: relative;
        z-index: 2;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.25s ease;
    }

    .dashboard-command-center .news-item-with-image:hover
    .news-thumbnail img {
        transform: scale(1.06);
    }

    .dashboard-command-center .news-thumbnail-placeholder {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: grid;
        place-items: center;
        font-size: 1.1rem;
        color: #67e8f9;
    }

    .dashboard-command-center .news-copy {
        min-width: 0;
    }

    .dashboard-command-center .news-copy .news-title {
        font-size: 0.72rem;
        line-height: 1.45;
    }

    .dashboard-command-center .news-copy .news-title a {
        color: inherit;
        text-decoration: none;
    }

    .dashboard-command-center .news-copy .news-title a:hover {
        color: #67e8f9;
    }

    .dashboard-command-center .news-sentiment-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
    }

    .dashboard-command-center .news-sentiment-row .badge {
        font-size: 0.53rem;
    }

    .dashboard-command-center .news-sentiment-row small {
        overflow: hidden;
        font-size: 0.58rem;
        color: #64748b;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 1199.98px) {
        .dashboard-command-center .risk-intelligence-grid {
            grid-template-columns:
                minmax(0, 1fr)
                minmax(280px, 0.72fr);
        }

        .dashboard-command-center .risk-map-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-command-center .risk-intelligence-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-command-center .risk-map-card {
            grid-column: auto;
        }

        .dashboard-command-center .risk-map-shell,
        .dashboard-command-center #globalRiskMap {
            min-height: 360px;
            height: 360px;
        }

        .dashboard-command-center .risk-map-legend {
            left: 10px;
            right: 10px;
            justify-content: flex-start;
        }
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

<div class="risk-intelligence-grid">

    {{-- Peta risiko global --}}
    <article class="card risk-map-card">
        <div class="card-header dashboard-card-header">
            <div
                class="d-flex align-items-start
                       justify-content-between gap-3"
            >
                <div>
                    <h2 class="card-title mb-0">
                        Peta Risiko Global
                    </h2>

                    <p class="card-description">
                        Klik negara pada peta untuk melihat skor
                        dan membuka halaman detail negara.
                    </p>
                </div>

                <a
                    href="{{ route('countries.index') }}"
                    class="btn btn-sm btn-light"
                >
                    <i class="bi bi-globe2 me-1"></i>
                    Data Negara
                </a>
            </div>
        </div>

        <div class="risk-map-shell">
            <div
                id="riskMapStatus"
                class="risk-map-status"
            >
                <i class="bi bi-arrow-repeat"></i>
                Memuat data risiko global...
            </div>

            <div
                id="globalRiskMap"
                aria-label="Peta interaktif risiko negara"
            ></div>

            <div class="risk-map-legend">
                <span class="risk-map-legend-item">
                    <span
                        class="risk-map-legend-color low"
                    ></span>
                    Rendah
                </span>

                <span class="risk-map-legend-item">
                    <span
                        class="risk-map-legend-color medium"
                    ></span>
                    Sedang
                </span>

                <span class="risk-map-legend-item">
                    <span
                        class="risk-map-legend-color high"
                    ></span>
                    Tinggi
                </span>

                <span class="risk-map-legend-item">
                    <span
                        class="risk-map-legend-color critical"
                    ></span>
                    Kritis
                </span>

                <span class="risk-map-legend-item">
                    <span
                        class="risk-map-legend-color unknown"
                    ></span>
                    Tidak tersedia
                </span>
            </div>
        </div>
    </article>

    {{-- Lima negara dengan risiko tertinggi --}}
    <article class="card">
        <div class="card-header dashboard-card-header">
            <div
                class="d-flex align-items-start
                       justify-content-between gap-2"
            >
                <div>
                    <h2 class="card-title mb-0">
                        5 Risiko Tertinggi
                    </h2>

                    <p class="card-description">
                        Skor risiko terbaru.
                    </p>
                </div>

                <a
                    href="{{ route('risk.index') }}"
                    class="btn btn-sm btn-light"
                    title="Lihat seluruh analisis risiko"
                >
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>

        <div
            id="topRiskList"
            class="top-risk-list"
        >
            @forelse($topRisks as $index => $risk)
                @php
                    $riskScore = (float) (
                        data_get($risk, 'total_score')
                        ?? data_get($risk, 'score')
                        ?? 0
                    );

                    $riskLevel = (string) (
                        data_get($risk, 'risk_level')
                        ?? data_get($risk, 'level')
                        ?? 'Tidak Tersedia'
                    );

                    $normalizedLevel = strtolower($riskLevel);

                    $riskBadgeClass = match (true) {
                        in_array(
                            $normalizedLevel,
                            ['rendah', 'low'],
                            true
                        ) => 'badge-low',

                        in_array(
                            $normalizedLevel,
                            ['sedang', 'medium', 'moderate'],
                            true
                        ) => 'badge-medium',

                        in_array(
                            $normalizedLevel,
                            ['kritis', 'critical'],
                            true
                        ) => 'badge-critical',

                        default => 'badge-high',
                    };

                    $countryId = data_get($risk, 'country.id')
                        ?? data_get($risk, 'country_id');

                    $countryName = data_get($risk, 'country.name')
                        ?? data_get($risk, 'country_name')
                        ?? '-';

                    $countryDetailUrl = $countryId
                        ? route('countries.show', $countryId)
                        : route('risk.index');
                @endphp

                <a
                    href="{{ $countryDetailUrl }}"
                    class="top-risk-item"
                >
                    <span class="top-risk-rank">
                        {{ $index + 1 }}
                    </span>

                    <span class="top-risk-country">
                        <strong>
                            {{ $countryName }}
                        </strong>

                        <small>
                            Klik untuk melihat detail
                        </small>
                    </span>

                    <span class="top-risk-value">
                        <strong>
                            {{ number_format($riskScore, 1) }}
                        </strong>

                        <span
                            class="badge {{ $riskBadgeClass }}"
                        >
                            {{ $riskLevel }}
                        </span>
                    </span>
                </a>
            @empty
                <div class="empty-state py-5">
                    <div class="empty-state-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>

                    <h5>Belum ada data risiko</h5>

                    <p>
                        Jalankan perhitungan risiko negara.
                    </p>

                    <a
                        href="{{ route('risk.index') }}"
                        class="btn btn-primary"
                    >
                        Hitung Risiko
                    </a>
                </div>
            @endforelse
        </div>
    </article>

    {{-- Berita terbaru dengan thumbnail --}}
    <article class="card">
        <div class="card-header dashboard-card-header">
            <div
                class="d-flex align-items-start
                       justify-content-between gap-2"
            >
                <div>
                    <h2 class="card-title mb-0">
                        Berita Terkini
                    </h2>

                    <p class="card-description">
                        Intelijen rantai pasok global.
                    </p>
                </div>

                <a
                    href="{{ route('news.index') }}"
                    class="btn btn-sm btn-light"
                    title="Lihat seluruh berita"
                >
                    <i class="bi bi-newspaper"></i>
                </a>
            </div>
        </div>

        <div
            id="recentNewsList"
            class="news-feed-compact"
        >
            @forelse($recentNews as $news)
                @php
                    $newsImage =
                        data_get($news, 'image_url')
                        ?? data_get($news, 'image')
                        ?? data_get($news, 'url_to_image')
                        ?? data_get($news, 'thumbnail_url')
                        ?? data_get($news, 'thumbnail');

                    $newsUrl =
                        data_get($news, 'url')
                        ?? data_get($news, 'link');

                    $newsSentiment =
                        data_get($news, 'sentiment')
                        ?? 'Netral';

                    $sentimentClass = match (
                        strtolower((string) $newsSentiment)
                    ) {
                        'positif', 'positive' =>
                            'text-bg-success',

                        'negatif', 'negative' =>
                            'text-bg-danger',

                        default =>
                            'text-bg-secondary',
                    };
                @endphp

                <article class="news-item-with-image">
                    <div class="news-thumbnail">
                        <span
                            class="news-thumbnail-placeholder"
                            aria-hidden="true"
                        >
                            <i class="bi bi-newspaper"></i>
                        </span>

                        @if($newsImage)
                            <img
                                src="{{ $newsImage }}"
                                alt=""
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                onerror="this.remove()"
                            >
                        @endif
                    </div>

                    <div class="news-copy">
                        <div class="news-title">
                            @if($newsUrl)
                                <a
                                    href="{{ $newsUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{
                                        data_get(
                                            $news,
                                            'title',
                                            'Berita SupplyGuard'
                                        )
                                    }}
                                </a>
                            @else
                                {{
                                    data_get(
                                        $news,
                                        'title',
                                        'Berita SupplyGuard'
                                    )
                                }}
                            @endif
                        </div>

                        <div class="news-sentiment-row">
                            <span
                                class="badge {{ $sentimentClass }}"
                            >
                                {{ $newsSentiment }}
                            </span>

                            <small>
                                {{
                                    data_get($news, 'source')
                                    ?? 'Sumber tidak tersedia'
                                }}
                                ·
                                {{
                                    data_get($news, 'published_at')
                                        ?->diffForHumans()
                                    ?? '-'
                                }}
                            </small>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-state py-5">
                    <div class="empty-state-icon">
                        <i class="bi bi-newspaper"></i>
                    </div>

                    <h5>Belum ada berita</h5>

                    <p>
                        Sinkronkan GNews untuk menampilkan berita.
                    </p>

                    <a
                        href="{{ route('news.index') }}"
                        class="btn btn-primary"
                    >
                        Buka Berita
                    </a>
                </div>
            @endforelse
        </div>
    </article>
</div>

{{-- =====================================================
     ANALYTICS AND SYSTEM HEALTH
===================================================== --}}
<div class="dashboard-section-label">
    Analytics & System Health
</div>

<div class="row g-4">

    {{-- Distribusi risiko --}}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header dashboard-card-header">
                <h2 class="card-title mb-0">
                    Distribusi Risiko Global
                </h2>

                <p class="card-description">
                    Komposisi negara berdasarkan tingkat risiko.
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
                                Hitung risiko negara untuk
                                menampilkan distribusi.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status integrasi API --}}
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
                            Status koneksi sumber data eksternal.
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
                                            Log muncul setelah sistem
                                            mengakses API eksternal.
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
        const AUTO_REFRESH_MS = 5 * 60 * 1000;

        const dashboardLiveUrl =
            @json(route('dashboard.live'));

        const riskApiUrl =
            @json(url('/api/risk?per_page=500'));

        const countriesApiUrl =
            @json(url('/api/countries?per_page=300'));

        const countryIndexUrl =
            @json(route('countries.index'));

        const countryDetailTemplate =
            @json(
                route(
                    'countries.show',
                    ['country' => '__COUNTRY__']
                )
            );

        const worldGeoJsonUrl =
            'https://cdn.jsdelivr.net/gh/johan/world.geo.json@master/countries.geo.json';

        const riskLabels = [
            'Rendah',
            'Sedang',
            'Tinggi',
            'Kritis'
        ];

        let riskChart = null;
        let riskMap = null;
        let riskGeoJsonLayer = null;
        let riskMarkerLayer = null;
        let isRefreshing = false;
        let isMapRefreshing = false;

        /**
         * Escape HTML agar data API aman saat dimasukkan
         * ke dalam template string.
         */
        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        /**
         * Memastikan URL berita menggunakan HTTP/HTTPS.
         */
        function safeHttpUrl(value) {
            if (!value) {
                return '';
            }

            try {
                const url = new URL(
                    String(value),
                    window.location.origin
                );

                if (
                    url.protocol === 'http:' ||
                    url.protocol === 'https:'
                ) {
                    return url.href;
                }
            } catch (error) {
                return '';
            }

            return '';
        }

        /**
         * Membuat URL detail negara berdasarkan ID.
         */
        function countryDetailUrl(countryId) {
            if (!countryId) {
                return countryIndexUrl;
            }

            return countryDetailTemplate.replace(
                '__COUNTRY__',
                encodeURIComponent(String(countryId))
            );
        }

        /**
         * Mengambil array dari beberapa bentuk response JSON.
         */
        function extractArray(payload) {
            if (Array.isArray(payload)) {
                return payload;
            }

            if (
                !payload ||
                typeof payload !== 'object'
            ) {
                return [];
            }

            const possibleKeys = [
                'data',
                'items',
                'results',
                'countries',
                'risks',
                'news'
            ];

            for (const key of possibleKeys) {
                const value = payload[key];

                if (Array.isArray(value)) {
                    return value;
                }

                if (
                    value &&
                    typeof value === 'object'
                ) {
                    const nested = extractArray(value);

                    if (nested.length) {
                        return nested;
                    }
                }
            }

            return [];
        }

        /**
         * Fetch JSON tanpa bergantung pada struktur helper lain.
         */
        async function fetchJson(url) {
            const response = await fetch(
                url,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json'
                    },
                    cache: 'no-store'
                }
            );

            if (!response.ok) {
                throw new Error(
                    `Request gagal dengan status ${response.status}.`
                );
            }

            return response.json();
        }

        /**
         * Normalisasi teks untuk proses pencocokan negara.
         */
        function normalizeCountryKey(value) {
            return String(value ?? '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '');
        }

        /**
         * Mengambil nama negara dari object API.
         */
        function countryNameFrom(item) {
            const name = item?.name;

            if (
                name &&
                typeof name === 'object'
            ) {
                return (
                    name.common ??
                    name.official ??
                    ''
                );
            }

            return (
                name ??
                item?.country_name ??
                item?.country?.name ??
                item?.country ??
                ''
            );
        }

        /**
         * Mengubah data negara API ke format internal.
         */
        function normalizeCountry(item) {
            const latlng =
                item?.latlng ??
                item?.coordinates ??
                item?.location ??
                [];

            const latitude = Number(
                item?.latitude ??
                item?.lat ??
                (
                    Array.isArray(latlng)
                        ? latlng[0]
                        : latlng?.latitude
                )
            );

            const longitude = Number(
                item?.longitude ??
                item?.lng ??
                item?.lon ??
                (
                    Array.isArray(latlng)
                        ? latlng[1]
                        : (
                            latlng?.longitude ??
                            latlng?.lng
                        )
                )
            );

            return {
                id:
                    item?.id ??
                    item?.country_id ??
                    null,

                name:
                    countryNameFrom(item),

                code2:
                    item?.cca2 ??
                    item?.iso2 ??
                    item?.iso_a2 ??
                    item?.alpha2_code ??
                    item?.code ??
                    '',

                code3:
                    item?.cca3 ??
                    item?.iso3 ??
                    item?.iso_a3 ??
                    item?.alpha3_code ??
                    '',

                latitude:
                    Number.isFinite(latitude)
                        ? latitude
                        : null,

                longitude:
                    Number.isFinite(longitude)
                        ? longitude
                        : null
            };
        }

        /**
         * Mengubah data risiko API ke format internal.
         */
        function normalizeRisk(item) {
            const country =
                (
                    item?.country &&
                    typeof item.country === 'object'
                )
                    ? item.country
                    : {};

            const score = Number(
                item?.total_score ??
                item?.risk_score ??
                item?.score ??
                item?.total ??
                0
            );

            return {
                id:
                    item?.id ??
                    item?.risk_score_id ??
                    null,

                countryId:
                    item?.country_id ??
                    country?.id ??
                    null,

                countryName:
                    item?.country_name ??
                    countryNameFrom(country) ??
                    '',

                code2:
                    item?.country_code ??
                    item?.iso2 ??
                    country?.cca2 ??
                    country?.iso2 ??
                    '',

                code3:
                    item?.iso3 ??
                    country?.cca3 ??
                    country?.iso3 ??
                    '',

                score:
                    Number.isFinite(score)
                        ? score
                        : 0,

                level:
                    item?.risk_level ??
                    item?.level ??
                    item?.risk_label ??
                    ''
            };
        }

        /**
         * Menentukan level risiko dari label atau skor.
         */
        function normalizedRiskLevel(
            level,
            score
        ) {
            const normalized =
                String(level ?? '').toLowerCase();

            if (
                normalized.includes('kritis') ||
                normalized.includes('critical')
            ) {
                return 'critical';
            }

            if (
                normalized.includes('tinggi') ||
                normalized.includes('high')
            ) {
                return 'high';
            }

            if (
                normalized.includes('sedang') ||
                normalized.includes('medium') ||
                normalized.includes('moderate')
            ) {
                return 'medium';
            }

            if (
                normalized.includes('rendah') ||
                normalized.includes('low')
            ) {
                return 'low';
            }

            const numericScore = Number(score || 0);

            if (numericScore >= 80) {
                return 'critical';
            }

            if (numericScore >= 60) {
                return 'high';
            }

            if (numericScore >= 30) {
                return 'medium';
            }

            if (numericScore > 0) {
                return 'low';
            }

            return 'unknown';
        }

        /**
         * Warna peta berdasarkan level risiko.
         */
        function riskColor(
            level,
            score
        ) {
            const normalized =
                normalizedRiskLevel(level, score);

            return {
                low: '#22c55e',
                medium: '#eab308',
                high: '#f97316',
                critical: '#ef4444',
                unknown: '#64748b'
            }[normalized];
        }

        /**
         * Label risiko dalam Bahasa Indonesia.
         */
        function riskLevelLabel(
            level,
            score
        ) {
            return {
                low: 'Rendah',
                medium: 'Sedang',
                high: 'Tinggi',
                critical: 'Kritis',
                unknown: 'Tidak Tersedia'
            }[
                normalizedRiskLevel(
                    level,
                    score
                )
            ];
        }

        /**
         * Class badge tingkat risiko.
         */
        function riskBadgeClass(level) {
            const normalized =
                normalizedRiskLevel(level, 0);

            if (normalized === 'low') {
                return 'badge-low';
            }

            if (normalized === 'medium') {
                return 'badge-medium';
            }

            if (normalized === 'critical') {
                return 'badge-critical';
            }

            return 'badge-high';
        }

        /**
         * Class badge sentimen.
         */
        function sentimentBadgeClass(sentiment) {
            const normalized =
                String(sentiment ?? '').toLowerCase();

            if (
                normalized === 'positif' ||
                normalized === 'positive'
            ) {
                return 'text-bg-success';
            }

            if (
                normalized === 'negatif' ||
                normalized === 'negative'
            ) {
                return 'text-bg-danger';
            }

            return 'text-bg-secondary';
        }

        /**
         * Membuat index pencarian negara dan risiko.
         */
        function buildCountryRiskIndex(
            countries,
            risks
        ) {
            const countryByKey = new Map();
            const riskByKey = new Map();

            countries.forEach(function (country) {
                const keys = [
                    country.id,
                    country.name,
                    country.code2,
                    country.code3
                ];

                keys.forEach(function (key) {
                    const normalized =
                        normalizeCountryKey(key);

                    if (normalized) {
                        countryByKey.set(
                            normalized,
                            country
                        );
                    }
                });
            });

            risks.forEach(function (risk) {
                const keys = [
                    risk.countryId,
                    risk.countryName,
                    risk.code2,
                    risk.code3
                ];

                keys.forEach(function (key) {
                    const normalized =
                        normalizeCountryKey(key);

                    if (normalized) {
                        riskByKey.set(
                            normalized,
                            risk
                        );
                    }
                });
            });

            return {
                countryByKey,
                riskByKey
            };
        }

        /**
         * Mencari data negara dari properti GeoJSON.
         */
        function matchCountryAndRisk(
            properties,
            indexes
        ) {
            const featureKeys = [
                properties?.name,
                properties?.NAME,
                properties?.admin,
                properties?.ADMIN,
                properties?.iso_a2,
                properties?.ISO_A2,
                properties?.iso_a3,
                properties?.ISO_A3,
                properties?.id
            ];

            let country = null;
            let risk = null;

            for (const key of featureKeys) {
                const normalized =
                    normalizeCountryKey(key);

                if (!normalized) {
                    continue;
                }

                country =
                    country ??
                    indexes.countryByKey.get(
                        normalized
                    );

                risk =
                    risk ??
                    indexes.riskByKey.get(
                        normalized
                    );
            }

            if (
                !risk &&
                country
            ) {
                const countryKeys = [
                    country.id,
                    country.name,
                    country.code2,
                    country.code3
                ];

                for (const key of countryKeys) {
                    const normalized =
                        normalizeCountryKey(key);

                    if (
                        normalized &&
                        indexes.riskByKey.has(normalized)
                    ) {
                        risk =
                            indexes.riskByKey.get(
                                normalized
                            );

                        break;
                    }
                }
            }

            return {
                country,
                risk
            };
        }

        /**
         * Isi popup pada negara atau marker.
         */
        function mapPopupHtml(
            country,
            risk,
            fallbackName
        ) {
            const countryName =
                country?.name ??
                risk?.countryName ??
                fallbackName ??
                'Negara';

            const countryId =
                country?.id ??
                risk?.countryId ??
                null;

            const score =
                Number(risk?.score ?? 0);

            const level =
                riskLevelLabel(
                    risk?.level,
                    score
                );

            return `
                <div>
                    <div class="map-popup-title">
                        ${escapeHtml(countryName)}
                    </div>

                    <div class="map-popup-row">
                        <span>Skor risiko</span>
                        <strong>
                            ${
                                risk
                                    ? score.toLocaleString(
                                        'id-ID',
                                        {
                                            minimumFractionDigits: 1,
                                            maximumFractionDigits: 1
                                        }
                                    )
                                    : '-'
                            }
                        </strong>
                    </div>

                    <div class="map-popup-row">
                        <span>Tingkat</span>
                        <strong>
                            ${escapeHtml(level)}
                        </strong>
                    </div>

                    <a
                        class="map-popup-link"
                        href="${escapeHtml(
                            countryDetailUrl(countryId)
                        )}"
                    >
                        <i class="bi bi-box-arrow-up-right"></i>
                        Lihat Detail Negara
                    </a>
                </div>
            `;
        }

        /**
         * Membuat peta Leaflet pertama kali.
         */
        function initializeRiskMap() {
            const mapElement =
                document.getElementById(
                    'globalRiskMap'
                );

            if (
                !mapElement ||
                typeof L === 'undefined'
            ) {
                const status =
                    document.getElementById(
                        'riskMapStatus'
                    );

                if (status) {
                    status.innerHTML = `
                        <i class="bi bi-exclamation-triangle"></i>
                        Leaflet belum dimuat.
                    `;
                }

                return false;
            }

            if (riskMap) {
                return true;
            }

            riskMap = L.map(
                mapElement,
                {
                    minZoom: 1,
                    maxZoom: 8,
                    zoomControl: true,
                    worldCopyJump: true,
                    attributionControl: true
                }
            ).setView(
                [18, 12],
                2
            );

            L.tileLayer(
                'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
                {
                    maxZoom: 19,
                    subdomains: 'abcd',
                    attribution:
                        '&copy; OpenStreetMap &copy; CARTO'
                }
            ).addTo(riskMap);

            riskMarkerLayer =
                L.layerGroup().addTo(riskMap);

            window.setTimeout(
                function () {
                    riskMap?.invalidateSize();
                },
                250
            );

            return true;
        }

        /**
         * Fallback marker jika GeoJSON tidak tersedia.
         */
        function renderCountryMarkers(
            countries,
            risks,
            indexes
        ) {
            riskMarkerLayer?.clearLayers();

            let rendered = 0;

            countries.forEach(function (country) {
                if (
                    country.latitude === null ||
                    country.longitude === null
                ) {
                    return;
                }

                const possibleKeys = [
                    country.id,
                    country.name,
                    country.code2,
                    country.code3
                ];

                let risk = null;

                for (const key of possibleKeys) {
                    const normalized =
                        normalizeCountryKey(key);

                    if (
                        normalized &&
                        indexes.riskByKey.has(normalized)
                    ) {
                        risk =
                            indexes.riskByKey.get(
                                normalized
                            );

                        break;
                    }
                }

                const marker = L.circleMarker(
                    [
                        country.latitude,
                        country.longitude
                    ],
                    {
                        radius: risk ? 6 : 4,
                        color: '#dbeafe',
                        weight: 0.8,
                        fillColor: riskColor(
                            risk?.level,
                            risk?.score
                        ),
                        fillOpacity: risk ? 0.85 : 0.40
                    }
                );

                marker.bindPopup(
                    mapPopupHtml(
                        country,
                        risk,
                        country.name
                    )
                );

                marker.addTo(riskMarkerLayer);

                rendered++;
            });

            return rendered;
        }

        /**
         * Mengambil data API dan menggambar peta risiko.
         */
        async function refreshRiskMap(
            showStatus = true
        ) {
            if (isMapRefreshing) {
                return;
            }

            if (!initializeRiskMap()) {
                return;
            }

            isMapRefreshing = true;

            const status =
                document.getElementById(
                    'riskMapStatus'
                );

            if (
                status &&
                showStatus
            ) {
                status.innerHTML = `
                    <i class="bi bi-arrow-repeat"></i>
                    Memuat data risiko global...
                `;
            }

            try {
                const results =
                    await Promise.allSettled([
                        fetchJson(riskApiUrl),
                        fetchJson(countriesApiUrl),
                        fetchJson(worldGeoJsonUrl)
                    ]);

                const riskPayload =
                    results[0].status === 'fulfilled'
                        ? results[0].value
                        : {};

                const countryPayload =
                    results[1].status === 'fulfilled'
                        ? results[1].value
                        : {};

                const geoJsonPayload =
                    results[2].status === 'fulfilled'
                        ? results[2].value
                        : null;

                const risks = extractArray(
                    riskPayload
                ).map(normalizeRisk);

                const countries = extractArray(
                    countryPayload
                ).map(normalizeCountry);

                const indexes =
                    buildCountryRiskIndex(
                        countries,
                        risks
                    );

                if (riskGeoJsonLayer) {
                    riskGeoJsonLayer.remove();
                    riskGeoJsonLayer = null;
                }

                riskMarkerLayer?.clearLayers();

                let renderedCountries = 0;

                if (
                    geoJsonPayload &&
                    Array.isArray(
                        geoJsonPayload.features
                    )
                ) {
                    riskGeoJsonLayer = L.geoJSON(
                        geoJsonPayload,
                        {
                            style(feature) {
                                const matched =
                                    matchCountryAndRisk(
                                        feature.properties,
                                        indexes
                                    );

                                const hasRisk =
                                    Boolean(matched.risk);

                                return {
                                    color:
                                        'rgba(203, 213, 225, 0.38)',

                                    weight: 0.65,

                                    fillColor:
                                        riskColor(
                                            matched.risk?.level,
                                            matched.risk?.score
                                        ),

                                    fillOpacity:
                                        hasRisk
                                            ? 0.73
                                            : 0.24
                                };
                            },

                            onEachFeature(
                                feature,
                                layer
                            ) {
                                const matched =
                                    matchCountryAndRisk(
                                        feature.properties,
                                        indexes
                                    );

                                const featureName =
                                    feature.properties?.name ??
                                    feature.properties?.NAME ??
                                    feature.properties?.admin ??
                                    'Negara';

                                layer.bindPopup(
                                    mapPopupHtml(
                                        matched.country,
                                        matched.risk,
                                        featureName
                                    )
                                );

                                layer.on({
                                    mouseover(event) {
                                        event.target.setStyle({
                                            weight: 1.8,
                                            color: '#67e8f9',
                                            fillOpacity: 0.88
                                        });

                                        event.target
                                            .bringToFront?.();
                                    },

                                    mouseout(event) {
                                        riskGeoJsonLayer
                                            ?.resetStyle(
                                                event.target
                                            );
                                    }
                                });

                                renderedCountries++;
                            }
                        }
                    ).addTo(riskMap);
                } else {
                    renderedCountries =
                        renderCountryMarkers(
                            countries,
                            risks,
                            indexes
                        );
                }

                if (status) {
                    status.innerHTML = `
                        <i class="bi bi-check-circle-fill"></i>
                        ${renderedCountries} negara ditampilkan ·
                        ${risks.length} data risiko tersedia
                    `;
                }
            } catch (error) {
                console.error(
                    'Peta risiko gagal dimuat:',
                    error
                );

                if (status) {
                    status.innerHTML = `
                        <i class="bi bi-exclamation-triangle"></i>
                        Data peta belum dapat dimuat.
                    `;
                }
            } finally {
                isMapRefreshing = false;

                window.setTimeout(
                    function () {
                        riskMap?.invalidateSize();
                    },
                    120
                );
            }
        }

        /**
         * Plugin tulisan total di tengah doughnut.
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
         * Mengubah status koneksi dashboard.
         */
        function setConnectionStatus(success) {
            const status =
                document.getElementById(
                    'liveStatus'
                );

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
         * Loading tombol perbarui data.
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

            const label =
                document.getElementById(
                    'refreshDashboardText'
                );

            if (
                !button ||
                !icon ||
                !label
            ) {
                return;
            }

            button.disabled = loading;

            if (loading) {
                icon.className =
                    'spinner-border spinner-border-sm me-2';

                label.textContent =
                    'Memperbarui...';

                return;
            }

            icon.className =
                'bi bi-arrow-repeat me-1';

            label.textContent =
                'Perbarui Data';
        }

        /**
         * Membuat atau memperbarui grafik risiko.
         */
        function updateRiskChart(levels = {}) {
            const values = riskLabels.map(
                function (label) {
                    return Number(
                        levels[label] ??
                        levels[
                            label.toLowerCase()
                        ] ??
                        0
                    );
                }
            );

            const hasData = values.some(
                function (value) {
                    return value > 0;
                }
            );

            document
                .getElementById('riskChartEmpty')
                ?.classList.toggle(
                    'd-none',
                    hasData
                );

            if (riskChart) {
                riskChart.data.datasets[0].data =
                    values;

                riskChart.update();

                return;
            }

            const canvas =
                document.getElementById(
                    'riskDistribution'
                );

            if (
                !canvas ||
                typeof Chart === 'undefined'
            ) {
                return;
            }

            riskChart = new Chart(
                canvas,
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
                                    'rgba(234, 179, 8, 0.90)',
                                    'rgba(249, 115, 22, 0.90)',
                                    'rgba(239, 68, 68, 0.90)'
                                ],

                                hoverBackgroundColor: [
                                    '#22c55e',
                                    '#eab308',
                                    '#f97316',
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
                                    padding: 15,

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
         * Memperbarui angka ringkasan.
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

                    if (!element) {
                        return;
                    }

                    element.textContent =
                        typeof sg !== 'undefined'
                            ? sg.number(value ?? 0)
                            : Number(
                                value ?? 0
                            ).toLocaleString('id-ID');
                }
            );
        }

        /**
         * Memperbarui lima risiko tertinggi.
         */
        function updateTopRisks(topRisks = []) {
            const container =
                document.getElementById(
                    'topRiskList'
                );

            if (!container) {
                return;
            }

            if (!topRisks.length) {
                container.innerHTML = `
                    <div class="empty-state py-5">
                        <div class="empty-state-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>

                        <h5>Belum ada data risiko</h5>

                        <p>
                            Jalankan perhitungan risiko negara.
                        </p>
                    </div>
                `;

                return;
            }

            container.innerHTML = topRisks
                .slice(0, 5)
                .map(function (risk, index) {
                    const score = Number(
                        risk.score ??
                        risk.total_score ??
                        0
                    );

                    const level =
                        risk.level ??
                        risk.risk_level ??
                        riskLevelLabel(
                            '',
                            score
                        );

                    const countryId =
                        risk.country_id ??
                        risk.country?.id ??
                        null;

                    const countryName =
                        (
                            risk.country &&
                            typeof risk.country === 'object'
                        )
                            ? (
                                risk.country.name ??
                                risk.country.common_name ??
                                '-'
                            )
                            : (
                                risk.country ??
                                risk.country_name ??
                                '-'
                            );

                    return `
                        <a
                            class="top-risk-item"
                            href="${escapeHtml(
                                countryDetailUrl(countryId)
                            )}"
                        >
                            <span class="top-risk-rank">
                                ${index + 1}
                            </span>

                            <span class="top-risk-country">
                                <strong>
                                    ${escapeHtml(countryName)}
                                </strong>

                                <small>
                                    ${escapeHtml(
                                        risk.calculated_human ??
                                        'Lihat detail negara'
                                    )}
                                </small>
                            </span>

                            <span class="top-risk-value">
                                <strong>
                                    ${score.toLocaleString(
                                        'id-ID',
                                        {
                                            minimumFractionDigits: 1,
                                            maximumFractionDigits: 1
                                        }
                                    )}
                                </strong>

                                <span
                                    class="badge
                                    ${riskBadgeClass(level)}"
                                >
                                    ${escapeHtml(level)}
                                </span>
                            </span>
                        </a>
                    `;
                })
                .join('');
        }

        /**
         * Memperbarui berita beserta thumbnail.
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
                    <div class="empty-state py-5">
                        <div class="empty-state-icon">
                            <i class="bi bi-newspaper"></i>
                        </div>

                        <h5>Belum ada berita</h5>

                        <p>
                            Sinkronkan GNews untuk
                            menampilkan berita.
                        </p>
                    </div>
                `;

                return;
            }

            container.innerHTML = recentNews
                .slice(0, 5)
                .map(function (news) {
                    const imageUrl = safeHttpUrl(
                        news.image_url ??
                        news.image ??
                        news.url_to_image ??
                        news.thumbnail_url ??
                        news.thumbnail
                    );

                    const articleUrl = safeHttpUrl(
                        news.url ??
                        news.link
                    );

                    const title =
                        news.title ??
                        'Berita SupplyGuard';

                    const titleContent =
                        articleUrl
                            ? `
                                <a
                                    href="${escapeHtml(articleUrl)}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    ${escapeHtml(title)}
                                </a>
                            `
                            : escapeHtml(title);

                    const imageContent =
                        imageUrl
                            ? `
                                <img
                                    src="${escapeHtml(imageUrl)}"
                                    alt=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                    onerror="this.remove()"
                                >
                            `
                            : '';

                    return `
                        <article class="news-item-with-image">
                            <div class="news-thumbnail">
                                <span
                                    class="news-thumbnail-placeholder"
                                >
                                    <i class="bi bi-newspaper"></i>
                                </span>

                                ${imageContent}
                            </div>

                            <div class="news-copy">
                                <div class="news-title">
                                    ${titleContent}
                                </div>

                                <div class="news-sentiment-row">
                                    <span
                                        class="badge
                                        ${sentimentBadgeClass(
                                            news.sentiment
                                        )}"
                                    >
                                        ${escapeHtml(
                                            news.sentiment ??
                                            'Netral'
                                        )}
                                    </span>

                                    <small>
                                        ${escapeHtml(
                                            news.source ?? '-'
                                        )}
                                        ·
                                        ${escapeHtml(
                                            news.published_human ??
                                            '-'
                                        )}
                                    </small>
                                </div>
                            </div>
                        </article>
                    `;
                })
                .join('');
        }

        /**
         * Memperbarui status integrasi API.
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
                                    Log muncul setelah sistem
                                    mengakses API eksternal.
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

                    return `
                        <tr>
                            <td>
                                <div
                                    class="d-flex
                                           align-items-center gap-2"
                                >
                                    <span
                                        class="api-service-dot
                                        ${
                                            isSuccess
                                                ? 'success'
                                                : 'failed'
                                        }"
                                    ></span>

                                    <strong>
                                        ${escapeHtml(
                                            log.service ?? '-'
                                        )}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                <span
                                    class="badge
                                    ${
                                        isSuccess
                                            ? 'text-bg-success'
                                            : 'text-bg-danger'
                                    }"
                                >
                                    <i
                                        class="bi
                                        ${
                                            isSuccess
                                                ? 'bi-check-circle'
                                                : 'bi-x-circle'
                                        }
                                        me-1"
                                    ></i>

                                    ${
                                        isSuccess
                                            ? 'Online'
                                            : 'Gagal'
                                    }
                                </span>
                            </td>

                            <td>
                                <strong>
                                    ${Number(
                                        log.response_time_ms ?? 0
                                    ).toLocaleString('id-ID')}
                                </strong>

                                <span class="text-muted">
                                    ms
                                </span>
                            </td>

                            <td class="text-muted">
                                ${escapeHtml(
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
         * Render seluruh komponen dashboard live.
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
                let payload;

                if (
                    typeof sg !== 'undefined' &&
                    typeof sg.fetchJson === 'function'
                ) {
                    payload = await sg.fetchJson(
                        dashboardLiveUrl,
                        {
                            method: 'GET',
                            cache: 'no-store'
                        }
                    );
                } else {
                    payload = await fetchJson(
                        dashboardLiveUrl
                    );
                }

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

                refreshRiskMap(false);

                if (
                    showNotification &&
                    typeof sg !== 'undefined'
                ) {
                    sg.toast(
                        'Data command center berhasil diperbarui.'
                    );
                }
            } catch (error) {
                console.error(error);
                setConnectionStatus(false);

                if (
                    showNotification &&
                    typeof sg !== 'undefined'
                ) {
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
         * Render awal grafik dan peta.
         */
        updateRiskChart(
            @json($riskLevels ?? [])
        );

        initializeRiskMap();
        refreshRiskMap(true);

        /**
         * Tombol perbarui data.
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
         * Refresh saat tab aktif kembali.
         */
        document.addEventListener(
            'visibilitychange',
            function () {
                if (!document.hidden) {
                    refreshDashboard(false);

                    window.setTimeout(
                        function () {
                            riskMap?.invalidateSize();
                        },
                        100
                    );
                }
            }
        );
    }
);
</script>
@endpush