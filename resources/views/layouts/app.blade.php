<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="theme-color"
        content="#050816"
    >

    <meta
        name="description"
        content="SupplyGuard Indonesia — Platform monitoring dan analisis risiko rantai pasok global."
    >

    <title>
        @yield('title', 'Dasbor') — SupplyGuard Indonesia
    </title>

    {{-- =====================================================
         FONT INTER
    ====================================================== --}}
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- =====================================================
         BOOTSTRAP
    ====================================================== --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    {{-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    {{-- =====================================================
         LEAFLET
    ====================================================== --}}
    <link
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        rel="stylesheet"
    >

    {{-- =====================================================
         CSS UTAMA SUPPLYGUARD

         Lokasi:
         public/css/supplyguard.css
    ====================================================== --}}
    <link
        href="{{ asset('css/supplyguard.css') }}"
        rel="stylesheet"
    >

    @stack('styles')
</head>

<body class="app-dark-theme @yield('body-class')">

@php
    $currentUser = auth()->user();

    $userName = $currentUser?->name ?? 'Pengguna';

    $userInitial = strtoupper(
        substr($userName, 0, 1)
    );

    $userRole = $currentUser?->isAdmin()
        ? 'Administrator'
        : 'Pengguna';
@endphp

{{-- =====================================================
     GLOBAL LOADING
===================================================== --}}
<div
    id="globalLoader"
    class="loading-overlay"
    aria-hidden="true"
>
    <div class="loading-card">
        <div
            class="spinner-border spinner-border-sm"
            role="status"
            aria-label="Memuat"
        ></div>

        <div>
            <div class="fw-semibold">
                Memproses data
            </div>

            <div class="text-muted">
                Mohon tunggu sebentar...
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     SIDEBAR BACKDROP MOBILE
===================================================== --}}
<div
    id="sidebarBackdrop"
    class="sidebar-backdrop"
    aria-hidden="true"
></div>

{{-- =====================================================
     TOAST NOTIFICATION
===================================================== --}}
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div
        id="appToast"
        class="toast"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
    >
        <div class="toast-header">
            <div
                class="me-2 rounded-circle bg-info"
                style="width: 8px; height: 8px;"
            ></div>

            <strong class="me-auto">
                SupplyGuard Indonesia
            </strong>

            <small class="text-muted">
                Sistem
            </small>

            <button
                type="button"
                class="btn-close ms-2"
                data-bs-dismiss="toast"
                aria-label="Tutup"
            ></button>
        </div>

        <div
            id="appToastBody"
            class="toast-body"
        ></div>
    </div>
</div>

{{-- =====================================================
     SIDEBAR
===================================================== --}}
<aside
    id="sidebar"
    class="sidebar"
    aria-label="Navigasi utama"
