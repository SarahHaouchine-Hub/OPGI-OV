@extends('layouts.master')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --gold:        #F5C200;
        --gold-hover:  #DBAD00;
        --blue:        #1035A0;
        --blue-mid:    #1A4BC4;
        --blue-light:  #EEF2FF;
        --white:       #FFFFFF;
        --bg-light:    #F4F7FA; /* Nouveau fond très clair */
        --slate-50:    #F8FAFC;
        --slate-100:   #F1F5F9;
        --slate-200:   #E2E8F0;
        --slate-400:   #94A3B8;
        --slate-700:   #334155;
        --slate-900:   #0F172A;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Outfit', sans-serif;
        background-color: var(--bg-light); /* Remplacement du noir */
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ── Fond Clair Flouté (Light Ambient Glow) ── */
    .opgi-bg {
        position: fixed;
        inset: 0;
        background: radial-gradient(circle at 15% 15%, rgba(245, 194, 0, 0.04) 0%, transparent 40%),
                    radial-gradient(circle at 85% 85%, rgba(16, 53, 160, 0.06) 0%, transparent 40%),
                    var(--bg-light);
        z-index: 0;
    }

    /* Motif très discret pour texturer le fond clair */
    .opgi-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(16, 53, 160, 0.05) 1px, transparent 1px);
        background-size: 24px 24px;
        opacity: 0.6;
    }

    /* ── Wrapper Principal ── */
    .login-wrapper {
        position: relative;
        z-index: 1;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    /* ── Carte de Connexion ── */
    .login-card {
        display: flex;
        width: 100%;
        max-width: 920px;
        min-height: 560px;
        background: var(--white);
        border-radius: 20px;
        overflow: hidden;
        /* Ombre beaucoup plus douce et claire pour un fond blanc */
        box-shadow: 0 20px 40px -10px rgba(16, 53, 160, 0.08), 
                    0 0 20px rgba(0, 0, 0, 0.02);
    }

    /* ── PANNEAU GAUCHE (Branding) ── */
    .panel-left {
        flex: 0 0 40%;
        /* Bleu plus vibrant et moderne, fini le bleu très sombre/noir */
        background: linear-gradient(135deg, var(--blue-mid) 0%, var(--blue) 100%);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        color: var(--white);
        overflow: hidden;
    }

    /* Ligne dorée en haut du panneau gauche */
    .panel-left::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
        background: var(--gold);
    }

    /* Effet de lumière doux dans le panneau gauche */
    .panel-left::before {
        content: '';
        position: absolute;
        top: -50px; left: -50px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .logo-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 24px;
        text-align: center;
        z-index: 2;
    }

    .logo-wrapper-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: var(--white);
        padding: 6px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .logo-wrapper-img img {
        width: 100%; height: 100%;
        object-fit: contain;
        border-radius: 50%;
    }

    .brand-title {
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .brand-subtitle-ar {
        font-family: 'Cairo', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--gold);
        margin-top: 4px;
    }

    .brand-location-ar {
        font-family: 'Cairo', sans-serif;
        font-size: 0.9rem;
        color: var(--blue-light);
        margin-top: 2px;
    }

    .ministry-badge {
        position: absolute;
        bottom: 30px;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 6px 14px;
        border-radius: 50px;
    }

    .ministry-badge img {
        width: 20px; height: 20px;
        object-fit: cover;
        border-radius: 50%;
    }

    .ministry-badge span {
        font-family: 'Cairo', sans-serif;
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--white);
        line-height: 1.3;
    }

    /* ── PANNEAU DROIT (Formulaire) ── */
    .panel-right {
        flex: 1;
        padding: 50px 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: var(--white);
    }

    .form-header {
        margin-bottom: 32px;
    }

    .form-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--slate-900);
    }

    .form-header p {
        font-size: 0.9rem;
        color: var(--slate-400);
        margin-top: 4px;
    }

    .form-header p strong {
        color: var(--slate-700);
    }

    /* Alertes */
    .alert-opgi {
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 0.85rem;
        color: #166534;
        margin-bottom: 24px;
    }

    /* Groupes de champs */
    .field-group {
        margin-bottom: 20px;
    }

    .field-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--slate-700);
        margin-bottom: 6px;
    }

    .field-wrap {
        position: relative;
    }

    .field-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-400);
        pointer-events: none;
        transition: color 0.2s;
    }

    .opgi-input {
        width: 100%;
        height: 46px;
        padding: 0 40px 0 42px;
        border: 1px solid var(--slate-200);
        border-radius: 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem;
        color: var(--slate-900);
        background: var(--slate-50);
        outline: none;
        transition: all 0.2s ease;
    }

    .opgi-input:focus {
        border-color: var(--blue);
        background: var(--white);
        box-shadow: 0 0 0 4px rgba(16, 53, 160, 0.1);
    }

    .field-wrap:focus-within .field-icon {
        color: var(--blue);
    }

    /* Bouton de visibilité mot de passe */
    .password-toggle {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--slate-400);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
    }
    .password-toggle:hover { color: var(--slate-700); }

    /* Erreurs de validation */
    .opgi-input.is-invalid {
        border-color: #EF4444;
        background-color: #FEF2F2;
    }
    .invalid-feedback {
        font-size: 0.78rem;
        color: #EF4444;
        margin-top: 6px;
        display: block;
    }

    /* Se souvenir de moi */
    .remember-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
    }

    .opgi-check {
        width: 16px; height: 16px;
        accent-color: var(--blue);
        cursor: pointer;
    }

    .remember-row label {
        font-size: 0.85rem;
        color: var(--slate-700);
        cursor: pointer;
        user-select: none;
    }

    /* Bouton Soumettre */
    .btn-opgi {
        width: 100%;
        height: 48px;
        border: none;
        border-radius: 10px;
        background: var(--blue);
        color: var(--white);
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(16, 53, 160, 0.15);
    }

    .btn-opgi:hover {
        background: var(--blue-mid);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(16, 53, 160, 0.25);
    }

    .btn-opgi:active {
        transform: translateY(0);
    }

    /* Pied de page du formulaire */
    .form-footer {
        margin-top: 32px;
        padding-top: 16px;
        border-top: 1px solid var(--slate-100);
        text-align: center;
        font-size: 0.75rem;
        color: var(--slate-400);
        line-height: 1.5;
    }

    .form-footer strong {
        color: var(--slate-700);
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .login-card { flex-direction: column; min-height: auto; }
        .panel-left { flex: 0 0 auto; padding: 36px 20px; }
        .ministry-badge { display: none; }
        .panel-right { padding: 36px 24px; }
        .logo-wrapper-img { width: 100px; height: 100px; }
    }
