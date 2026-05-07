@extends('layouts.master')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --gold:        #F5C200;
        --gold-dark:   #C99A00;
        --gold-light:  #FFE566;
        --blue:        #1035A0;
        --blue-mid:    #1A4BC4;
        --blue-light:  #3A6EE8;
        --white:       #FFFFFF;
        --off-white:   #F8F7F2;
        --dark:        #0D1B3E;
        --text-muted:  #6B7A99;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Outfit', sans-serif;
        background: var(--dark);
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ── Animated background ── */
    .opgi-bg {
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 20% 10%, rgba(245,194,0,.18) 0%, transparent 60%),
            radial-gradient(ellipse 60% 80% at 85% 90%, rgba(16,53,160,.45) 0%, transparent 60%),
            linear-gradient(160deg, #0a1428 0%, #0D1B3E 50%, #091020 100%);
        z-index: 0;
    }

    /* Floating geometric rings inspired by the circular logo */
    .opgi-bg::before {
        content: '';
        position: absolute;
        width: 600px; height: 600px;
        border-radius: 50%;
        border: 2px solid rgba(245,194,0,.08);
        top: -150px; left: -150px;
        animation: slowSpin 30s linear infinite;
    }
    .opgi-bg::after {
        content: '';
        position: absolute;
        width: 400px; height: 400px;
        border-radius: 50%;
        border: 1.5px solid rgba(26,75,196,.15);
        bottom: -100px; right: -100px;
        animation: slowSpin 20s linear infinite reverse;
    }

    @keyframes slowSpin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    /* Gold dots pattern */
    .dots-layer {
        position: fixed;
        inset: 0;
        background-image: radial-gradient(circle, rgba(245,194,0,.06) 1px, transparent 1px);
        background-size: 32px 32px;
        z-index: 0;
    }

    /* ── Main wrapper ── */
    .login-wrapper {
        position: relative;
        z-index: 1;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    /* ── Card ── */
    .login-card {
        display: flex;
        width: 100%;
        max-width: 960px;
        min-height: 580px;
        border-radius: 24px;
        overflow: hidden;
        box-shadow:
            0 0 0 1px rgba(245,194,0,.12),
            0 40px 80px rgba(0,0,0,.5),
            0 0 80px rgba(245,194,0,.06);
        animation: cardIn .7s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(28px) scale(.97); }
        to   { opacity: 1; transform: none; }
    }

    /* ── LEFT PANEL ── */
    .panel-left {
        flex: 0 0 42%;
        position: relative;
        background: linear-gradient(160deg, var(--gold) 0%, #E0A800 55%, #C48A00 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 36px;
        overflow: hidden;
    }

    /* Decorative circle ring inside left panel */
    .panel-left::before {
        content: '';
        position: absolute;
        width: 420px; height: 420px;
        border-radius: 50%;
        border: 40px solid rgba(0,0,0,.06);
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
    }
    .panel-left::after {
        content: '';
        position: absolute;
        width: 280px; height: 280px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,.25);
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Diagonal stripe texture */
    .panel-stripe {
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            -45deg,
            transparent,
            transparent 18px,
            rgba(0,0,0,.04) 18px,
            rgba(0,0,0,.04) 19px
        );
    }

    .logo-container {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        text-align: center;
    }

    .logo-ring {
        width: 160px; height: 160px;
        border-radius: 50%;
        background: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow:
            0 0 0 6px rgba(255,255,255,.3),
            0 16px 40px rgba(0,0,0,.2);
        padding: 8px;
        transition: transform .4s ease;
    }
    .logo-ring:hover { transform: scale(1.04); }

    .logo-ring img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }

    .brand-title {
        font-family: 'Cairo', sans-serif;
        font-size: 1.45rem;
        font-weight: 900;
        color: var(--dark);
        line-height: 1.3;
        text-shadow: 0 1px 0 rgba(255,255,255,.3);
    }

    .brand-subtitle {
        font-size: .78rem;
        font-weight: 600;
        letter-spacing: .08em;
        color: rgba(13,27,62,.7);
        text-transform: uppercase;
    }

    /* Badge at bottom of left panel */
    .ministry-badge {
        position: absolute;
        bottom: 28px;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(0,0,0,.12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 100px;
        padding: 6px 16px 6px 8px;
    }
    .ministry-badge .flag-dot {
        width: 28px; height: 28px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
    }
    .ministry-badge .flag-dot img { width: 100%; height: 100%; object-fit: cover; }
    .ministry-badge span {
        font-family: 'Cairo', sans-serif;
        font-size: .7rem;
        font-weight: 700;
        color: rgba(13,27,62,.85);
        line-height: 1.2;
    }

    /* ── RIGHT PANEL ── */
    .panel-right {
        flex: 1;
        background: var(--white);
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 52px 52px;
        position: relative;
        overflow: hidden;
    }

    /* Subtle blue geometric accent top-right */
    .panel-right::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16,53,160,.07) 0%, transparent 70%);
    }

    /* Gold line accent bottom-left */
    .panel-right::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0;
        width: 4px; height: 100%;
        background: linear-gradient(to bottom, transparent, var(--gold), transparent);
        opacity: .5;
    }

    /* Header */
    .form-header {
        margin-bottom: 36px;
    }
    .form-header .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--blue-mid);
        margin-bottom: 10px;
    }
    .form-header .eyebrow::before {
        content: '';
        display: block;
        width: 20px; height: 2px;
        background: var(--gold);
    }
    .form-header h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark);
        line-height: 1.15;
    }
    .form-header p {
        font-size: .85rem;
        color: var(--text-muted);
        margin-top: 8px;
    }

    /* Divider */
    .gold-divider {
        width: 48px; height: 3px;
        background: linear-gradient(90deg, var(--gold), var(--gold-light));
        border-radius: 2px;
        margin: 12px 0 20px;
    }

    /* Alert */
    .alert-opgi {
        background: #f0f9f0;
        border-left: 3px solid #27ae60;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: .82rem;
        color: #1e8449;
        margin-bottom: 20px;
    }

    /* Field */
    .field-group {
        margin-bottom: 20px;
    }
    .field-label {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--dark);
        margin-bottom: 8px;
    }
    .field-wrap {
        position: relative;
    }
    .field-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
        transition: color .2s;
    }
    .field-wrap:focus-within .field-icon { color: var(--blue-mid); }

    .opgi-input {
        width: 100%;
        height: 50px;
        padding: 0 16px 0 46px;
        border: 1.5px solid #E2E6EF;
        border-radius: 12px;
        font-family: 'Outfit', sans-serif;
        font-size: .9rem;
        color: var(--dark);
        background: var(--off-white);
        outline: none;
        transition: border-color .25s, box-shadow .25s, background .25s;
    }
    .opgi-input::placeholder { color: #B0B8CC; }
    .opgi-input:focus {
        border-color: var(--blue-mid);
        background: var(--white);
        box-shadow: 0 0 0 4px rgba(16,53,160,.08);
    }
    .opgi-input.is-invalid {
        border-color: #e74c3c;
        box-shadow: 0 0 0 4px rgba(231,76,60,.08);
    }
    .invalid-feedback { font-size: .78rem; color: #e74c3c; margin-top: 6px; }

    /* Remember me */
    .remember-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 28px;
    }
    .opgi-check {
        width: 18px; height: 18px;
        accent-color: var(--blue-mid);
        cursor: pointer;
        border-radius: 4px;
    }
    .remember-row label {
        font-size: .83rem;
        color: var(--text-muted);
        cursor: pointer;
        user-select: none;
    }

    /* Submit button */
    .btn-opgi {
        width: 100%;
        height: 52px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--blue) 0%, var(--blue-mid) 60%, var(--blue-light) 100%);
        color: var(--white);
        font-family: 'Outfit', sans-serif;
        font-size: .95rem;
        font-weight: 600;
        letter-spacing: .04em;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: transform .2s, box-shadow .2s;
        box-shadow: 0 6px 24px rgba(16,53,160,.35);
    }
    .btn-opgi::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.12), transparent);
    }
    /* Gold shimmer on hover */
    .btn-opgi::after {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(245,194,0,.2), transparent);
        transform: skewX(-20deg);
        transition: left .5s ease;
    }
    .btn-opgi:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 32px rgba(16,53,160,.45);
    }
    .btn-opgi:hover::after { left: 160%; }
    .btn-opgi:active { transform: translateY(0); }

    /* Footer note */
    .form-footer {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #EEF0F5;
        text-align: center;
        font-size: .75rem;
        color: #B0B8CC;
        line-height: 1.7;
    }
    .form-footer strong { color: var(--text-muted); }

    /* ── Responsive ── */
    @media (max-width: 720px) {
        .login-card { flex-direction: column; }
        .panel-left {
            flex: 0 0 auto;
            padding: 40px 24px 32px;
            min-height: 220px;
        }
        .panel-left::before { width: 260px; height: 260px; border-width: 28px; }
        .logo-ring { width: 110px; height: 110px; }
        .brand-title { font-size: 1.1rem; }
        .ministry-badge { display: none; }
        .panel-right { padding: 36px 28px; }
    }