>

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">
            <i class="bi bi-globe-asia-australia"></i>
        </div>

        <div>
            <h1 class="sidebar-brand-name">
                SupplyGuard
            </h1>

            <div class="sidebar-brand-subtitle">
                Global Risk Intelligence
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="sidebar-navigation">

        <div class="nav-section">
            Command Center
        </div>

        <nav class="nav flex-column">

            {{-- Dashboard --}}
            <a
                href="{{ route('dashboard') }}"
                class="nav-link
                    {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}"
            >
                <i class="bi bi-grid-1x2-fill"></i>

                <span>
                    Dasbor
                </span>
            </a>

            {{-- Data Negara --}}
            <a
                href="{{ route('countries.index') }}"
                class="nav-link
                    {{ request()->routeIs('countries.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('countries.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-flag-fill"></i>

                <span>
                    Data Negara
                </span>
            </a>

            {{-- Perbandingan Negara --}}
            <a
                href="{{ route('comparison.index') }}"
                class="nav-link
                    {{ request()->routeIs('comparison.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('comparison.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-arrow-left-right"></i>

                <span>
                    Perbandingan Negara
                </span>
            </a>

            {{-- Cuaca --}}
            <a
                href="{{ route('weather.index') }}"
                class="nav-link
                    {{ request()->routeIs('weather.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('weather.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-cloud-sun-fill"></i>

                <span>
                    Pemantauan Cuaca
                </span>
            </a>

            {{-- Nilai Tukar --}}
            <a
                href="{{ route('currency.index') }}"
                class="nav-link
                    {{ request()->routeIs('currency.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('currency.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-currency-exchange"></i>

                <span>
                    Dampak Nilai Tukar
                </span>
            </a>

            {{-- Risiko --}}
            <a
                href="{{ route('risk.index') }}"
                class="nav-link
                    {{ request()->routeIs('risk.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('risk.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-shield-fill-exclamation"></i>

                <span>
                    Analisis Risiko
                </span>
            </a>

            {{-- Visualisasi --}}
            <a
                href="{{ route('visualization.index') }}"
                class="nav-link
                    {{ request()->routeIs('visualization.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('visualization.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-bar-chart-fill"></i>

                <span>
                    Visualisasi Data
                </span>
            </a>

            {{-- Pelabuhan --}}
            <a
                href="{{ route('ports.index') }}"
                class="nav-link
                    {{ request()->routeIs('ports.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('ports.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-geo-alt-fill"></i>

                <span>
                    Lokasi Pelabuhan
                </span>
            </a>

            {{-- Berita --}}
            <a
                href="{{ route('news.index') }}"
                class="nav-link
                    {{ request()->routeIs('news.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('news.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-newspaper"></i>

                <span>
                    Intelijen Berita
                </span>
            </a>

            {{-- Watchlist --}}
            <a
                href="{{ route('watchlists.index') }}"
                class="nav-link
                    {{ request()->routeIs('watchlists.*') ? 'active' : '' }}"
                aria-current="{{ request()->routeIs('watchlists.*') ? 'page' : 'false' }}"
            >
                <i class="bi bi-star-fill"></i>

                <span>
                    Daftar Pemantauan
                </span>
            </a>
        </nav>

        {{-- =================================================
             ADMINISTRATOR
        ================================================== --}}
        @if($currentUser?->isAdmin())
            <div class="nav-section">
                Administrator
            </div>

            <nav class="nav flex-column">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="nav-link
                        {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                    aria-current="{{ request()->routeIs('admin.*') ? 'page' : 'false' }}"
                >
                    <i class="bi bi-sliders2"></i>

                    <span>
                        Dasbor Admin
                    </span>
                </a>
            </nav>
        @endif

        {{-- =================================================
             USER PROFILE SIDEBAR
        ================================================== --}}
        <div class="sidebar-user">
            <div class="d-flex align-items-center gap-2">

                <div class="sidebar-user-avatar">
                    {{ $userInitial }}
                </div>

                <div class="overflow-hidden flex-grow-1">
                    <div class="sidebar-user-name">
                        {{ $userName }}
                    </div>

                    <div class="sidebar-user-role">
                        {{ $userRole }}
                    </div>
                </div>

                <div
                    class="text-success"
                    title="Pengguna sedang aktif"
                >
                    <i class="bi bi-circle-fill"
                       style="font-size: 0.42rem;"></i>
                </div>
            </div>
        </div>
    </div>
</aside>

{{-- =====================================================
     MAIN CONTENT
===================================================== --}}
<main class="main">

    {{-- =================================================
         TOPBAR
    ================================================== --}}
    <header class="topbar">

        <div class="d-flex align-items-center gap-3">

            {{-- Mobile sidebar button --}}
            <button
                id="sidebarToggle"
                type="button"
                class="sidebar-toggle d-lg-none"
                aria-label="Buka menu navigasi"
                aria-controls="sidebar"
                aria-expanded="false"
            >
                <i class="bi bi-list fs-5"></i>
            </button>

            {{-- Heading --}}
            <div>
                <h2 class="topbar-title">
                    @yield('heading', 'SupplyGuard Indonesia')
                </h2>

                <div class="topbar-subtitle">
                    Global Supply Chain Risk Command Center
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">

            {{-- System online --}}
            <div
                class="d-none d-md-flex align-items-center gap-1 me-2"
                title="Sistem sedang aktif"
            >
                <span class="live-dot"></span>

                <small class="text-muted">
                    System Online
                </small>
            </div>

            {{-- Current date --}}
            <div
                class="d-none d-xl-flex align-items-center gap-2 me-2 px-3 py-2 rounded-3"
                style="
                    background: rgba(15, 23, 42, 0.65);
                    border: 1px solid rgba(148, 163, 184, 0.13);
                "
            >
                <i class="bi bi-calendar3 text-info"></i>

                <span
                    class="text-muted"
                    style="font-size: 0.7rem;"
                >
                    {{ now()->translatedFormat('d M Y') }}
                </span>
            </div>

            {{-- User dropdown --}}
            <div class="dropdown">
                <button
                    type="button"
                    class="topbar-user-button dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Menu pengguna"
                >
                    <span class="topbar-avatar">
                        {{ $userInitial }}
                    </span>

                    <span class="topbar-user-name text-start">
                        <span class="d-block small fw-semibold">
                            {{ $userName }}
                        </span>

                        <span
                            class="d-block text-muted"
                            style="font-size: 0.64rem;"
                        >
                            {{ $userRole }}
                        </span>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    {{-- User information --}}
                    <li>
                        <div class="px-3 py-2">
                            <div class="d-flex align-items-center gap-2">

                                <div class="topbar-avatar">
                                    {{ $userInitial }}
                                </div>

                                <div class="overflow-hidden">
                                    <div class="small fw-semibold text-white">
                                        {{ $userName }}
                                    </div>

                                    <div
                                        class="text-muted text-truncate"
                                        style="
                                            max-width: 180px;
                                            font-size: 0.67rem;
                                        "
                                    >
                                        {{ $currentUser?->email }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <span class="badge badge-soft-primary">
                                    <i class="bi bi-person-badge me-1"></i>
                                    {{ $userRole }}
                                </span>
                            </div>
                        </div>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    {{-- Logout --}}
                    <li>
                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="dropdown-item text-danger"
                            >
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Keluar dari Sistem
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- =================================================
         PAGE CONTENT
    ================================================== --}}
    <section class="content">

        {{-- Success message --}}
        @if(session('success'))
            <div
                class="alert alert-success alert-dismissible fade show"
                role="alert"
            >
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check-circle-fill mt-1"></i>

                    <div>
                        <div class="fw-semibold">
                            Berhasil
                        </div>

                        <div>
                            {{ session('success') }}
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"
                ></button>
            </div>
        @endif

        {{-- Error message --}}
        @if(session('error'))
            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-circle-fill mt-1"></i>

                    <div>
                        <div class="fw-semibold">
                            Terjadi Kesalahan
                        </div>

                        <div>
                            {{ session('error') }}
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"
                ></button>
            </div>
        @endif

        {{-- Warning message --}}
        @if(session('warning'))
            <div
                class="alert alert-warning alert-dismissible fade show"
                role="alert"
            >
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>

                    <div>
                        <div class="fw-semibold">
                            Perhatian
                        </div>

                        <div>
                            {{ session('warning') }}
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"
                ></button>
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>

                    <div>
                        <div class="fw-semibold mb-2">
                            Periksa kembali data yang dimasukkan
                        </div>

                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Tutup"
                ></button>
            </div>
        @endif

        {{-- Main content from page --}}
        @yield('content')

        {{-- =================================================
             FOOTER
        ================================================== --}}
        <footer class="app-footer">
            <div class="mb-1">
                <strong>
                    SupplyGuard Indonesia
                </strong>

                — Global Supply Chain Risk Intelligence Platform
            </div>

            <div>
                Open-Meteo · World Bank · REST Countries ·
                Frankfurter · GNews · OpenStreetMap
            </div>

            <div class="mt-1">
                © {{ date('Y') }} SupplyGuard Indonesia
            </div>
        </footer>
    </section>
</main>

{{-- =====================================================
     JAVASCRIPT LIBRARY
===================================================== --}}

{{-- Bootstrap --}}
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

{{-- Chart.js --}}
<script
    src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"
></script>

{{-- Leaflet --}}
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>

{{-- =====================================================
     GLOBAL JAVASCRIPT SUPPLYGUARD
===================================================== --}}
<script>
/**
 * Default Chart.js untuk tema dark.
 */
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#8290a6';

    Chart.defaults.borderColor =
        'rgba(148, 163, 184, 0.10)';

    Chart.defaults.font.family =
        'Inter, system-ui, sans-serif';

    Chart.defaults.plugins.legend.labels.usePointStyle =
        true;

    Chart.defaults.plugins.legend.labels.pointStyle =
        'circle';
}

/**
 * Helper global aplikasi.
 */
window.sg = {
    csrf: document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '',

    /**
     * Mengirim request Fetch dan membaca JSON.
     */
    async fetchJson(url, options = {}) {
        const method = (
            options.method || 'GET'
        ).toUpperCase();

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {})
        };

        if (method !== 'GET') {
            headers['X-CSRF-TOKEN'] = this.csrf;
        }

        const response = await fetch(url, {
            ...options,
            method,
            headers
        });

        let payload = {};

        try {
            payload = await response.json();
        } catch (error) {
            payload = {};
        }

        if (response.status === 401) {
            window.location.href =
                @json(route('login'));

            throw new Error(
                'Sesi telah berakhir. Silakan masuk kembali.'
            );
        }

        if (!response.ok) {
            const validationMessages = Object
                .values(payload.errors || {})
                .flat()
                .join(' ');

            const message =
                payload.message ||
                validationMessages ||
                'Permintaan tidak dapat diproses.';

            throw new Error(message);
        }

        return payload;
    },

    /**
     * Menampilkan atau menyembunyikan loading.
     */
    loading(show = true) {
        const loader =
            document.getElementById('globalLoader');

        if (!loader) {
            return;
        }

        loader.classList.toggle('show', show);

        loader.setAttribute(
            'aria-hidden',
            show ? 'false' : 'true'
        );
    },

    /**
     * Menampilkan toast.
     */
    toast(message, success = true) {
        const toastElement =
            document.getElementById('appToast');

        const toastBody =
            document.getElementById('appToastBody');

        if (!toastElement || !toastBody) {
            return;
        }

        toastElement.classList.remove(
            'text-bg-success',
            'text-bg-danger'
        );

        toastElement.classList.add(
            success
                ? 'text-bg-success'
                : 'text-bg-danger'
        );

        toastBody.textContent = message;

        bootstrap.Toast
            .getOrCreateInstance(
                toastElement,
                {
                    delay: 4000
                }
            )
            .show();
    },

    /**
     * Mengamankan teks sebelum dimasukkan ke HTML.
     */
    escape(value) {
        const element =
            document.createElement('div');

        element.textContent =
            value ?? '';

        return element.innerHTML;
    },

    /**
     * Format angka Indonesia.
     */
    number(value, digits = 0) {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '-';
        }

        const numericValue =
            Number(value);

        if (Number.isNaN(numericValue)) {
            return '-';
        }

        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits
        }).format(numericValue);
    },

    /**
     * Format mata uang.
     */
    currency(
        value,
        currency = 'IDR',
        digits = 0
    ) {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '-';
        }

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: digits,
            maximumFractionDigits: digits
        }).format(Number(value));
    },

    /**
     * Class risiko.
     */
    riskClass(level) {
        const normalizedLevel =
            String(level ?? '')
                .toLowerCase();

        if (
            normalizedLevel === 'rendah' ||
            normalizedLevel === 'low'
        ) {
            return 'badge-low';
        }

        if (
            normalizedLevel === 'sedang' ||
            normalizedLevel === 'medium'
        ) {
            return 'badge-medium';
        }

        return 'badge-high';
    },

    /**
     * Label risiko Indonesia.
     */
    riskLabel(level) {
        const normalizedLevel =
            String(level ?? '')
                .toLowerCase();

        if (
            normalizedLevel === 'rendah' ||
            normalizedLevel === 'low'
        ) {
            return 'Rendah';
        }

        if (
            normalizedLevel === 'sedang' ||
            normalizedLevel === 'medium'
        ) {
            return 'Sedang';
        }

        return 'Tinggi';
    }
};

/* =====================================================
   SIDEBAR RESPONSIVE
===================================================== */

const sidebar =
    document.getElementById('sidebar');

const sidebarBackdrop =
    document.getElementById('sidebarBackdrop');

const sidebarToggle =
    document.getElementById('sidebarToggle');

/**
 * Membuka atau menutup sidebar mobile.
 */
function setSidebar(show) {
    if (!sidebar || !sidebarBackdrop) {
        return;
    }

    sidebar.classList.toggle(
        'open',
        show
    );

    sidebarBackdrop.classList.toggle(
        'show',
        show
    );

    sidebarBackdrop.setAttribute(
        'aria-hidden',
        show ? 'false' : 'true'
    );

    sidebarToggle?.setAttribute(
        'aria-expanded',
        show ? 'true' : 'false'
    );

    document.body.style.overflow =
        show ? 'hidden' : '';
}

/**
 * Tombol sidebar mobile.
 */
sidebarToggle?.addEventListener(
    'click',
    function () {
        setSidebar(
            !sidebar?.classList.contains('open')
        );
    }
);

/**
 * Klik backdrop.
 */
sidebarBackdrop?.addEventListener(
    'click',
    function () {
        setSidebar(false);
    }
);

/**
 * Tombol Escape.
 */
document.addEventListener(
    'keydown',
    function (event) {
        if (event.key === 'Escape') {
            setSidebar(false);
        }
    }
);

/**
 * Tutup sidebar setelah menu diklik.
 */
sidebar
    ?.querySelectorAll('.nav-link')
    .forEach(function (link) {
        link.addEventListener(
            'click',
            function () {
                if (window.innerWidth < 992) {
                    setSidebar(false);
                }
            }
        );
    });

/**
 * Tutup sidebar ketika layar kembali desktop.
 */
window.addEventListener(
    'resize',
    function () {
        if (window.innerWidth >= 992) {
            setSidebar(false);
        }
    }
);

/**
 * Hilangkan alert secara otomatis setelah beberapa detik.
 */
window.setTimeout(
    function () {
        document
            .querySelectorAll('.alert.alert-dismissible')
            .forEach(function (alertElement) {
                const instance =
                    bootstrap.Alert.getOrCreateInstance(
                        alertElement
                    );

                instance.close();
            });
    },
    8000
);
</script>

@stack('scripts')

</body>
</html>