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
        content="Masuk ke SupplyGuard Indonesia untuk memantau risiko rantai pasok global."
    >

    <title>
        Masuk — SupplyGuard Indonesia
    </title>

    {{-- Font Inter --}}
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

    {{-- Bootstrap --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    {{-- Bootstrap Icons --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    {{-- CSS utama SupplyGuard --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/supplyguard.css') }}"
    >

    <style>
        .login-security-card {
            padding: 14px;
            margin-top: 25px;

            background:
                linear-gradient(
                    145deg,
                    rgba(15, 25, 44, 0.82),
                    rgba(8, 15, 29, 0.88)
                );

            border:
                1px solid rgba(148, 163, 184, 0.13);

            border-radius: 13px;

            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.025);
        }

        .login-security-icon {
            display: grid;
            flex: 0 0 36px;
            width: 36px;
            height: 36px;
            place-items: center;

            color: #67e8f9;

            background:
                rgba(34, 211, 238, 0.07);

            border:
                1px solid rgba(34, 211, 238, 0.15);

            border-radius: 11px;
        }

        .login-demo-email {
            font-size: 0.75rem;
            font-weight: 650;
            color: #e2e8f0;
        }

        .login-demo-password {
            font-size: 0.69rem;
            color: #8290a6;
        }

        .login-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            margin-bottom: 18px;
            padding: 7px 11px;

            font-size: 0.64rem;
            font-weight: 700;
            color: #a5f3fc;
            text-transform: uppercase;
            letter-spacing: 0.1em;

            background:
                rgba(34, 211, 238, 0.07);

            border:
                1px solid rgba(34, 211, 238, 0.16);

            border-radius: 99px;
        }

        .login-status-dot {
            width: 7px;
            height: 7px;

            background: #22c55e;
            border-radius: 50%;

            box-shadow:
                0 0 12px rgba(34, 197, 94, 0.55);

            animation: loginStatusPulse 1.8s infinite;
        }

        @keyframes loginStatusPulse {
            0% {
                box-shadow:
                    0 0 0 0 rgba(34, 197, 94, 0.35);
            }

            70% {
                box-shadow:
                    0 0 0 7px rgba(34, 197, 94, 0);
            }

            100% {
                box-shadow:
                    0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        .login-visual-badge {
            position: relative;
            z-index: 2;

            display: inline-flex;
            align-items: center;
            gap: 7px;

            width: fit-content;
            padding: 7px 11px;

            font-size: 0.63rem;
            font-weight: 700;
            color: #bfdbfe;
            text-transform: uppercase;
            letter-spacing: 0.11em;

            background:
                rgba(59, 130, 246, 0.08);

            border:
                1px solid rgba(96, 165, 250, 0.16);

            border-radius: 99px;
        }

        .auth-form-wrapper .invalid-feedback {
            margin-top: 6px;
            font-size: 0.69rem;
        }

        .auth-form-wrapper .form-check-label {
            color: #94a3b8;
        }

        .auth-form-wrapper .form-check-label:hover {
            color: #cbd5e1;
        }

        .auth-submit:disabled {
            cursor: not-allowed;
            opacity: 0.72;
            transform: none;
        }

        @media (max-width: 767.98px) {
            .login-security-card {
                margin-top: 20px;
            }
        }
    </style>
</head>

<body class="auth-body">

<main class="auth-shell">

    {{-- =====================================================
         BAGIAN VISUAL
    ====================================================== --}}
    <section class="auth-visual">

        {{-- Brand --}}
        <div class="auth-brand">
            <div class="auth-brand-logo">
                <i class="bi bi-globe-asia-australia"></i>
            </div>

            <div>
                <div class="fw-bold fs-5 text-white">
                    SupplyGuard
                </div>

                <div
                    class="text-muted"
                    style="font-size: 0.7rem;"
                >
                    Global Risk Intelligence
                </div>
            </div>
        </div>

        {{-- Konten visual --}}
        <div class="auth-visual-content">

            <div class="login-visual-badge">
                <i class="bi bi-radar"></i>
                Global Risk Command Center
            </div>

            <h1 class="auth-visual-title mt-4">
                Pantau risiko rantai pasok global dalam satu pusat kendali.
            </h1>

            <p class="auth-visual-description">
                Analisis data ekonomi, cuaca, nilai tukar, berita,
                sentimen, dan pelabuhan untuk membantu memahami
                perubahan risiko rantai pasok secara lebih cepat.
            </p>

            <div class="auth-feature-list">

                <div class="auth-feature">
                    <span class="auth-feature-icon">
                        <i class="bi bi-bar-chart-fill"></i>
                    </span>

                    <span>
                        Dashboard analitik dan visualisasi risiko.
                    </span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">
                        <i class="bi bi-cloud-sun-fill"></i>
                    </span>

                    <span>
                        Pemantauan cuaca dan kondisi global.
                    </span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">
                        <i class="bi bi-currency-exchange"></i>
                    </span>

                    <span>
                        Analisis perubahan nilai tukar mata uang.
                    </span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">
                        <i class="bi bi-shield-fill-check"></i>
                    </span>

                    <span>
                        Sistem penilaian risiko terintegrasi.
                    </span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">
                        <i class="bi bi-newspaper"></i>
                    </span>

                    <span>
                        Intelijen berita dan analisis sentimen.
                    </span>
                </div>
            </div>
        </div>

        {{-- Footer visual --}}
        <div class="auth-visual-footer">
            SupplyGuard Indonesia © {{ date('Y') }}
            <span class="mx-2">•</span>
            Global Supply Chain Risk Intelligence
        </div>
    </section>

    {{-- =====================================================
         BAGIAN FORM
    ====================================================== --}}
    <section class="auth-panel">

        <div class="auth-form-wrapper">

            <div class="login-status">
                <span class="login-status-dot"></span>
                Sistem siap digunakan
            </div>

            <div class="mb-4">
                <span class="badge badge-soft-primary mb-3">
                    <i class="bi bi-shield-lock-fill me-1"></i>
                    Secure Access
                </span>

                <h2 class="auth-form-title">
                    Selamat datang kembali
                </h2>

                <p class="auth-form-description">
                    Masukkan alamat email dan kata sandi untuk
                    mengakses SupplyGuard Indonesia.
                </p>
            </div>

            {{-- Pesan error umum --}}
            @if(session('error'))
                <div
                    class="alert alert-danger"
                    role="alert"
                >
                    <div class="d-flex align-items-start gap-2">
                        <i
                            class="bi bi-exclamation-circle-fill mt-1"
                        ></i>

                        <div>
                            <div class="fw-semibold">
                                Gagal masuk
                            </div>

                            <div>
                                {{ session('error') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Pesan validasi --}}
            @if($errors->any())
                <div
                    class="alert alert-danger"
                    role="alert"
                >
                    <div class="d-flex align-items-start gap-2">
                        <i
                            class="bi bi-exclamation-triangle-fill mt-1"
                        ></i>

                        <div>
                            <div class="fw-semibold">
                                Periksa kembali data login
                            </div>

                            <div>
                                {{ $errors->first() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Pesan sukses --}}
            @if(session('success'))
                <div
                    class="alert alert-success"
                    role="alert"
                >
                    <div class="d-flex align-items-start gap-2">
                        <i
                            class="bi bi-check-circle-fill mt-1"
                        ></i>

                        <div>
                            <div class="fw-semibold">
                                Berhasil
                            </div>

                            <div>
                                {{ session('success') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Form login --}}
            <form
                id="loginForm"
                method="POST"
                action="{{ route('login.store') }}"
                novalidate
            >
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label
                        for="email"
                        class="form-label"
                    >
                        Alamat Email
                    </label>

                    <div class="auth-input-group">
                        <i
                            class="bi bi-envelope
                                   auth-input-icon"
                        ></i>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control auth-input
                                   @error('email') is-invalid @enderror"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            inputmode="email"
                            required
                            autofocus
                            aria-describedby="emailHelp"
                        >
                    </div>

                    @error('email')
                        <div
                            id="emailHelp"
                            class="invalid-feedback d-block"
                        >
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label
                        for="password"
                        class="form-label"
                    >
                        Kata Sandi
                    </label>

                    <div class="auth-input-group">
                        <i
                            class="bi bi-lock
                                   auth-input-icon"
                        ></i>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control auth-input pe-5
                                   @error('password') is-invalid @enderror"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                            aria-describedby="passwordHelp"
                        >

                        <button
                            id="togglePassword"
                            type="button"
                            class="auth-password-toggle"
                            aria-label="Tampilkan kata sandi"
                            aria-pressed="false"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    @error('password')
                        <div
                            id="passwordHelp"
                            class="invalid-feedback d-block"
                        >
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Remember --}}
                <div
                    class="d-flex justify-content-between
                           align-items-center mb-4"
                >
                    <div class="form-check">
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            class="form-check-input"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                        >

                        <label
                            for="remember"
                            class="form-check-label small"
                        >
                            Ingat sesi login saya
                        </label>
                    </div>
                </div>

                {{-- Tombol login --}}
                <button
                    id="loginButton"
                    type="submit"
                    class="btn btn-primary auth-submit w-100"
                >
                    <span
                        id="loginButtonContent"
                        class="d-inline-flex align-items-center"
                    >
                        <i
                            class="bi bi-box-arrow-in-right me-2"
                        ></i>

                        Masuk ke Command Center
                    </span>

                    <span
                        id="loginButtonLoading"
                        class="d-none align-items-center"
                    >
                        <span
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                        ></span>

                        Memverifikasi akun...
                    </span>
                </button>
            </form>

            {{-- Divider --}}
            <div class="auth-divider">
                <span>
                    AKSES PENGGUNA
                </span>
            </div>

            {{-- Register --}}
            <p class="text-center small text-muted mb-0">
                Belum memiliki akun?

                <a
                    href="{{ route('register') }}"
                    class="fw-semibold"
                >
                    Daftar sekarang
                </a>
            </p>

            {{-- Akun demo --}}
            <div class="login-security-card">
                <div class="d-flex align-items-center gap-3">

                    <div class="login-security-icon">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>

                    <div class="flex-grow-1 overflow-hidden">
                        <div
                            class="text-muted"
                            style="font-size: 0.65rem;"
                        >
                            Akun demo administrator
                        </div>

                        <div class="login-demo-email text-truncate">
                            admin@supplyguard.test
                        </div>

                        <div class="login-demo-password">
                            Kata sandi: password
                        </div>
                    </div>

                    <button
                        id="fillDemoAccount"
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        title="Gunakan akun demo"
                    >
                        <i class="bi bi-magic"></i>
                    </button>
                </div>
            </div>

            {{-- Security information --}}
            <div
                class="d-flex align-items-center
                       justify-content-center gap-2 mt-4"
            >
                <i
                    class="bi bi-lock-fill text-success"
                    style="font-size: 0.68rem;"
                ></i>

                <span
                    class="text-muted"
                    style="font-size: 0.64rem;"
                >
                    Akses dilindungi oleh autentikasi SupplyGuard
                </span>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const loginForm =
            document.getElementById('loginForm');

        const loginButton =
            document.getElementById('loginButton');

        const loginButtonContent =
            document.getElementById(
                'loginButtonContent'
            );

        const loginButtonLoading =
            document.getElementById(
                'loginButtonLoading'
            );

        const passwordInput =
            document.getElementById('password');

        const emailInput =
            document.getElementById('email');

        const togglePassword =
            document.getElementById('togglePassword');

        const fillDemoAccount =
            document.getElementById('fillDemoAccount');

        /**
         * Menampilkan atau menyembunyikan password.
         */
        togglePassword?.addEventListener(
            'click',
            function () {
                if (!passwordInput) {
                    return;
                }

                const isPassword =
                    passwordInput.type === 'password';

                passwordInput.type =
                    isPassword
                        ? 'text'
                        : 'password';

                const icon =
                    this.querySelector('i');

                if (icon) {
                    icon.className =
                        isPassword
                            ? 'bi bi-eye-slash'
                            : 'bi bi-eye';
                }

                this.setAttribute(
                    'aria-label',
                    isPassword
                        ? 'Sembunyikan kata sandi'
                        : 'Tampilkan kata sandi'
                );

                this.setAttribute(
                    'aria-pressed',
                    isPassword
                        ? 'true'
                        : 'false'
                );
            }
        );

        /**
         * Mengisi akun demo secara otomatis.
         */
        fillDemoAccount?.addEventListener(
            'click',
            function () {
                if (emailInput) {
                    emailInput.value =
                        'admin@supplyguard.test';
                }

                if (passwordInput) {
                    passwordInput.value =
                        'password';
                }

                emailInput?.focus();
            }
        );

        /**
         * Menampilkan loading saat form dikirim.
         */
        loginForm?.addEventListener(
            'submit',
            function () {
                if (
                    !loginButton ||
                    !loginButtonContent ||
                    !loginButtonLoading
                ) {
                    return;
                }

                loginButton.disabled = true;

                loginButtonContent.classList.add(
                    'd-none'
                );

                loginButtonLoading.classList.remove(
                    'd-none'
                );

                loginButtonLoading.classList.add(
                    'd-inline-flex'
                );
            }
        );
    }
);
</script>

</body>
</html>