</style>

<div class="opgi-bg"></div>

<div class="login-wrapper">
    <div class="login-card">

        {{-- ══ PANNEAU GAUCHE ══ --}}
        <div class="panel-left">
            <div class="logo-container">
                <div class="logo-wrapper-img">
                    <img src="{{ asset('images/OPGI.jpg') }}" alt="Logo OPGI">
                </div>

                <div>
                    <div class="brand-title">OPGI</div>
                    <div class="brand-subtitle-ar">ديوان الترقية والتسيير العقاري</div>
                    <div class="brand-location-ar">الدار البيضاء</div>
                </div>
            </div>

            <div class="ministry-badge">
                <img src="{{ asset('images/algeria.png') }}" alt="Algérie">
                <span>وزارة السكن والعمران<br>والمدينة</span>
            </div>
        </div>

        {{-- ══ PANNEAU DROIT ══ --}}
        <div class="panel-right">

            <div class="form-header">
                <h2>Connexion</h2>
                <p>Système de Gestion des Souscripteurs<br>
                <strong>Office de Promotion et de Gestion Immobilière</strong></p>
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
                        <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                        <svg class="field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                        <button type="button" id="togglePassword" class="password-toggle" aria-label="Afficher/Masquer le mot de passe">
                            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="invalid-feedback" />
                </div>

                {{-- Remember Me --}}
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

{{-- Script UX pour la visibilité du mot de passe --}}
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            // Icone œil barré
            eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            passwordInput.type = 'password';
            // Icone œil normal
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    });
</script>

@endsection