</style>

<div class="opgi-bg"></div>
<div class="dots-layer"></div>

<div class="login-wrapper">
    <div class="login-card">

        {{-- ══ LEFT PANEL ══ --}}
        <div class="panel-left">
            <div class="panel-stripe"></div>

            <div class="logo-container">
                <div class="logo-ring">
                    <img src="{{ asset('images/OPGI.jpg') }}" alt="Logo OPGI">
                </div>

                <div>
                    <div class="brand-title">OPGI</div>
                    <div class="brand-subtitle" style="margin-top:6px;">
                        ديوان الترقية والتسيير العقاري
                    </div>
                    <div class="brand-subtitle" style="margin-top:3px; color:rgba(13,27,62,.55);">
                        الدار البيضاء
                    </div>
                </div>
            </div>

            <div class="ministry-badge">
                <div class="flag-dot">
                    <img src="{{ asset('images/algeria.png') }}" alt="Algérie">
                </div>
                <span>وزارة السكن والعمران<br>والمدينة</span>
            </div>
        </div>

        {{-- ══ RIGHT PANEL ══ --}}
        <div class="panel-right">

            <div class="form-header">
               
                <h2>Connexion</h2>
                <div class="gold-divider"></div>
                <p>Système de Gestion des Souscripteurs<br>
                <strong style="color:var(--dark);"> Office de Promotion et de Gestion Immobilière.</strong></p>
            </div>

            @if (session('status'))
                <div class="alert-opgi">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="field-group">
                    <label class="field-label" for="email">Adresse Email</label>
                    <div class="field-wrap">
                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="3"/>
                            <polyline points="2,4 12,13 22,4"/>
                        </svg>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="opgi-input @error('email') is-invalid @enderror"
                            placeholder="votre@email.dz"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="invalid-feedback" />
                </div>

                {{-- Password --}}
                <div class="field-group">
                    <label class="field-label" for="password">Mot de passe</label>
                    <div class="field-wrap">
                        <svg class="field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="opgi-input @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="invalid-feedback" />
                </div>

                {{-- Remember --}}
                <div class="remember-row">
                    <input type="checkbox" id="remember_me" name="remember" class="opgi-check">
                    <label for="remember_me">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn-opgi">Se connecter</button>
            </form>

            <div class="form-footer">
                <strong>OPGI — Dar El Beïda</strong><br>
                Accès réservé au personnel autorisé · République Algérienne Démocratique et Populaire
            </div>

        </div>
    </div>
</div>

@endsection