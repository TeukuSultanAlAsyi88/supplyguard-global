@extends('layouts.app')

@section('title', 'Dasbor Administrator')

@section('heading', 'Administration Control Center')

@php
    /*
    |--------------------------------------------------------------------------
    | Nilai default agar halaman tetap aman
    |--------------------------------------------------------------------------
    */

    $userCount = (int) ($userCount ?? 0);
    $portCount = (int) ($portCount ?? 0);
    $articleCount = (int) ($articleCount ?? 0);

    $positiveCount = (int) ($positiveCount ?? 0);
    $negativeCount = (int) ($negativeCount ?? 0);

    $apiLogCount = (int) ($apiLogCount ?? 0);
    $apiSuccess = (int) ($apiSuccess ?? 0);
    $apiFailed = (int) ($apiFailed ?? 0);
    $apiSuccessRate = (float) ($apiSuccessRate ?? 0);

    $logs = collect($logs ?? []);
    $recentUsers = collect($recentUsers ?? []);
    $recentArticles = collect($recentArticles ?? []);

    $totalSentimentWords = $positiveCount + $negativeCount;

    try {
        $lastApiText = $lastApiRequest
            ? \Carbon\Carbon::parse($lastApiRequest)->diffForHumans()
            : 'Belum tersedia';
    } catch (\Throwable $exception) {
        $lastApiText = 'Belum tersedia';
    }
@endphp

