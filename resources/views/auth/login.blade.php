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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/supplyguard.css') }}?v={{ time() }}"
    >

    <style>
        :root {
            --sg-bg: #050816;
            --sg-bg-2: #08111f;
            --sg-panel: rgba(15, 23, 42, 0.86);
            --sg-panel-strong: rgba(2, 6, 23, 0.92);
            --sg-border: rgba(148, 163, 184, 0.16);
            --sg-border-strong: rgba(96, 165, 250, 0.28);
            --sg-text: #f8fafc;
            --sg-muted: #94a3b8;
            --sg-soft: #cbd5e1;
            --sg-primary: #38bdf8;
            --sg-primary-2: #2563eb;
            --sg-success: #22c55e;
            --sg-danger: #ef4444;
            --sg-warning: #f59e0b;
            --sg-shadow: 0 24px 80px rgba(0, 0, 0, 0.42);
            --sg-radius: 28px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: var(--sg-bg);
        }

        body.auth-body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--sg-text);
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 34%),
                radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.18), transparent 35%),
                linear-gradient(135deg, #020617 0%, #08111f 48%, #050816 100%);
            overflow-x: hidden;
        }

        body.auth-body::before {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            content: "";
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.045) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(0,0,0,0.2));
        }

        a {
            color: #7dd3fc;
            text-decoration: none;
        }

        a:hover {
            color: #bae6fd;
        }

        .auth-shell {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(430px, 0.92fr);
            min-height: 100vh;
            padding: 22px;
            gap: 22px;
        }

        .auth-visual,
        .auth-panel {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--sg-border);
            box-shadow: var(--sg-shadow);
            backdrop-filter: blur(18px);
        }

        .auth-visual {
            display: flex;
            min-height: calc(100vh - 44px);
            flex-direction: column;
            justify-content: space-between;
            padding: 38px;
            background:
                linear-gradient(145deg, rgba(15, 23, 42, 0.88), rgba(2, 6, 23, 0.9)),
                radial-gradient(circle at 78% 20%, rgba(56, 189, 248, 0.22), transparent 30%);
            border-radius: var(--sg-radius);
        }

        .auth-visual::before {
            position: absolute;
            top: -120px;
            right: -120px;
            width: 380px;
            height: 380px;
            content: "";
            background: rgba(14, 165, 233, 0.16);
            border-radius: 999px;
            filter: blur(18px);
        }

        .auth-visual::after {
            position: absolute;
            right: 40px;
            bottom: 42px;
            width: 300px;
            height: 300px;
            content: "";
            border: 1px solid rgba(125, 211, 252, 0.12);
            border-radius: 999px;
            box-shadow:
                0 0 0 48px rgba(125, 211, 252, 0.025),
                0 0 0 96px rgba(125, 211, 252, 0.018);
        }

        .auth-brand {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .auth-brand-logo {
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            color: #e0f2fe;
            font-size: 1.35rem;
            font-weight: 900;
            background:
                linear-gradient(135deg, rgba(56, 189, 248, 0.28), rgba(37, 99, 235, 0.26));
            border: 1px solid rgba(125, 211, 252, 0.32);
            border-radius: 16px;
            box-shadow: 0 16px 34px rgba(14, 165, 233, 0.18);
        }

        .auth-brand-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .auth-brand-subtitle {
            margin-top: 2px;
            color: var(--sg-muted);
            font-size: 0.74rem;
        }

        .auth-visual-content {
            position: relative;
            z-index: 2;
            max-width: 720px;
            padding: 70px 0;
        }

        .login-visual-badge,
        .login-status,
        .badge-soft-primary {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            border-radius: 999px;
        }

        .login-visual-badge {
            gap: 8px;
            padding: 8px 13px;
            color: #bfdbfe;
            font-size: 0.64rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            background: rgba(37, 99, 235, 0.13);
            border: 1px solid rgba(96, 165, 250, 0.22);
        }

        .auth-visual-title {
            margin: 26px 0 16px;
            max-width: 740px;
            color: #ffffff;
            font-size: clamp(2.4rem, 5vw, 4.7rem);
            font-weight: 900;
            line-height: 0.98;
            letter-spacing: -0.07em;
        }

        .auth-visual-description {
            max-width: 690px;
            margin: 0 0 30px;
            color: #cbd5e1;
            font-size: 1.02rem;
            line-height: 1.8;
        }

        .auth-feature-list {
            display: grid;
            max-width: 650px;
            gap: 13px;
        }

        .auth-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2e8f0;
            font-size: 0.94rem;
        }

        .auth-feature-icon {
            display: grid;
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            place-items: center;
            color: #a5f3fc;
            background: rgba(34, 211, 238, 0.08);
            border: 1px solid rgba(34, 211, 238, 0.16);
            border-radius: 12px;
        }

        .auth-visual-footer {
            position: relative;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-size: 0.78rem;
        }

        .auth-panel {
            display: grid;
            min-height: calc(100vh - 44px);
            place-items: center;
            padding: 34px;
            background:
                linear-gradient(160deg, rgba(15, 23, 42, 0.9), rgba(2, 6, 23, 0.88));
            border-radius: var(--sg-radius);
        }

        .auth-form-wrapper {
            width: min(100%, 460px);
            padding: 34px;
            background:
                linear-gradient(145deg, rgba(15, 23, 42, 0.84), rgba(2, 6, 23, 0.74));
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: 24px;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.035),
                0 24px 58px rgba(0, 0, 0, 0.28);
        }

        .login-status {
            gap: 8px;
            margin-bottom: 22px;
            padding: 8px 12px;
            color: #a5f3fc;
            font-size: 0.66rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.11em;
            background: rgba(34, 211, 238, 0.08);
            border: 1px solid rgba(34, 211, 238, 0.18);
        }

        .login-status-dot {
            width: 8px;
            height: 8px;
            background: var(--sg-success);
            border-radius: 999px;
            box-shadow: 0 0 13px rgba(34, 197, 94, 0.7);
            animation: loginStatusPulse 1.8s infinite;
        }

        @keyframes loginStatusPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.35);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        .badge-soft-primary {
            gap: 7px;
            margin-bottom: 15px;
            padding: 8px 11px;
            color: #bfdbfe;
            font-size: 0.66rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: rgba(37, 99, 235, 0.12);
            border: 1px solid rgba(96, 165, 250, 0.22);
        }

        .auth-form-title {
            margin: 0 0 10px;
            color: #ffffff;
            font-size: 2rem;
            font-weight: 850;
            line-height: 1.08;
            letter-spacing: -0.045em;
        }

        .auth-form-description {
            margin: 0;
            color: var(--sg-muted);
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .alert {
            margin-bottom: 18px;
            padding: 14px;
            color: #ffffff;
            border-radius: 15px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.22);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.22);
        }

        .form-label {
            display: inline-block;
            margin-bottom: 9px;
            color: #dbeafe;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .auth-input-group {
            position: relative;
        }

        .auth-input-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            z-index: 3;
            color: #7dd3fc;
            transform: translateY(-50%);
        }

        .auth-input {
            width: 100%;
            min-height: 52px;
            padding: 14px 48px 14px 45px;
            color: #f8fafc;
            font: inherit;
            background: rgba(2, 6, 23, 0.52);
            border: 1px solid rgba(148, 163, 184, 0.17);
            border-radius: 16px;
            outline: none;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .auth-input::placeholder {
            color: #64748b;
        }

        .auth-input:focus {
            background: rgba(2, 6, 23, 0.72);
            border-color: rgba(56, 189, 248, 0.55);
            box-shadow:
                0 0 0 4px rgba(14, 165, 233, 0.13),
                0 16px 34px rgba(0, 0, 0, 0.18);
        }

        .auth-input.is-invalid {
            border-color: rgba(239, 68, 68, 0.62);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.11);
        }

        .auth-password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            z-index: 4;
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            color: #94a3b8;
            cursor: pointer;
            background: transparent;
            border: 0;
            border-radius: 12px;
            transform: translateY(-50%);
        }

        .auth-password-toggle:hover {
            color: #e2e8f0;
            background: rgba(148, 163, 184, 0.08);
        }

        .d-flex {
            display: flex;
        }

        .align-items-center {
            align-items: center;
        }

        .align-items-start {
            align-items: flex-start;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .justify-content-center {
            justify-content: center;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 0.84rem;
        }

        .text-muted {
            color: var(--sg-muted) !important;
        }

        .text-white {
            color: #ffffff !important;
        }

        .text-success {
            color: var(--sg-success) !important;
        }

        .fw-bold {
            font-weight: 800;
        }

        .fw-semibold {
            font-weight: 700;
        }

        .fs-5 {
            font-size: 1.25rem;
        }

        .w-100 {
            width: 100%;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 24px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            accent-color: #38bdf8;
        }

        .form-check-label {
            cursor: pointer;
            color: #94a3b8;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 16px;
            color: #ffffff;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            border-radius: 14px;
            transition:
                transform 0.18s ease,
                box-shadow 0.18s ease,
                border-color 0.18s ease,
                background 0.18s ease;
        }

        .btn-primary,
        .auth-submit {
            min-height: 54px;
            background:
                linear-gradient(135deg, #0ea5e9, #2563eb);
            border: 1px solid rgba(125, 211, 252, 0.34);
            box-shadow:
                0 18px 34px rgba(37, 99, 235, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.16);
        }

        .btn-primary:hover,
        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow:
                0 24px 42px rgba(37, 99, 235, 0.34),
                inset 0 1px 0 rgba(255, 255, 255, 0.16);
        }

        .auth-submit:disabled {
            cursor: not-allowed;
            opacity: 0.78;
            transform: none;
        }

        .btn-sm {
            min-height: 34px;
            padding: 7px 10px;
            font-size: 0.82rem;
            border-radius: 11px;
        }

        .btn-outline-primary {
            color: #7dd3fc;
            background: rgba(14, 165, 233, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.26);
        }

        .btn-outline-primary:hover {
            color: #ffffff;
            background: rgba(14, 165, 233, 0.18);
        }

        .d-none {
            display: none !important;
        }

        .d-inline-flex {
            display: inline-flex !important;
        }

        .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            vertical-align: -0.125em;
            border: 0.15em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinnerBorder 0.75s linear infinite;
        }

        .spinner-border-sm {
            width: 0.9rem;
            height: 0.9rem;
            border-width: 0.13em;
        }

        @keyframes spinnerBorder {
            to {
                transform: rotate(360deg);
            }
        }

        .invalid-feedback {
            display: block;
            margin-top: 7px;
            color: #fecaca;
            font-size: 0.74rem;
        }

        .auth-divider {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 24px 0;
            color: #64748b;
            font-size: 0.66rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.13em;
        }

        .auth-divider::before,
        .auth-divider::after {
            flex: 1;
            height: 1px;
            content: "";
            background: rgba(148, 163, 184, 0.14);
        }

        .auth-divider span {
            padding: 0 12px;
        }

        .login-security-card {
            padding: 15px;
            margin-top: 24px;
            background:
                linear-gradient(145deg, rgba(15, 25, 44, 0.82), rgba(8, 15, 29, 0.88));
            border: 1px solid rgba(148, 163, 184, 0.13);
            border-radius: 17px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.025);
        }

        .login-security-icon {
            display: grid;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            place-items: center;
            color: #67e8f9;
            background: rgba(34, 211, 238, 0.08);
            border: 1px solid rgba(34, 211, 238, 0.15);
            border-radius: 13px;
        }

        .login-demo-email {
            color: #e2e8f0;
            font-size: 0.78rem;
            font-weight: 750;
        }

        .login-demo-password {
            margin-top: 2px;
            color: #8290a6;
            font-size: 0.71rem;
        }

        .me-1 {
            margin-right: 0.25rem;
        }

        .me-2 {
            margin-right: 0.5rem;
        }

        .mt-1 {
            margin-top: 0.25rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .mx-2 {
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }

        @media (max-width: 1100px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .auth-visual {
                min-height: auto;
            }

            .auth-panel {
                min-height: auto;
            }

            .auth-visual-content {
                padding: 54px 0;
            }
        }

        @media (max-width: 767.98px) {
            .auth-shell {
                padding: 12px;
                gap: 12px;
            }

            .auth-visual,
            .auth-panel {
                border-radius: 22px;
            }

            .auth-visual {
                padding: 24px;
            }

            .auth-panel {
                padding: 18px;
            }

            .auth-form-wrapper {
                padding: 24px 20px;
                border-radius: 20px;
            }

            .auth-visual-title {
                font-size: 2.25rem;
                letter-spacing: -0.05em;
            }

            .auth-form-title {
                font-size: 1.72rem;
            }

            .auth-visual-description {
                font-size: 0.94rem;
            }
        }
    </style>
</head>

<body class="auth-body">

<main class="auth-shell">

    <section class="auth-visual">
        <div class="auth-brand">
            <div class="auth-brand-logo">
                SG
            </div>

            <div>
                <h1 class="auth-brand-title">
                    SupplyGuard
                </h1>

                <div class="auth-brand-subtitle">
                    Global Risk Intelligence
                </div>
            </div>
        </div>

        <div class="auth-visual-content">
            <div class="login-visual-badge">
                <span>◉</span>
                Global Risk Command Center
            </div>

            <h2 class="auth-visual-title">
                Pantau risiko rantai pasok global dalam satu pusat kendali.
            </h2>

            <p class="auth-visual-description">
                Analisis data ekonomi, cuaca, nilai tukar, berita,
                sentimen, dan pelabuhan untuk membantu memahami
                perubahan risiko rantai pasok secara lebih cepat.
            </p>

            <div class="auth-feature-list">
                <div class="auth-feature">
                    <span class="auth-feature-icon">▣</span>
                    <span>Dashboard analitik dan visualisasi risiko.</span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">☁</span>
                    <span>Pemantauan cuaca dan kondisi global.</span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">↔</span>
                    <span>Analisis perubahan nilai tukar mata uang.</span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">✓</span>
                    <span>Sistem penilaian risiko terintegrasi.</span>
                </div>

                <div class="auth-feature">
                    <span class="auth-feature-icon">▤</span>
                    <span>Intelijen berita dan analisis sentimen.</span>
                </div>
            </div>
        </div>

        <div class="auth-visual-footer">
            <span>SupplyGuard Indonesia © {{ date('Y') }}</span>
            <span class="mx-2">•</span>
            <span>Global Supply Chain Risk Intelligence</span>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-form-wrapper">
            <div class="login-status">
                <span class="login-status-dot"></span>
                Sistem siap digunakan
            </div>

            <div class="mb-4">
                <span class="badge-soft-primary">
                    🔒 Secure Access
                </span>

                <h2 class="auth-form-title">
                    Selamat datang kembali
                </h2>

                <p class="auth-form-description">
                    Masukkan alamat email dan kata sandi untuk
                    mengakses SupplyGuard Indonesia.
                </p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <span class="mt-1">!</span>

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

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <span class="mt-1">!</span>

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

            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <span class="mt-1">✓</span>

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

            <form
                id="loginForm"
                method="POST"
                action="{{ route('login.store') }}"
                novalidate
            >
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">
                        Alamat Email
                    </label>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">@</span>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="auth-input @error('email') is-invalid @enderror"
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
                            class="invalid-feedback"
                        >
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        Kata Sandi
                    </label>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">●</span>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="auth-input @error('password') is-invalid @enderror"
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
                            👁
                        </button>
                    </div>

                    @error('password')
                        <div
                            id="passwordHelp"
                            class="invalid-feedback"
                        >
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
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

                <button
                    id="loginButton"
                    type="submit"
                    class="btn btn-primary auth-submit w-100"
                >
                    <span
                        id="loginButtonContent"
                        class="d-inline-flex align-items-center"
                    >
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

            <div class="auth-divider">
                <span>AKSES PENGGUNA</span>
            </div>

            <p class="text-center small text-muted mb-0">
                Belum memiliki akun?

                <a
                    href="{{ route('register') }}"
                    class="fw-semibold"
                >
                    Daftar sekarang
                </a>
            </p>

            <div class="login-security-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="login-security-icon">
                        ID
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
                        Isi
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-center gap-2 mt-4">
                <span
                    class="text-success"
                    style="font-size: 0.68rem;"
                >
                    ●
                </span>

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
document.addEventListener('DOMContentLoaded', function () {
    const loginForm =
        document.getElementById('loginForm');

    const loginButton =
        document.getElementById('loginButton');

    const loginButtonContent =
        document.getElementById('loginButtonContent');

    const loginButtonLoading =
        document.getElementById('loginButtonLoading');

    const passwordInput =
        document.getElementById('password');

    const emailInput =
        document.getElementById('email');

    const togglePassword =
        document.getElementById('togglePassword');

    const fillDemoAccount =
        document.getElementById('fillDemoAccount');

    togglePassword?.addEventListener('click', function () {
        if (!passwordInput) {
            return;
        }

        const isPassword =
            passwordInput.type === 'password';

        passwordInput.type =
            isPassword
                ? 'text'
                : 'password';

        this.textContent =
            isPassword
                ? '🙈'
                : '👁';

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
    });

    fillDemoAccount?.addEventListener('click', function () {
        if (emailInput) {
            emailInput.value =
                'admin@supplyguard.test';
        }

        if (passwordInput) {
            passwordInput.value =
                'password';
        }

        passwordInput?.focus();
    });

    loginForm?.addEventListener('submit', function () {
        if (
            !loginButton ||
            !loginButtonContent ||
            !loginButtonLoading
        ) {
            return;
        }

        loginButton.disabled = true;

        loginButtonContent.classList.add('d-none');

        loginButtonLoading.classList.remove('d-none');
        loginButtonLoading.classList.add('d-inline-flex');
    });
});
</script>

</body>
</html>