<style>
    body { background-color: #f4f7f6; }
    .card-header-gradient { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-bottom: none; }
    .custom-card { border-radius: 12px; border: none; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    .inner-card { border: 1px solid #e0e6ed; border-radius: 10px; transition: border-color 0.3s; }
    .inner-card:hover { border-color: #2a5298; }
    .input-group-text { background-color: #f8f9fa; border-right: none; color: #1e3c72; }
    .form-control { border-left: none; border-radius: 0 8px 8px 0; }
    .form-control:focus { border-color: #ced4da; box-shadow: none; background-color: #fdfdfd; }
    .form-select:disabled { background-color: #f0f0f0; color: #aaa; cursor: not-allowed; }
    .btn-submit {
        background: linear-gradient(45deg, #198754, #28a745);
        border: none; padding: 12px 40px; border-radius: 50px;
        font-weight: 600; box-shadow: 0 4px 15px rgba(25,135,84,0.2);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(25,135,84,0.3); }

    .sel-loading { position: relative; }
    .sel-loading::after {
        content: ''; position: absolute; right: 34px; top: 50%;
        transform: translateY(-50%); width: 14px; height: 14px;
        border: 2px solid #dee2e6; border-top-color: #2a5298;
        border-radius: 50%; animation: spin .6s linear infinite;
        pointer-events: none;
    }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

    .step-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 50%; font-size: 11px;
        font-weight: 700; background-color: #6c757d; color: white;
        margin-right: 5px; flex-shrink: 0; transition: background-color .3s;
    }
    .step-badge.active { background-color: #1e3c72; }
    .step-badge.done   { background-color: #198754; }

    .import-box {
        border: 2px dashed #cbd5e1; border-radius: 20px;
        background-color: #f8fafc; transition: all 0.2s;
    }
    .import-box:hover { border-color: #2a5298; background-color: #f1f5f9; }

    /* Section conjoint masquée par défaut */
    #bloc_conjoint { display: none; }
    #bloc_conjoint.show { display: block; }

    /* Séparateur de section */
    .section-divider {
        display: flex; align-items: center; gap: 12px; margin: 8px 0 16px;
        color: #6c757d; font-size: 0.78rem; font-weight: 600; text-transform: uppercase;
    }
    .section-divider::before, .section-divider::after {
        content: ''; flex: 1; height: 1px; background: #dee2e6;
    }
</style>

<x-app-layout>
<div class="container py-5">

    @if(session('fiche_url'))
        <script>window.open("{{ session('fiche_url') }}", "_blank")</script>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card custom-card">
        <div class="card-header card-header-gradient p-4 text-white">
            <div class="d-flex align-items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor"
                     class="bi bi-person-fill-add" viewBox="0 0 18 15">
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                    <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4"/>
                </svg>
                <h4 class="mb-0 fw-bold">Inscription Souscripteur Pour Un Programme</h4>
            </div>
        </div>

        <div class="card-body p-4">
            <ul class="nav nav-tabs mb-4" id="inscriptionTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="individual-tab" data-bs-toggle="tab"
                            data-bs-target="#individual" type="button" role="tab">
                        <i class="bi bi-person-badge me-1"></i> Saisie individuelle
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="import-tab" data-bs-toggle="tab"
                            data-bs-target="#import" type="button" role="tab">
                        <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="inscriptionTabContent">

                {{-- ═══════════════════════════════════════════════════════════════
                     ONGLET SAISIE INDIVIDUELLE
                ═══════════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade show active" id="individual" role="tabpanel">
                    <form action="{{ route('souscripteur.store') }}" method="POST" id="mainForm">
                        @csrf

                        {{-- ══════════════════════════════════════════════════════
                             BLOC 1 — INFORMATIONS PERSONNELLES
                        ══════════════════════════════════════════════════════ --}}
                        <div class="inner-card mb-4 overflow-hidden">
                            <div class="bg-light p-3 border-bottom d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                <span class="fw-bold text-uppercase small text-secondary">Informations Personnelles</span>
                            </div>
                            <div class="p-4">

                                {{-- Nom / Prénom (FR) --}}
                                <div class="row g-4 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nom (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="nom"
                                                   class="form-control @error('nom') is-invalid @enderror"
                                                   value="{{ old('nom') }}" required placeholder="NOM">
                                        </div>
                                        @error('nom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prénom (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="prenom"
                                                   class="form-control @error('prenom') is-invalid @enderror"
                                                   value="{{ old('prenom') }}" required placeholder="Prénom">
                                        </div>
                                        @error('prenom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- Date naissance / Lieu naissance --}}
                                <div class="row g-4 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Date de Naissance</label>
                                        <input type="date" name="date_naissance"
                                               class="form-control @error('date_naissance') is-invalid @enderror"
                                               value="{{ old('date_naissance') }}" required style="border-radius:8px;">
                                        @error('date_naissance')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Lieu de Naissance</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                            <input type="text" name="lieu_naissance"
                                                   class="form-control @error('lieu_naissance') is-invalid @enderror"
                                                   value="{{ old('lieu_naissance') }}" placeholder="Ville / Commune">
                                        </div>
                                        @error('lieu_naissance')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Situation Familiale</label>
                                        <select name="situation_familiale" id="sel_situation"
                                                class="form-select @error('situation_familiale') is-invalid @enderror" required>
                                            <option value="celibataire" {{ old('situation_familiale','celibataire') == 'celibataire' ? 'selected':'' }}>Célibataire</option>
                                            <option value="marie"       {{ old('situation_familiale') == 'marie'       ? 'selected':'' }}>Marié(e)</option>
                                            <option value="divorce"     {{ old('situation_familiale') == 'divorce'     ? 'selected':'' }}>Divorcé(e)</option>
                                            <option value="veuf"        {{ old('situation_familiale') == 'veuf'        ? 'selected':'' }}>Veuf / Veuve</option>
                                        </select>
                                        @error('situation_familiale')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- NIN --}}
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">
                                            NIN <span class="text-muted fw-normal">(Numéro d'Identification Nationale)</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                            <input type="text" name="nin"
                                                   class="form-control @error('nin') is-invalid @enderror"
                                                   value="{{ old('nin') }}" required maxlength="18"
                                                   placeholder="Ex: 1 99999999999999 99"
                                                   pattern="[0-9\s]{18,20}">
                                        </div>
                                        @error('nin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                            </div>
                        </div>{{-- /BLOC 1 --}}

                        {{-- ══════════════════════════════════════════════════════
                             BLOC 2 — PARENTS DU SOUSCRIPTEUR
                        ══════════════════════════════════════════════════════ --}}
                        <div class="inner-card mb-4 overflow-hidden">
                            <div class="bg-light p-3 border-bottom d-flex align-items-center">
                                <i class="bi bi-people-fill text-success me-2"></i>
                                <span class="fw-bold text-uppercase small text-secondary">Informations des Parents</span>
                            </div>
                            <div class="p-4">

                                {{-- Père --}}
                                <div class="section-divider"><i class="bi bi-person me-1"></i> Père</div>
                                <div class="row g-4 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nom du Père (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="nom_pere"
                                                   class="form-control @error('nom_pere') is-invalid @enderror"
                                                   value="{{ old('nom_pere') }}" placeholder="Nom">
                                        </div>
                                        @error('nom_pere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prénom du Père (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="prenom_pere"
                                                   class="form-control @error('prenom_pere') is-invalid @enderror"
                                                   value="{{ old('prenom_pere') }}" placeholder="Prénom">
                                        </div>
                                        @error('prenom_pere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- Mère --}}
                                <div class="section-divider"><i class="bi bi-person me-1"></i> Mère</div>
                                <div class="row g-4 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nom de la Mère (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="nom_mere"
                                                   class="form-control @error('nom_mere') is-invalid @enderror"
                                                   value="{{ old('nom_mere') }}" placeholder="Nom">
                                        </div>
                                        @error('nom_mere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prénom de la Mère (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="prenom_mere"
                                                   class="form-control @error('prenom_mere') is-invalid @enderror"
                                                   value="{{ old('prenom_mere') }}" placeholder="Prénom">
                                        </div>
                                        @error('prenom_mere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                            </div>
                        </div>{{-- /BLOC 2 --}}

                        {{-- ══════════════════════════════════════════════════════
                             BLOC 3 — CONJOINT (affiché uniquement si marié)
                        ══════════════════════════════════════════════════════ --}}
                        <div id="bloc_conjoint"
                             class="inner-card mb-4 overflow-hidden {{ old('situation_familiale') == 'marie' ? 'show' : '' }}">
                            <div class="p-3 border-bottom d-flex align-items-center" style="background-color:#fff8e1;">
                                <i class="bi bi-heart-fill text-warning me-2"></i>
                                <span class="fw-bold text-uppercase small" style="color:#b7791f;">Informations du Conjoint</span>
                                <span class="badge bg-warning text-dark ms-auto small">Requis si marié(e)</span>
                            </div>
                            <div class="p-4" style="background-color:#fffdf5;">

                                <div class="section-divider">Identité</div>
                                <div class="row g-4 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nom du Conjoint (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-heart"></i></span>
                                            <input type="text" name="conjoint_nom"
                                                   class="form-control @error('conjoint_nom') is-invalid @enderror"
                                                   value="{{ old('conjoint_nom') }}" placeholder="Nom">
                                        </div>
                                        @error('conjoint_nom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prénom du Conjoint (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-heart"></i></span>
                                            <input type="text" name="conjoint_prenom"
                                                   class="form-control @error('conjoint_prenom') is-invalid @enderror"
                                                   value="{{ old('conjoint_prenom') }}" placeholder="Prénom">
                                        </div>
                                        @error('conjoint_prenom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row g-4 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">NIN du Conjoint</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                            <input type="text" name="conjoint_nin"
                                                   class="form-control @error('conjoint_nin') is-invalid @enderror"
                                                   value="{{ old('conjoint_nin') }}" maxlength="18"
                                                   placeholder="Ex: 2 99999999999999 99">
                                        </div>
                                        @error('conjoint_nin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Date de Naissance du Conjoint</label>
                                        <input type="date" name="conjoint_date_naissance"
                                               class="form-control @error('conjoint_date_naissance') is-invalid @enderror"
                                               value="{{ old('conjoint_date_naissance') }}" style="border-radius:8px;">
                                        @error('conjoint_date_naissance')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Lieu de Naissance (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                            <input type="text" name="conjoint_lieu_naissance"
                                                   class="form-control @error('conjoint_lieu_naissance') is-invalid @enderror"
                                                   value="{{ old('conjoint_lieu_naissance') }}" placeholder="Ville / Commune">
                                        </div>
                                        @error('conjoint_lieu_naissance')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- Parents du conjoint — Père --}}
                                <div class="section-divider"><i class="bi bi-people me-1"></i> Parents du Conjoint — Père</div>
                                <div class="row g-4 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nom du Père (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="conjoint_nom_pere"
                                                   class="form-control @error('conjoint_nom_pere') is-invalid @enderror"
                                                   value="{{ old('conjoint_nom_pere') }}" placeholder="Nom">
                                        </div>
                                        @error('conjoint_nom_pere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prénom du Père (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="conjoint_prenom_pere"
                                                   class="form-control @error('conjoint_prenom_pere') is-invalid @enderror"
                                                   value="{{ old('conjoint_prenom_pere') }}" placeholder="Prénom">
                                        </div>
                                        @error('conjoint_prenom_pere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- Parents du conjoint — Mère --}}
                                <div class="section-divider"><i class="bi bi-people me-1"></i> Parents du Conjoint — Mère</div>
                                <div class="row g-4 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nom de la Mère (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="conjoint_nom_mere"
                                                   class="form-control @error('conjoint_nom_mere') is-invalid @enderror"
                                                   value="{{ old('conjoint_nom_mere') }}" placeholder="Nom">
                                        </div>
                                        @error('conjoint_nom_mere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prénom de la Mère (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="conjoint_prenom_mere"
                                                   class="form-control @error('conjoint_prenom_mere') is-invalid @enderror"
                                                   value="{{ old('conjoint_prenom_mere') }}" placeholder="Prénom">
                                        </div>
                                        @error('conjoint_prenom_mere')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                            </div>
                        </div>{{-- /BLOC 3 conjoint --}}

                        {{-- ══════════════════════════════════════════════════════
                             BLOC 4 — AFFECTATION
                        ══════════════════════════════════════════════════════ --}}
                        <div class="inner-card mb-4 overflow-hidden border-primary-subtle">
                            <div class="p-3 border-bottom d-flex align-items-center" style="background-color:#eef4ff;">
                                <i class="bi bi-house-door-fill text-primary me-2"></i>
                                <span class="fw-bold text-uppercase small text-primary">Détails de l'Affectation</span>
                            </div>
                            <div class="p-4" style="background-color:#fcfdff;">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">
                                            <span class="step-badge active" id="b1">1</span> Wilaya
                                        </label>
                                        <select name="wilaya_id" id="sel_wilaya"
                                                class="form-select @error('wilaya_id') is-invalid @enderror">
                                            <option value="">-- Choisir une wilaya --</option>
                                            @foreach($wilayas as $w)
                                                <option value="{{ $w->id }}"
                                                    {{ old('wilaya_id') == $w->id ? 'selected':'' }}>
                                                    {{ $w->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('wilaya_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">
                                            <span class="step-badge" id="b2">2</span> Programme
                                        </label>
                                        <div id="wrap_programme">
                                            <select name="programme_id" id="sel_programme"
                                                    class="form-select @error('programme_id') is-invalid @enderror" disabled>
                                                <option value="">-- Choisir d'abord une wilaya --</option>
                                            </select>
                                        </div>
                                        @error('programme_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">
                                            <span class="step-badge" id="b3">3</span> Projet
                                        </label>
                                        <div id="wrap_site">
                                            <select name="site_id" id="sel_site"
                                                    class="form-select @error('site_id') is-invalid @enderror" disabled>
                                                <option value="">-- Choisir d'abord un programme --</option>
                                            </select>
                                        </div>
                                        @error('site_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">
                                            <span class="step-badge" id="b4">4</span> Bâtiment
                                        </label>
                                        <div id="wrap_bat">
                                            <select id="sel_batiment" class="form-select" disabled>
                                                <option value="">-- Choisir d'abord un projet --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">
                                            <span class="step-badge" id="b5">5</span> Étage
                                        </label>
                                        <div id="wrap_etage">
                                            <select id="sel_etage" class="form-select" disabled>
                                                <option value="">-- Choisir d'abord un bâtiment --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">
                                            <span class="step-badge" id="b6">6</span> N° Porte
                                        </label>
                                        <div id="wrap_porte">
                                            <select id="sel_porte"
                                                    class="form-select @error('logement_id') is-invalid @enderror" disabled>
                                                <option value="">-- Choisir d'abord un étage --</option>
                                            </select>
                                        </div>
                                        @error('logement_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div id="recap" class="alert alert-success d-none py-2 mb-0">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Logement sélectionné :&nbsp;<strong id="recap_text"></strong>
                                </div>
                                <input type="hidden" name="logement_id" id="logement_id">
                            </div>
                        </div>{{-- /BLOC 4 --}}

                        <div class="text-center py-3">
                            <button type="reset" id="btn_reset" class="btn btn-link text-decoration-none text-muted me-3">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-submit text-white px-5 shadow">
                                <i class="bi bi-check2-circle me-2"></i> Enregistrer le Souscripteur
                            </button>
                        </div>
                    </form>
                </div>{{-- /tab-pane individual --}}

                {{-- ═══════════════════════════════════════════════════════════════
                     ONGLET IMPORT EXCEL
                ═══════════════════════════════════════════════════════════════ --}}
                <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">

                    @if(session('import_errors'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Erreurs détectées lors de l'import :</strong>
                            <ul class="mb-0 mt-2" style="max-height:200px;overflow-y:auto;">
                                @foreach(session('import_errors') as $err)
                                    <li class="small">{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-4 mt-1">

                        {{-- LPL --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm"
                                 style="border-top:4px solid #1E3C72!important;border-radius:12px;">
                                <div class="card-body p-4 text-center d-flex flex-column">
                                    <div class="mb-3">
                                        <span class="badge px-3 py-2 fs-6 fw-bold"
                                              style="background-color:#1E3C72;color:#fff;border-radius:8px;">
                                            LPL Promotionnel
                                        </span>
                                    </div>
                                    <p class="text-muted small flex-grow-1">
                                        Paiement libre par tranche.<br>
                                        Pourcentage saisi manuellement dans l'OV.<br>
                                        Pas d'aide BNH/FNPOS dans ce flux.
                                    </p>
                                    <a href="{{ asset('downloads/Import_LPL_Promotionnel.xlsx') }}"
                                       class="btn btn-outline-primary btn-sm mb-3">
                                        <i class="bi bi-download me-1"></i> Télécharger le modèle LPL
                                    </a>
                                    <form action="{{ route('souscripteur.import.lpl') }}" method="POST"
                                          enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="file" name="excel_file_lpl"
                                                   class="form-control form-control-sm"
                                                   accept=".xlsx,.xls,.csv" required>
                                        </div>
                                        <button type="submit" class="btn w-100 text-white fw-bold"
                                                style="background:linear-gradient(45deg,#1E3C72,#2A5298);border-radius:8px;">
                                            <i class="bi bi-upload me-1"></i> Importer LPL
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- LSP --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm"
                                 style="border-top:4px solid #1A5276!important;border-radius:12px;">
                                <div class="card-body p-4 text-center d-flex flex-column">
                                    <div class="mb-3">
                                        <span class="badge px-3 py-2 fs-6 fw-bold"
                                              style="background-color:#1A5276;color:#fff;border-radius:8px;">
                                            LSP
                                        </span>
                                    </div>
                                    <p class="text-muted small flex-grow-1">
                                        Location-Vente Promotionnelle.<br>
                                        Montant saisi librement à chaque OV.<br>
                                        Aides BNH/FNPOS enregistrées dans l'OV.
                                    </p>
                                    <a href="{{ asset('downloads/Import_LSP.xlsx') }}"
                                       class="btn btn-outline-primary btn-sm mb-3">
                                        <i class="bi bi-download me-1"></i> Télécharger le modèle LSP
                                    </a>
                                    <form action="{{ route('souscripteur.import.lsp') }}" method="POST"
                                          enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="file" name="excel_file_lsp"
                                                   class="form-control form-control-sm"
                                                   accept=".xlsx,.xls,.csv" required>
                                        </div>
                                        <button type="submit" class="btn w-100 text-white fw-bold"
                                                style="background:linear-gradient(45deg,#1A5276,#154360);border-radius:8px;">
                                            <i class="bi bi-upload me-1"></i> Importer LSP
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- LPA --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm"
                                 style="border-top:4px solid #6C3483!important;border-radius:12px;">
                                <div class="card-body p-4 text-center d-flex flex-column">
                                    <div class="mb-3">
                                        <span class="badge px-3 py-2 fs-6 fw-bold"
                                              style="background-color:#6C3483;color:#fff;border-radius:8px;">
                                            LPA
                                        </span>
                                    </div>
                                    <p class="text-muted small flex-grow-1">
                                        Logement Promotionnel Aidé.<br>
                                        5 tranches fixes : 20% / 15% / 35% / 25% / 5%.<br>
                                        Aide BNH <strong>obligatoire</strong> avant tranche 1.
                                    </p>
                                    <a href="{{ asset('downloads/Import_LPA.xlsx') }}"
                                       class="btn btn-outline-primary btn-sm mb-3">
                                        <i class="bi bi-download me-1"></i> Télécharger le modèle LPA
                                    </a>
                                    <form action="{{ route('souscripteur.import.lpa') }}" method="POST"
                                          enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="file" name="excel_file_lpa"
                                                   class="form-control form-control-sm"
                                                   accept=".xlsx,.xls,.csv" required>
                                        </div>
                                        <button type="submit" class="btn w-100 text-white fw-bold"
                                                style="background:linear-gradient(45deg,#6C3483,#512E5F);border-radius:8px;">
                                            <i class="bi bi-upload me-1"></i> Importer LPA
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /row --}}

                    <div class="mt-4 p-3 rounded" style="background:#f8f9fa;border-left:4px solid #dee2e6;">
                        <p class="mb-1 small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Structure commune aux 3 fichiers (17 colonnes, données à partir de la ligne 9) :</strong><br>
                            <code>Nom FR | Prénom FR | Nom AR | Prénom AR | Date naissance | NIN | Wilaya | Programme |
                                  Projet | Commune | N° Bâtiment | N° Étage | N° Porte | N° Lot | Surface | Typologie | Prix</code>
                        </p>
                        <p class="mb-0 small text-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Formater la colonne NIN en <strong>TEXTE</strong> avant la saisie pour éviter la notation scientifique.
                        </p>
                    </div>

                </div>{{-- /tab-pane import --}}

            </div>{{-- /tab-content --}}
        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</div>{{-- /container --}}

<script>
document.addEventListener("DOMContentLoaded", function () {

    const $ = id => document.getElementById(id);

    // ── Affichage dynamique du bloc conjoint ─────────────────────────────────
    const selSituation = $("sel_situation");
    const blocConjoint = $("bloc_conjoint");

    function toggleConjoint() {
        const isMarie = selSituation.value === 'marie';
        blocConjoint.classList.toggle('show', isMarie);

        // Champs requis uniquement si marié(e)
        const required = ['conjoint_nom', 'conjoint_prenom', 'conjoint_nin', 'conjoint_date_naissance'];
        blocConjoint.querySelectorAll('input, select').forEach(el => el.removeAttribute('required'));
        if (isMarie) {
            required.forEach(name => {
                const el = blocConjoint.querySelector(`[name="${name}"]`);
                if (el) el.setAttribute('required', 'required');
            });
        }
    }

    selSituation.addEventListener('change', toggleConjoint);
    toggleConjoint(); // État initial

    // ── Cascade affectation ───────────────────────────────────────────────────
    const selWilaya   = $("sel_wilaya");
    const selProg     = $("sel_programme");
    const selSite     = $("sel_site");
    const selBatiment = $("sel_batiment");
    const selEtage    = $("sel_etage");
    const selPorte    = $("sel_porte");
    const hiddenId    = $("logement_id");
    const recap       = $("recap");
    const recapText   = $("recap_text");

    const levels = [
        null,
        { sel: selWilaya,   wrap: null,             msg: '' },
        { sel: selProg,     wrap: 'wrap_programme', msg: "-- Choisir d'abord une wilaya --"   },
        { sel: selSite,     wrap: 'wrap_site',      msg: "-- Choisir d'abord un programme --" },
        { sel: selBatiment, wrap: 'wrap_bat',       msg: "-- Choisir d'abord un projet --"    },
        { sel: selEtage,    wrap: 'wrap_etage',     msg: "-- Choisir d'abord un bâtiment --"  },
        { sel: selPorte,    wrap: 'wrap_porte',     msg: "-- Choisir d'abord un étage --"     },
    ];

    function reset(sel, msg) {
        sel.innerHTML = `<option value="">${msg}</option>`;
        sel.disabled = true;
    }
    function spin(wrapId, on) {
        if (wrapId) $(wrapId).classList.toggle('sel-loading', on);
    }
    function badge(n, state) {
        const el = $('b' + n); if (!el) return;
        el.classList.remove('active', 'done');
        if (state === 'active') el.classList.add('active');
        if (state === 'done')   el.classList.add('done');
        el.textContent = state === 'done' ? '✓' : n;
    }
    function clearFrom(fromStep) {
        for (let i = fromStep; i <= 6; i++) {
            if (i > 1) reset(levels[i].sel, levels[i].msg);
            badge(i, 'pending');
        }
        badge(fromStep, 'active');
        hiddenId.value = '';
        recap.classList.add('d-none');
    }

    async function loadInto(url, sel, wrapId, buildOption) {
        spin(wrapId, true);
        try {
            const data = await fetch(url).then(r => r.json());
            if (!data.length) {
                sel.innerHTML = '<option value="">Aucun résultat disponible</option>';
                return false;
            }
            data.forEach(item => {
                const o = document.createElement('option');
                buildOption(o, item);
                sel.appendChild(o);
            });
            sel.disabled = false;
            return true;
        } catch (e) {
            console.error(e);
            return false;
        } finally {
            spin(wrapId, false);
        }
    }

    function setDefaultAlger(selectEl) {
        if (!selectEl) return false;
        const opt = Array.from(selectEl.options).find(o => o.textContent.trim() === 'Alger');
        if (opt && !selectEl.value) { selectEl.value = opt.value; return true; }
        return false;
    }

    selWilaya.addEventListener("change", async function () {
        clearFrom(2);
        if (!this.value) { badge(1, 'active'); return; }
        const ok = await loadInto(
            `/api/souscripteur/programmes-by-wilaya/${this.value}`,
            selProg, 'wrap_programme',
            (o, p) => { o.value = p.id; o.textContent = p.libelle; }
        );
        if (ok) { badge(1, 'done'); badge(2, 'active'); }
    });

    selProg.addEventListener("change", async function () {
        clearFrom(3);
        if (!this.value) { badge(2, 'active'); return; }
        const ok = await loadInto(
            `/api/souscripteur/sites/${selWilaya.value}/${this.value}`,
            selSite, 'wrap_site',
            (o, s) => { o.value = s.id; o.textContent = s.libelle; }
        );
        if (ok) { badge(2, 'done'); badge(3, 'active'); }
    });

    selSite.addEventListener("change", async function () {
        clearFrom(4);
        if (!this.value) { badge(3, 'active'); return; }
        const ok = await loadInto(
            `/api/souscripteur/batiments/${this.value}`,
            selBatiment, 'wrap_bat',
            (o, b) => { o.value = b; o.textContent = 'Bâtiment ' + b; }
        );
        if (ok) { badge(3, 'done'); badge(4, 'active'); }
    });

    selBatiment.addEventListener("change", async function () {
        clearFrom(5);
        if (!this.value) { badge(4, 'active'); return; }
        const ok = await loadInto(
            `/api/souscripteur/etages/${selSite.value}/${this.value}`,
            selEtage, 'wrap_etage',
            (o, e) => { o.value = e; o.textContent = 'Étage ' + e; }
        );
        if (ok) { badge(4, 'done'); badge(5, 'active'); }
    });

    selEtage.addEventListener("change", async function () {
        clearFrom(6);
        if (!this.value) { badge(5, 'active'); return; }
        const ok = await loadInto(
            `/api/souscripteur/portes/${selSite.value}/${selBatiment.value}/${this.value}`,
            selPorte, 'wrap_porte',
            (o, p) => { o.value = p.id; o.textContent = 'Porte ' + p.num_porte; }
        );
        if (ok) { badge(5, 'done'); badge(6, 'active'); }
    });

    selPorte.addEventListener("change", function () {
        hiddenId.value = this.value;
        if (this.value) {
            badge(6, 'done');
            recapText.textContent = [
                selProg.options[selProg.selectedIndex]?.text     || '',
                selSite.options[selSite.selectedIndex]?.text     || '',
                'Bât. ' + selBatiment.value,
                'Ét. '  + selEtage.value,
                'Porte ' + (this.options[this.selectedIndex]?.text.replace('Porte ', '') || '')
            ].filter(Boolean).join('  —  ');
            recap.classList.remove('d-none');
        } else {
            badge(6, 'active');
            hiddenId.value = '';
            recap.classList.add('d-none');
        }
    });

    $("btn_reset").addEventListener('click', function () {
        setTimeout(() => {
            if (setDefaultAlger(selWilaya)) {
                selWilaya.dispatchEvent(new Event('change'));
            } else {
                for (let i = 2; i <= 6; i++) reset(levels[i].sel, levels[i].msg);
                for (let i = 1; i <= 6; i++) badge(i, i === 1 ? 'active' : 'pending');
                hiddenId.value = '';
                recap.classList.add('d-none');
            }
            toggleConjoint();
        }, 10);
    });

    // Initialisation cascade
    if (!selWilaya.value) {
        if (setDefaultAlger(selWilaya)) selWilaya.dispatchEvent(new Event('change'));
    } else {
        selWilaya.dispatchEvent(new Event('change'));
    }

    // Auto-dismiss alertes
    const alertEl = document.getElementById('alert');
    if (alertEl) setTimeout(() => new bootstrap.Alert(alertEl).close(), 4000);
});
</script>
</x-app-layout>