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
        content="Daftar akun SupplyGuard Indonesia untuk memantau risiko rantai pasok global."
    >

    <title>
        Daftar — SupplyGuard Indonesia
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

    {{-- CSS SupplyGuard --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/supplyguard.css') }}"
    >

    <style>
        .register-status {
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

            background: rgba(34, 211, 238, 0.07);
            border: 1px solid rgba(34, 211, 238, 0.16);
            border-radius: 999px;
        }

        .register-status-dot {
            width: 7px;
            height: 7px;

            background: #22c55e;
            border-radius: 50%;

            box-shadow:
                0 0 12px rgba(34, 197, 94, 0.55);

            animation: registerStatusPulse 1.8s infinite;
        }

        @keyframes registerStatusPulse {
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

        .register-visual-badge {
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

            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 999px;
        }

        .register-benefit-card {
            display: flex;
            align-items: center;
            gap: 12px;

            padding: 12px 13px;

            background:
                linear-gradient(
                    145deg,
                    rgba(15, 25, 44, 0.68),
                    rgba(8, 15, 29, 0.76)
                );

            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 13px;
        }

        .register-benefit-icon {
            display: grid;
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            place-items: center;

            color: #67e8f9;

            background: rgba(34, 211, 238, 0.07);
            border: 1px solid rgba(34, 211, 238, 0.14);
            border-radius: 10px;
        }

        .password-strength {
            margin-top: 9px;
        }

        .password-strength-track {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;

            width: 100%;
        }

        .password-strength-segment {
            height: 4px;

            background: rgba(148, 163, 184, 0.13);
            border-radius: 999px;

            transition:
                background 0.2s ease,
                box-shadow 0.2s ease;
        }

        .password-strength-segment.active.weak {
            background: #ef4444;
            box-shadow:
                0 0 8px rgba(239, 68, 68, 0.3);
        }

        .password-strength-segment.active.medium {
            background: #f59e0b;
            box-shadow:
                0 0 8px rgba(245, 158, 11, 0.3);
        }

        .password-strength-segment.active.strong {
            background: #22c55e;
            box-shadow:
                0 0 8px rgba(34, 197, 94, 0.3);
        }

        .password-strength-info {
            display: flex;
            justify-content: space-between;
            gap: 10px;

            margin-top: 6px;

            font-size: 0.64rem;
            color: #64748b;
        }

        .password-strength-label {
            font-weight: 650;
        }

        .password-requirements {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px 12px;

            margin-top: 10px;
        }

        .password-requirement {
            display: flex;
            align-items: center;
            gap: 6px;

            font-size: 0.62rem;
            color: #64748b;

            transition: color 0.2s ease;
        }

        .password-requirement i {
            font-size: 0.66rem;
        }

        .password-requirement.valid {
            color: #86efac;
        }

        .password-match-message {
            display: none;
            align-items: center;
            gap: 6px;

            margin-top: 7px;

            font-size: 0.66rem;
        }

        .password-match-message.show {
            display: flex;
        }

        .password-match-message.valid {
            color: #86efac;
        }

        .password-match-message.invalid {
            color: #fca5a5;
        }

        .register-security-card {
            display: flex;
            align-items: flex-start;
            gap: 11px;

            margin-top: 23px;
            padding: 13px;

            background:
                linear-gradient(
                    145deg,
                    rgba(15, 25, 44, 0.75),
                    rgba(8, 15, 29, 0.82)
                );

            border: 1px solid rgba(148, 163, 184, 0.11);
            border-radius: 13px;
        }

        .register-security-card i {
            margin-top: 2px;
            color: #22c55e;
        }

        .register-security-title {
            font-size: 0.7rem;
            font-weight: 650;
            color: #cbd5e1;
        }

        .register-security-description {
            margin-top: 3px;

            font-size: 0.63rem;
            line-height: 1.55;
            color: #64748b;
        }

        .auth-form-wrapper .invalid-feedback {
            margin-top: 6px;
            font-size: 0.69rem;
        }

        .auth-submit:disabled {
            cursor: not-allowed;
            opacity: 0.72;
            transform: none;
        }

        @media (max-width: 767.98px) {
            .password-requirements {
                grid-template-columns: 1fr;
            }

            .register-security-card {
                margin-top: 19px;
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

            <div class="register-visual-badge">
                <i class="bi bi-person-bounding-box"></i>
                Intelligence Access Registration
            </div>

            <h1 class="auth-visual-title mt-4">
                Mulai memantau risiko global dengan lebih terstruktur.
            </h1>

            <p class="auth-visual-description">
                Buat akun SupplyGuard untuk menyimpan negara favorit,
                membandingkan indikator, memantau perubahan ekonomi,
                dan memahami risiko rantai pasok dalam satu platform.
            </p>

            <div class="auth-feature-list">

                <div class="register-benefit-card">
                    <span class="register-benefit-icon">
                        <i class="bi bi-star-fill"></i>
                    </span>

                    <span>
                        Simpan negara penting dalam daftar pemantauan.
                    </span>
                </div>

                <div class="register-benefit-card">
                    <span class="register-benefit-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </span>

                    <span>
                        Bandingkan indikator dan risiko antarnegara.
                    </span>
                </div>

                <div class="register-benefit-card">
                    <span class="register-benefit-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </span>

                    <span>
                        Pantau perkembangan ekonomi dan nilai tukar.
                    </span>
                </div>

                <div class="register-benefit-card">
                    <span class="register-benefit-icon">
                        <i class="bi bi-shield-fill-check"></i>
                    </span>

                    <span>
                        Akses hasil perhitungan risiko terintegrasi.
                    </span>
                </div>
            </div>
        </div>

        {{-- Footer visual --}}
        <div class="auth-visual-footer">
            SupplyGuard Indonesia © {{ date('Y') }}

            <span class="mx-2">
                •
            </span>

            Global Supply Chain Risk Intelligence
        </div>
    </section>

    {{-- =====================================================
         BAGIAN FORM
    ====================================================== --}}
    <section class="auth-panel">

        <div class="auth-form-wrapper">

            <div class="register-status">
                <span class="register-status-dot"></span>
                Registration System Online
            </div>

            <div class="mb-4">
                <span class="badge badge-soft-primary mb-3">
                    <i class="bi bi-person-plus-fill me-1"></i>
                    Pendaftaran Pengguna
                </span>

                <h2 class="auth-form-title">
                    Buat akun baru
                </h2>

                <p class="auth-form-description">
                    Lengkapi informasi berikut untuk mulai menggunakan
                    SupplyGuard Indonesia.
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
                                Pendaftaran gagal
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
                            <div class="fw-semibold mb-2">
                                Periksa kembali data pendaftaran
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

            {{-- Form pendaftaran --}}
            <form
                id="registerForm"
                method="POST"
                action="{{ route('register.store') }}"
            >
                @csrf

                {{-- Nama --}}
                <div class="mb-3">
                    <label
                        for="name"
                        class="form-label"
                    >
                        Nama Lengkap
                    </label>

                    <div class="auth-input-group">
                        <i
                            class="bi bi-person
                                   auth-input-icon"
                        ></i>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control auth-input
                                   @error('name') is-invalid @enderror"
                            placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            minlength="3"
                            maxlength="100"
                            required
                            autofocus
                            aria-describedby="nameError"
                        >
                    </div>

                    @error('name')
                        <div
                            id="nameError"
                            class="invalid-feedback d-block"
                        >
                            {{ $message }}
                        </div>
                    @enderror
                </div>

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
                            maxlength="150"
                            required
                            aria-describedby="emailError"
                        >
                    </div>

                    @error('email')
                        <div
                            id="emailError"
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
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            aria-describedby="passwordError passwordStrength"
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
                            id="passwordError"
                            class="invalid-feedback d-block"
                        >
                            {{ $message }}
                        </div>
                    @enderror

                    <div
                        id="passwordStrength"
                        class="password-strength"
                    >
                        <div class="password-strength-track">
                            <span class="password-strength-segment"></span>
                            <span class="password-strength-segment"></span>
                            <span class="password-strength-segment"></span>
                            <span class="password-strength-segment"></span>
                        </div>

                        <div class="password-strength-info">
                            <span>
                                Kekuatan kata sandi
                            </span>

                            <span
                                id="passwordStrengthLabel"
                                class="password-strength-label"
                            >
                                Belum diisi
                            </span>
                        </div>

                        <div class="password-requirements">

                            <div
                                id="requirementLength"
                                class="password-requirement"
                            >
                                <i class="bi bi-circle"></i>
                                Minimal 8 karakter
                            </div>

                            <div
                                id="requirementUppercase"
                                class="password-requirement"
                            >
                                <i class="bi bi-circle"></i>
                                Memiliki huruf besar
                            </div>

                            <div
                                id="requirementNumber"
                                class="password-requirement"
                            >
                                <i class="bi bi-circle"></i>
                                Memiliki angka
                            </div>

                            <div
                                id="requirementSymbol"
                                class="password-requirement"
                            >
                                <i class="bi bi-circle"></i>
                                Memiliki simbol
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Konfirmasi password --}}
                <div class="mb-4">
                    <label
                        for="password_confirmation"
                        class="form-label"
                    >
                        Konfirmasi Kata Sandi
                    </label>

                    <div class="auth-input-group">
                        <i
                            class="bi bi-shield-lock
                                   auth-input-icon"
                        ></i>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="form-control auth-input pe-5"
                            placeholder="Ulangi kata sandi"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            aria-describedby="passwordMatchMessage"
                        >

                        <button
                            id="toggleConfirmation"
                            type="button"
                            class="auth-password-toggle"
                            aria-label="Tampilkan konfirmasi kata sandi"
                            aria-pressed="false"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <div
                        id="passwordMatchMessage"
                        class="password-match-message"
                    >
                        <i class="bi bi-circle"></i>

                        <span>
                            Konfirmasi kata sandi belum diisi.
                        </span>
                    </div>
                </div>

                {{-- Tombol daftar --}}
                <button
                    id="registerButton"
                    type="submit"
                    class="btn btn-primary auth-submit w-100"
                >
                    <span
                        id="registerButtonContent"
                        class="d-inline-flex align-items-center"
                    >
                        <i
                            class="bi bi-person-check-fill me-2"
                        ></i>

                        Buat Akun SupplyGuard
                    </span>

                    <span
                        id="registerButtonLoading"
                        class="d-none align-items-center"
                    >
                        <span
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true"
                        ></span>

                        Membuat akun...
                    </span>
                </button>
            </form>

            {{-- Divider --}}
            <div class="auth-divider">
                <span>
                    SUDAH TERDAFTAR
                </span>
            </div>

            {{-- Login --}}
            <p class="text-center small text-muted mb-0">
                Sudah memiliki akun?

                <a
                    href="{{ route('login') }}"
                    class="fw-semibold"
                >
                    Masuk sekarang
                </a>
            </p>

            {{-- Informasi keamanan --}}
            <div class="register-security-card">
                <i class="bi bi-shield-lock-fill"></i>

                <div>
                    <div class="register-security-title">
                        Data akun dilindungi
                    </div>

                    <div class="register-security-description">
                        Kata sandi akan disimpan secara aman menggunakan
                        sistem hash Laravel dan tidak ditampilkan dalam
                        bentuk teks asli.
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const registerForm =
            document.getElementById('registerForm');

        const registerButton =
            document.getElementById('registerButton');

        const registerButtonContent =
            document.getElementById(
                'registerButtonContent'
            );

        const registerButtonLoading =
            document.getElementById(
                'registerButtonLoading'
            );

        const passwordInput =
            document.getElementById('password');

        const confirmationInput =
            document.getElementById(
                'password_confirmation'
            );

        const strengthSegments =
            document.querySelectorAll(
                '.password-strength-segment'
            );

        const strengthLabel =
            document.getElementById(
                'passwordStrengthLabel'
            );

        const matchMessage =
            document.getElementById(
                'passwordMatchMessage'
            );

        /**
         * Mengatur tombol tampil atau sembunyikan password.
         */
        function setupPasswordToggle(
            buttonId,
            inputId
        ) {
            const button =
                document.getElementById(buttonId);

            const input =
                document.getElementById(inputId);

            button?.addEventListener(
                'click',
                function () {
                    if (!input) {
                        return;
                    }

                    const isPassword =
                        input.type === 'password';

                    input.type =
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
        }

        /**
         * Mengubah status persyaratan password.
         */
        function updateRequirement(
            elementId,
            isValid
        ) {
            const element =
                document.getElementById(elementId);

            if (!element) {
                return;
            }

            element.classList.toggle(
                'valid',
                isValid
            );

            const icon =
                element.querySelector('i');

            if (icon) {
                icon.className =
                    isValid
                        ? 'bi bi-check-circle-fill'
                        : 'bi bi-circle';
            }
        }

        /**
         * Menghitung kekuatan password.
         */
        function updatePasswordStrength() {
            const password =
                passwordInput?.value ?? '';

            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                symbol: /[^A-Za-z0-9]/.test(password)
            };

            updateRequirement(
                'requirementLength',
                requirements.length
            );

            updateRequirement(
                'requirementUppercase',
                requirements.uppercase
            );

            updateRequirement(
                'requirementNumber',
                requirements.number
            );

            updateRequirement(
                'requirementSymbol',
                requirements.symbol
            );

            const score =
                Object.values(requirements)
                    .filter(Boolean)
                    .length;

            strengthSegments.forEach(
                function (segment, index) {
                    segment.className =
                        'password-strength-segment';

                    if (
                        password &&
                        index < score
                    ) {
                        segment.classList.add(
                            'active'
                        );

                        if (score <= 1) {
                            segment.classList.add(
                                'weak'
                            );
                        } else if (score <= 3) {
                            segment.classList.add(
                                'medium'
                            );
                        } else {
                            segment.classList.add(
                                'strong'
                            );
                        }
                    }
                }
            );

            if (!strengthLabel) {
                return;
            }

            if (!password) {
                strengthLabel.textContent =
                    'Belum diisi';

                strengthLabel.style.color =
                    '#64748b';

                return;
            }

            if (score <= 1) {
                strengthLabel.textContent =
                    'Lemah';

                strengthLabel.style.color =
                    '#fca5a5';

                return;
            }

            if (score <= 3) {
                strengthLabel.textContent =
                    'Sedang';

                strengthLabel.style.color =
                    '#fcd34d';

                return;
            }

            strengthLabel.textContent =
                'Kuat';

            strengthLabel.style.color =
                '#86efac';
        }

        /**
         * Memeriksa kesamaan password.
         */
        function updatePasswordMatch() {
            if (
                !confirmationInput ||
                !matchMessage
            ) {
                return;
            }

            const confirmation =
                confirmationInput.value;

            const password =
                passwordInput?.value ?? '';

            const icon =
                matchMessage.querySelector('i');

            const text =
                matchMessage.querySelector('span');

            matchMessage.classList.remove(
                'valid',
                'invalid'
            );

            if (!confirmation) {
                matchMessage.classList.remove(
                    'show'
                );

                return;
            }

            matchMessage.classList.add('show');

            const isMatch =
                confirmation === password;

            matchMessage.classList.add(
                isMatch
                    ? 'valid'
                    : 'invalid'
            );

            if (icon) {
                icon.className =
                    isMatch
                        ? 'bi bi-check-circle-fill'
                        : 'bi bi-x-circle-fill';
            }

            if (text) {
                text.textContent =
                    isMatch
                        ? 'Konfirmasi kata sandi sesuai.'
                        : 'Konfirmasi kata sandi belum sesuai.';
            }

            confirmationInput.setCustomValidity(
                isMatch
                    ? ''
                    : 'Konfirmasi kata sandi tidak sesuai.'
            );
        }

        setupPasswordToggle(
            'togglePassword',
            'password'
        );

        setupPasswordToggle(
            'toggleConfirmation',
            'password_confirmation'
        );

        passwordInput?.addEventListener(
            'input',
            function () {
                updatePasswordStrength();
                updatePasswordMatch();
            }
        );

        confirmationInput?.addEventListener(
            'input',
            updatePasswordMatch
        );

        /**
         * Menampilkan loading ketika form valid dan dikirim.
         */
        registerForm?.addEventListener(
            'submit',
            function (event) {
                updatePasswordMatch();

                if (!registerForm.checkValidity()) {
                    event.preventDefault();
                    registerForm.reportValidity();

                    return;
                }

                if (
                    !registerButton ||
                    !registerButtonContent ||
                    !registerButtonLoading
                ) {
                    return;
                }

                registerButton.disabled = true;

                registerButtonContent.classList.add(
                    'd-none'
                );

                registerButtonLoading.classList.remove(
                    'd-none'
                );

                registerButtonLoading.classList.add(
                    'd-inline-flex'
                );
            }
        );

        updatePasswordStrength();
        updatePasswordMatch();
    }
);
</script>

</body>
</html>