@push('styles')
<style>
    .admin-control-center {
        --admin-purple: #8b5cf6;
        --admin-purple-soft: rgba(139, 92, 246, 0.14);
        --admin-cyan: #22d3ee;
        --admin-green: #22c55e;
        --admin-yellow: #f59e0b;
        --admin-red: #ef4444;
        --admin-card: rgba(15, 27, 48, 0.96);
        --admin-border: rgba(148, 163, 184, 0.14);
    }

    .admin-hero {
        position: relative;
        padding: 30px;
        margin-bottom: 22px;
        overflow: hidden;
        border: 1px solid rgba(139, 92, 246, 0.26);
        border-radius: 20px;
        background:
            radial-gradient(
                circle at 88% 12%,
                rgba(34, 211, 238, 0.13),
                transparent 30%
            ),
            linear-gradient(
                135deg,
                rgba(31, 21, 62, 0.98),
                rgba(9, 22, 42, 0.98)
            );
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.20);
    }

    .admin-hero::after {
        position: absolute;
        right: -110px;
        bottom: -175px;
        width: 350px;
        height: 350px;
        border: 1px solid rgba(139, 92, 246, 0.13);
        border-radius: 50%;
        content: "";
    }

    .admin-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
    }

    .admin-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 11px;
        color: #c4b5fd;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
    }

    .admin-hero h1 {
        margin: 0;
        color: #ffffff;
        font-size: clamp(27px, 4vw, 40px);
        font-weight: 800;
        letter-spacing: -1px;
    }

    .admin-hero p {
        max-width: 720px;
        margin: 12px 0 0;
        color: #a8b5ca;
        font-size: 13px;
        line-height: 1.7;
    }

    .admin-system-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        margin-top: 18px;
        border: 1px solid rgba(34, 197, 94, 0.23);
        border-radius: 999px;
        background: rgba(34, 197, 94, 0.09);
        color: #86efac;
        font-size: 10px;
        font-weight: 600;
    }

    .admin-system-status::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 12px rgba(34, 197, 94, 0.8);
        content: "";
    }

    .admin-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .admin-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .admin-stat-card {
        position: relative;
        min-height: 150px;
        padding: 20px;
        overflow: hidden;
        border: 1px solid var(--admin-border);
        border-radius: 17px;
        background:
            linear-gradient(
                145deg,
                rgba(20, 34, 58, 0.98),
                rgba(11, 23, 42, 0.98)
            );
        box-shadow: 0 16px 38px rgba(0, 0, 0, 0.12);
    }

    .admin-stat-card::after {
        position: absolute;
        right: -32px;
        bottom: -48px;
        width: 115px;
        height: 115px;
        border-radius: 50%;
        background: rgba(139, 92, 246, 0.06);
        content: "";
    }

    .admin-stat-icon {
        position: relative;
        z-index: 2;
        width: 45px;
        height: 45px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: var(--admin-purple-soft);
        color: #c4b5fd;
        font-size: 20px;
    }

    .admin-stat-icon.cyan {
        background: rgba(34, 211, 238, 0.12);
        color: #67e8f9;
    }

    .admin-stat-icon.green {
        background: rgba(34, 197, 94, 0.12);
        color: #86efac;
    }

    .admin-stat-icon.orange {
        background: rgba(245, 158, 11, 0.12);
        color: #fcd34d;
    }

    .admin-stat-label {
        position: relative;
        z-index: 2;
        margin-top: 17px;
        color: #8d9db4;
        font-size: 11px;
    }

    .admin-stat-value {
        position: relative;
        z-index: 2;
        margin-top: 4px;
        color: #ffffff;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.7px;
    }

    .admin-stat-description {
        position: relative;
        z-index: 2;
        margin-top: 5px;
        color: #718198;
        font-size: 9px;
    }

    .admin-grid-two {
        display: grid;
        grid-template-columns:
            minmax(0, 1.2fr)
            minmax(310px, 0.8fr);
        gap: 18px;
        margin-bottom: 18px;
    }

    .admin-card {
        overflow: hidden;
        border: 1px solid var(--admin-border);
        border-radius: 18px;
        background:
            linear-gradient(
                145deg,
                rgba(17, 30, 51, 0.98),
                rgba(10, 21, 39, 0.98)
            );
        box-shadow: 0 16px 38px rgba(0, 0, 0, 0.10);
    }

    .admin-card-header {
        min-height: 72px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.11);
    }

    .admin-card-title {
        margin: 0;
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
    }

    .admin-card-subtitle {
        margin: 5px 0 0;
        color: #7b8aa1;
        font-size: 10px;
    }

    .admin-card-body {
        padding: 20px;
    }

    .admin-quick-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .admin-quick-link {
        min-height: 108px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 15px;
        border: 1px solid rgba(148, 163, 184, 0.11);
        border-radius: 13px;
        background: rgba(15, 23, 42, 0.35);
        color: #dce5f2;
        transition:
            transform 0.18s ease,
            border-color 0.18s ease,
            background 0.18s ease;
    }

    .admin-quick-link:hover {
        border-color: rgba(139, 92, 246, 0.35);
        background: rgba(139, 92, 246, 0.08);
        color: #ffffff;
        transform: translateY(-2px);
    }

    .admin-quick-icon {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: rgba(139, 92, 246, 0.13);
        color: #c4b5fd;
        font-size: 17px;
    }

    .admin-quick-link strong {
        display: block;
        margin-top: 10px;
        font-size: 11px;
        font-weight: 650;
    }

    .admin-quick-link small {
        display: block;
        margin-top: 4px;
        color: #718198;
        font-size: 9px;
        line-height: 1.5;
    }

    .admin-health-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .admin-health-item {
        padding: 15px;
        border: 1px solid rgba(148, 163, 184, 0.11);
        border-radius: 13px;
        background: rgba(15, 23, 42, 0.38);
    }

    .admin-health-item span {
        display: block;
        color: #7e8da5;
        font-size: 9px;
        text-transform: uppercase;
    }

    .admin-health-item strong {
        display: block;
        margin-top: 7px;
        color: #ffffff;
        font-size: 19px;
        font-weight: 750;
    }

    .admin-progress-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 9px;
        color: #a5b2c5;
        font-size: 10px;
    }

    .admin-progress {
        height: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.10);
    }

    .admin-progress-bar {
        height: 100%;
        border-radius: inherit;
        background:
            linear-gradient(
                90deg,
                #8b5cf6,
                #22d3ee
            );
        box-shadow: 0 0 16px rgba(34, 211, 238, 0.25);
    }

    .admin-sentiment-list {
        display: flex;
        flex-direction: column;
        gap: 13px;
    }

    .admin-sentiment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px;
        border: 1px solid rgba(148, 163, 184, 0.11);
        border-radius: 13px;
        background: rgba(15, 23, 42, 0.35);
    }

    .admin-sentiment-copy {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .admin-sentiment-icon {
        width: 39px;
        height: 39px;
        display: grid;
        place-items: center;
        border-radius: 11px;
        font-size: 17px;
    }

    .admin-sentiment-icon.positive {
        background: rgba(34, 197, 94, 0.12);
        color: #86efac;
    }

    .admin-sentiment-icon.negative {
        background: rgba(239, 68, 68, 0.12);
        color: #fca5a5;
    }

    .admin-sentiment-copy strong {
        display: block;
        color: #eef4fc;
        font-size: 11px;
    }

    .admin-sentiment-copy small {
        display: block;
        margin-top: 3px;
        color: #718198;
        font-size: 9px;
    }

    .admin-sentiment-value {
        color: #ffffff;
        font-size: 21px;
        font-weight: 750;
    }

    .admin-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .admin-table th {
        padding: 12px 15px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        color: #718198;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.45px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .admin-table td {
        padding: 13px 15px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        color: #cbd5e1;
        font-size: 10px;
        vertical-align: middle;
    }

    .admin-table tbody tr:hover {
        background: rgba(139, 92, 246, 0.035);
    }

    .admin-user {
        min-width: 190px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-user-avatar {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        flex-shrink: 0;
        border-radius: 10px;
        background: rgba(139, 92, 246, 0.14);
        color: #c4b5fd;
        font-size: 11px;
        font-weight: 700;
    }

    .admin-user strong {
        display: block;
        color: #f8fafc;
        font-size: 10px;
        font-weight: 600;
    }

    .admin-user small {
        display: block;
        margin-top: 3px;
        color: #718198;
        font-size: 8px;
    }

    .admin-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 650;
    }

    .admin-badge.success {
        background: rgba(34, 197, 94, 0.10);
        color: #86efac;
    }

    .admin-badge.failed {
        background: rgba(239, 68, 68, 0.10);
        color: #fca5a5;
    }

    .admin-badge.admin {
        background: rgba(139, 92, 246, 0.12);
        color: #c4b5fd;
    }

    .admin-badge.user {
        background: rgba(59, 130, 246, 0.11);
        color: #93c5fd;
    }

    .admin-empty {
        padding: 36px 20px;
        color: #718198;
        font-size: 11px;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .admin-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .admin-quick-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .admin-grid-two {
            grid-template-columns: 1fr;
        }

        .admin-hero-content {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 575.98px) {
        .admin-stat-grid,
        .admin-health-summary,
        .admin-quick-grid {
            grid-template-columns: 1fr;
        }

        .admin-hero {
            padding: 21px;
        }

        .admin-hero-actions {
            width: 100%;
            align-items: stretch;
            flex-direction: column;
        }

        .admin-hero-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<section class="admin-control-center">

    {{-- =====================================================
         HERO ADMINISTRATOR
    ====================================================== --}}
    <article class="admin-hero">
        <div class="admin-hero-content">
            <div>
                <span class="admin-eyebrow">
                    <i class="bi bi-shield-lock-fill"></i>
                    Administrator Access
                </span>

                <h1>
                    Administration Control Center
                </h1>

                <p>
                    Kelola pengguna, pelabuhan, artikel, kamus sentimen,
                    integrasi API, dan operasional SupplyGuard melalui
                    satu pusat kendali.
                </p>

                <span class="admin-system-status">
                    Sistem administrator aktif
                </span>
            </div>

            <div class="admin-hero-actions">
                <a
                    href="{{ route('dashboard') }}"
                    class="btn btn-outline-light"
                >
                    <i class="bi bi-display me-1"></i>
                    Dasbor Pengguna
                </a>

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.location.reload()"
                >
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Perbarui Data
                </button>
            </div>
        </div>
    </article>

    {{-- =====================================================
         STATISTIK UTAMA
    ====================================================== --}}
    <div class="admin-stat-grid">
        <article class="admin-stat-card">
            <span class="admin-stat-icon">
                <i class="bi bi-people-fill"></i>
            </span>

            <div class="admin-stat-label">
                Total Pengguna
            </div>

            <div class="admin-stat-value">
                {{ number_format($userCount, 0, ',', '.') }}
            </div>

            <div class="admin-stat-description">
                Seluruh akun yang terdaftar
            </div>
        </article>

        <article class="admin-stat-card">
            <span class="admin-stat-icon cyan">
                <i class="bi bi-anchor"></i>
            </span>

            <div class="admin-stat-label">
                Data Pelabuhan
            </div>

            <div class="admin-stat-value">
                {{ number_format($portCount, 0, ',', '.') }}
            </div>

            <div class="admin-stat-description">
                Pelabuhan tersimpan dalam sistem
            </div>
        </article>

        <article class="admin-stat-card">
            <span class="admin-stat-icon green">
                <i class="bi bi-newspaper"></i>
            </span>

            <div class="admin-stat-label">
                Artikel dan Berita
            </div>

            <div class="admin-stat-value">
                {{ number_format($articleCount, 0, ',', '.') }}
            </div>

            <div class="admin-stat-description">
                Konten intelijen yang tersimpan
            </div>
        </article>

        <article class="admin-stat-card">
            <span class="admin-stat-icon orange">
                <i class="bi bi-plug-fill"></i>
            </span>

            <div class="admin-stat-label">
                Keberhasilan API
            </div>

            <div class="admin-stat-value">
                {{ number_format($apiSuccessRate, 1, ',', '.') }}%
            </div>

            <div class="admin-stat-description">
                {{ $apiSuccess }} berhasil · {{ $apiFailed }} gagal
            </div>
        </article>
    </div>

    {{-- =====================================================
         AKSES CEPAT DAN KAMUS SENTIMEN
    ====================================================== --}}
    <div class="admin-grid-two">
        <article class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">
                        Akses Cepat Administrator
                    </h2>

                    <p class="admin-card-subtitle">
                        Buka modul pengelolaan utama SupplyGuard.
                    </p>
                </div>
            </div>

            <div class="admin-card-body">
                <div class="admin-quick-grid">
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="admin-quick-link"
                    >
                        <span class="admin-quick-icon">
                            <i class="bi bi-people"></i>
                        </span>

                        <div>
                            <strong>Kelola Pengguna</strong>
                            <small>
                                Atur akun, role, dan status pengguna.
                            </small>
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.ports.index') }}"
                        class="admin-quick-link"
                    >
                        <span class="admin-quick-icon">
                            <i class="bi bi-anchor"></i>
                        </span>

                        <div>
                            <strong>Kelola Pelabuhan</strong>
                            <small>
                                Tambah, ubah, hapus, dan import data.
                            </small>
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.articles.index') }}"
                        class="admin-quick-link"
                    >
                        <span class="admin-quick-icon">
                            <i class="bi bi-newspaper"></i>
                        </span>

                        <div>
                            <strong>Kelola Artikel</strong>
                            <small>
                                Atur artikel dan berita internal.
                            </small>
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.words.index') }}"
                        class="admin-quick-link"
                    >
                        <span class="admin-quick-icon">
                            <i class="bi bi-chat-square-text"></i>
                        </span>

                        <div>
                            <strong>Kamus Sentimen</strong>
                            <small>
                                Kelola kata positif dan negatif.
                            </small>
                        </div>
                    </a>

                    <a
                        href="{{ route('admin.api-logs.index') }}"
                        class="admin-quick-link"
                    >
                        <span class="admin-quick-icon">
                            <i class="bi bi-activity"></i>
                        </span>

                        <div>
                            <strong>Log Integrasi API</strong>
                            <small>
                                Periksa request berhasil dan gagal.
                            </small>
                        </div>
                    </a>

                    <a
                        href="{{ route('dashboard') }}"
                        class="admin-quick-link"
                    >
                        <span class="admin-quick-icon">
                            <i class="bi bi-display"></i>
                        </span>

                        <div>
                            <strong>Dasbor Pengguna</strong>
                            <small>
                                Buka pusat monitoring risiko global.
                            </small>
                        </div>
                    </a>
                </div>
            </div>
        </article>

        <article class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">
                        Kamus Sentimen
                    </h2>

                    <p class="admin-card-subtitle">
                        Kata pendukung analisis berita.
                    </p>
                </div>

                <span class="admin-badge admin">
                    {{ $totalSentimentWords }} kata
                </span>
            </div>

            <div class="admin-card-body">
                <div class="admin-sentiment-list">
                    <div class="admin-sentiment-item">
                        <div class="admin-sentiment-copy">
                            <span class="admin-sentiment-icon positive">
                                <i class="bi bi-emoji-smile"></i>
                            </span>

                            <div>
                                <strong>Kata Positif</strong>
                                <small>
                                    Mendukung sentimen positif
                                </small>
                            </div>
                        </div>

                        <span class="admin-sentiment-value">
                            {{ $positiveCount }}
                        </span>
                    </div>

                    <div class="admin-sentiment-item">
                        <div class="admin-sentiment-copy">
                            <span class="admin-sentiment-icon negative">
                                <i class="bi bi-emoji-frown"></i>
                            </span>

                            <div>
                                <strong>Kata Negatif</strong>
                                <small>
                                    Mendukung sentimen negatif
                                </small>
                            </div>
                        </div>

                        <span class="admin-sentiment-value">
                            {{ $negativeCount }}
                        </span>
                    </div>
                </div>
            </div>
        </article>
    </div>

    {{-- =====================================================
         KESEHATAN API
    ====================================================== --}}
    <article class="admin-card mb-4">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">
                    Kesehatan Sistem API
                </h2>

                <p class="admin-card-subtitle">
                    Ringkasan seluruh aktivitas integrasi data.
                </p>
            </div>

            <span class="admin-badge success">
                Terakhir {{ $lastApiText }}
            </span>
        </div>

        <div class="admin-card-body">
            <div class="admin-health-summary">
                <div class="admin-health-item">
                    <span>Total request</span>
                    <strong>
                        {{ number_format($apiLogCount, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="admin-health-item">
                    <span>Berhasil</span>
                    <strong style="color:#86efac">
                        {{ number_format($apiSuccess, 0, ',', '.') }}
                    </strong>
                </div>

                <div class="admin-health-item">
                    <span>Gagal</span>
                    <strong style="color:#fca5a5">
                        {{ number_format($apiFailed, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            <div class="admin-progress-label">
                <span>Tingkat keberhasilan API</span>

                <strong>
                    {{ number_format($apiSuccessRate, 1, ',', '.') }}%
                </strong>
            </div>

            <div class="admin-progress">
                <div
                    class="admin-progress-bar"
                    style="width: {{ min(max($apiSuccessRate, 0), 100) }}%"
                ></div>
            </div>
        </div>
    </article>

    {{-- =====================================================
         PENGGUNA DAN ARTIKEL TERBARU
    ====================================================== --}}
    <div class="admin-grid-two">
        <article class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">
                        Pengguna Terbaru
                    </h2>

                    <p class="admin-card-subtitle">
                        Akun yang baru bergabung ke SupplyGuard.
                    </p>
                </div>

                <a
                    href="{{ route('admin.users.index') }}"
                    class="btn btn-sm btn-outline-light"
                >
                    Lihat Semua
                </a>
            </div>

            @if($recentUsers->isNotEmpty())
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Role</th>
                                <th>Terdaftar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($recentUsers as $user)
                                @php
                                    $role = strtolower(
                                        trim((string) ($user->role ?? 'user'))
                                    );

                                    $isAdmin = in_array(
                                        $role,
                                        [
                                            'admin',
                                            'administrator',
                                        ],
                                        true
                                    ) || (bool) ($user->is_admin ?? false);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="admin-user">
                                            <span class="admin-user-avatar">
                                                {{
                                                    strtoupper(
                                                        substr(
                                                            $user->name ?? 'U',
                                                            0,
                                                            1
                                                        )
                                                    )
                                                }}
                                            </span>

                                            <div>
                                                <strong>
                                                    {{ $user->name ?? 'Pengguna' }}
                                                </strong>

                                                <small>
                                                    {{ $user->email ?? '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span
                                            class="
                                                admin-badge
                                                {{ $isAdmin ? 'admin' : 'user' }}
                                            "
                                        >
                                            {{
                                                $isAdmin
                                                    ? 'Administrator'
                                                    : 'Pengguna'
                                            }}
                                        </span>
                                    </td>

                                    <td>
                                        {{
                                            $user->created_at
                                                ? $user->created_at->diffForHumans()
                                                : '-'
                                        }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="admin-empty">
                    Belum tersedia data pengguna.
                </div>
            @endif
        </article>

        <article class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h2 class="admin-card-title">
                        Artikel Terbaru
                    </h2>

                    <p class="admin-card-subtitle">
                        Konten intelijen terbaru yang tersimpan.
                    </p>
                </div>

                <a
                    href="{{ route('admin.articles.index') }}"
                    class="btn btn-sm btn-outline-light"
                >
                    Lihat Semua
                </a>
            </div>

            @if($recentArticles->isNotEmpty())
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Dibuat</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($recentArticles as $article)
                                <tr>
                                    <td>
                                        <strong>
                                            {{
                                                \Illuminate\Support\Str::limit(
                                                    $article->title ?? 'Artikel',
                                                    55
                                                )
                                            }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{
                                            $article->created_at
                                                ? $article->created_at->diffForHumans()
                                                : '-'
                                        }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="admin-empty">
                    Belum tersedia artikel terbaru.
                </div>
            @endif
        </article>
    </div>

    {{-- =====================================================
         LOG API TERBARU
    ====================================================== --}}
    <article class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">
                    Log API Terbaru
                </h2>

                <p class="admin-card-subtitle">
                    Sepuluh aktivitas integrasi API terbaru.
                </p>
            </div>

            <a
                href="{{ route('admin.api-logs.index') }}"
                class="btn btn-sm btn-outline-light"
            >
                Lihat Semua
            </a>
        </div>

        @if($logs->isNotEmpty())
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Endpoint</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Respons</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $success = (bool) data_get(
                                    $log,
                                    'success',
                                    false
                                );

                                $requestedAt =
                                    data_get($log, 'requested_at')
                                    ?? data_get($log, 'created_at');

                                try {
                                    $requestedText = $requestedAt
                                        ? \Carbon\Carbon::parse(
                                            $requestedAt
                                        )->diffForHumans()
                                        : '-';
                                } catch (\Throwable $exception) {
                                    $requestedText = '-';
                                }
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{
                                            data_get($log, 'service')
                                            ?? data_get($log, 'api_name')
                                            ?? 'API'
                                        }}
                                    </strong>
                                </td>

                                <td>
                                    {{
                                        \Illuminate\Support\Str::limit(
                                            data_get($log, 'endpoint', '-'),
                                            48
                                        )
                                    }}
                                </td>

                                <td>
                                    {{ data_get($log, 'method', 'GET') }}
                                </td>

                                <td>
                                    <span
                                        class="
                                            admin-badge
                                            {{ $success ? 'success' : 'failed' }}
                                        "
                                    >
                                        {{ $success ? 'Berhasil' : 'Gagal' }}
                                    </span>
                                </td>

                                <td>
                                    @if(
                                        data_get(
                                            $log,
                                            'response_time_ms'
                                        ) !== null
                                    )
                                        {{
                                            number_format(
                                                data_get(
                                                    $log,
                                                    'response_time_ms'
                                                ),
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                        ms
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ $requestedText }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-empty">
                Log API akan muncul setelah layanan eksternal digunakan.
            </div>
        @endif
    </article>
</section>
@endsection