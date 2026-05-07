<style>
    /* Vos styles existants (gardez-les tels quels) */
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

    /* Spinner chargement */
    .sel-loading { position: relative; }
    .sel-loading::after {
        content: ''; position: absolute; right: 34px; top: 50%;
        transform: translateY(-50%); width: 14px; height: 14px;
        border: 2px solid #dee2e6; border-top-color: #2a5298;
        border-radius: 50%; animation: spin .6s linear infinite;
        pointer-events: none;
    }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

    /* Badges étapes */
    .step-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 50%; font-size: 11px;
        font-weight: 700; background-color: #6c757d; color: white;
        margin-right: 5px; flex-shrink: 0; transition: background-color .3s;
    }
    .step-badge.active { background-color: #1e3c72; }
    .step-badge.done   { background-color: #198754; }

    /* Style pour l'onglet import */
    .import-box {
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        background-color: #f8fafc;
        transition: all 0.2s;
    }
    .import-box:hover {
        border-color: #2a5298;
        background-color: #f1f5f9;
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
            <!-- NAV TABS -->
            <ul class="nav nav-tabs mb-4" id="inscriptionTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button" role="tab" aria-controls="individual" aria-selected="true">
                        <i class="bi bi-person-badge me-1"></i> Saisie individuelle
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab" aria-controls="import" aria-selected="false">
                        <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="inscriptionTabContent">
                <!-- ========= ONGLET SAISIE INDIVIDUELLE ========= -->
                <div class="tab-pane fade show active" id="individual" role="tabpanel" aria-labelledby="individual-tab">
                    <form action="{{ route('souscripteur.store') }}" method="POST" id="mainForm">
                        @csrf

                        {{-- BLOC 1 — INFORMATIONS PERSONNELLES --}}
                        <div class="inner-card mb-4 overflow-hidden">
                            <div class="bg-light p-3 border-bottom d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                <span class="fw-bold text-uppercase small text-secondary">Informations Personnelles</span>
                            </div>
                            <div class="p-4">
                                <div class="row g-4 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nom (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required placeholder="NOM">
                                        </div>
                                        @error('nom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prénom (FR)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror" value="{{ old('prenom') }}" required placeholder="Prénom">
                                        </div>
                                        @error('prenom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row g-4 mb-3" dir="rtl">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">اللقب (بالعربية)</label>
                                        <input type="text" name="nom_ar" class="form-control text-end @error('nom_ar') is-invalid @enderror" value="{{ old('nom_ar') }}" required style="border-radius:8px;">
                                        @error('nom_ar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">الاسم (بالعربية)</label>
                                        <input type="text" name="prenom_ar" class="form-control text-end @error('prenom_ar') is-invalid @enderror" value="{{ old('prenom_ar') }}" required style="border-radius:8px;">
                                        @error('prenom_ar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Date de Naissance</label>
                                        <input type="date" name="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance') }}" required style="border-radius:8px;">
                                        @error('date_naissance')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">NIN <span class="text-muted fw-normal">(Numéro d'Identification Nationale)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                            <input type="text" name="nin" class="form-control @error('nin') is-invalid @enderror" value="{{ old('nin') }}" required maxlength="18" placeholder="Ex: 1 99999999999999 99" pattern="[0-9\s]{18,20}">
                                        </div>
                                        @error('nin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BLOC 2 — AFFECTATION --}}
                        <div class="inner-card mb-4 overflow-hidden border-primary-subtle">
                            <div class="p-3 border-bottom d-flex align-items-center" style="background-color:#eef4ff;">
                                <i class="bi bi-house-door-fill text-primary me-2"></i>
                                <span class="fw-bold text-uppercase small text-primary">Détails de l'Affectation</span>
                            </div>
                            <div class="p-4" style="background-color:#fcfdff;">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold"><span class="step-badge active" id="b1">1</span> Wilaya</label>
                                        <select name="wilaya_id" id="sel_wilaya" class="form-select @error('wilaya_id') is-invalid @enderror">
                                            <option value="">-- Choisir une wilaya --</option>
                                            @foreach($wilayas as $w)
                                                <option value="{{ $w->id }}" {{ old('wilaya_id') == $w->id ? 'selected':'' }}>{{ $w->nom }}</option>
                                            @endforeach
                                        </select>
                                        @error('wilaya_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold"><span class="step-badge" id="b2">2</span> Programme</label>
                                        <div id="wrap_programme">
                                            <select name="programme_id" id="sel_programme" class="form-select @error('programme_id') is-invalid @enderror" disabled>
                                                <option value="">-- Choisir d'abord une wilaya --</option>
                                            </select>
                                        </div>
                                        @error('programme_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold"><span class="step-badge" id="b3">3</span> Site</label>
                                        <div id="wrap_site">
                                            <select name="site_id" id="sel_site" class="form-select @error('site_id') is-invalid @enderror" disabled>
                                                <option value="">-- Choisir d'abord un programme --</option>
                                            </select>
                                        </div>
                                        @error('site_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold"><span class="step-badge" id="b4">4</span> Bâtiment</label>
                                        <div id="wrap_bat">
                                            <select id="sel_batiment" class="form-select" disabled>
                                                <option value="">-- Choisir d'abord un site --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold"><span class="step-badge" id="b5">5</span> Étage</label>
                                        <div id="wrap_etage">
                                            <select id="sel_etage" class="form-select" disabled>
                                                <option value="">-- Choisir d'abord un bâtiment --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold"><span class="step-badge" id="b6">6</span> N° Porte</label>
                                        <div id="wrap_porte">
                                            <select id="sel_porte" class="form-select @error('logement_id') is-invalid @enderror" disabled>
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
                        </div>

                        <div class="text-center py-3">
                            <button type="reset" id="btn_reset" class="btn btn-link text-decoration-none text-muted me-3">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-submit text-white px-5 shadow">
                                <i class="bi bi-check2-circle me-2"></i> Enregistrer le Souscripteur
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========= ONGLET IMPORT EXCEL ========= -->
                <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
                    <div class="text-center py-4 import-box p-5">
                        <i class="bi bi-file-earmark-excel" style="font-size: 4rem; color: #1e743a;"></i>
                        <h5 class="mt-3 mb-4">Importer une liste de souscripteurs depuis un fichier Excel</h5>
                        <form action="{{ route('souscripteur.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf
                            <div class="mb-4">
                                <input type="file" name="excel_file" class="form-control @error('excel_file') is-invalid @enderror" accept=".xlsx, .xls, .csv" required style="max-width: 400px; margin: 0 auto;">
                                @error('excel_file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="small text-muted mb-3">
                                <i class="bi bi-info-circle"></i> Format attendu : colonnes (nom, prenom, nom_ar, prenom_ar, date_naissance, nin, wilaya, programme, site, batiment, etage, porte)
                            </div>
                            <button type="submit" class="btn btn-submit text-white px-4 shadow">
                                <i class="bi bi-upload me-2"></i> Importer et créer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const $  = id => document.getElementById(id);

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
        { sel: selBatiment, wrap: 'wrap_bat',       msg: "-- Choisir d'abord un site --"      },
        { sel: selEtage,    wrap: 'wrap_etage',     msg: "-- Choisir d'abord un bâtiment --"  },
        { sel: selPorte,    wrap: 'wrap_porte',     msg: "-- Choisir d'abord un étage --"     },
    ];

    function reset(sel, msg) {
        sel.innerHTML = `<option value="">${msg}</option>`;
        sel.disabled  = true;
    }

    function spin(wrapId, on) {
        if (wrapId) $(wrapId).classList.toggle('sel-loading', on);
    }

    function badge(n, state) {
        const el = $('b' + n);
        if (!el) return;
        el.classList.remove('active', 'done');
        if (state === 'active') el.classList.add('active');
        if (state === 'done')   el.classList.add('done');
        if (state === 'done') el.textContent = '✓';
        else el.textContent = n;
    }

    function clearFrom(fromStep) {
        for (let i = fromStep; i <= 6; i++) {
            if (i > 1) reset(levels[i].sel, levels[i].msg);
            badge(i, i === fromStep - 1 + 1 ? 'active' : 'pending');
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
        } catch(e) {
            console.error(e);
            return false;
        } finally {
            spin(wrapId, false);
        }
    }

    // Fonction pour sélectionner "Alger" par défaut
    function setDefaultAlger(selectEl) {
        if (!selectEl) return false;
        const options = Array.from(selectEl.options);
        const algerOption = options.find(opt => opt.textContent.trim() === 'Alger');
        if (algerOption && !selectEl.value) {
            selectEl.value = algerOption.value;
            return true;
        }
        return false;
    }

    // Événement changement wilaya
    selWilaya.addEventListener("change", async function () {
        clearFrom(2);
        if (!this.value) { badge(1, 'active'); return; }
        const ok = await loadInto(`/api/souscripteur/programmes-by-wilaya/${this.value}`, selProg, 'wrap_programme', (o, p) => { o.value = p.id; o.textContent = p.libelle; });
        if (ok) { badge(1, 'done'); badge(2, 'active'); }
    });

    selProg.addEventListener("change", async function () {
        clearFrom(3);
        if (!this.value) { badge(2, 'active'); return; }
        const ok = await loadInto(`/api/souscripteur/sites/${selWilaya.value}/${this.value}`, selSite, 'wrap_site', (o, s) => { o.value = s.id; o.textContent = s.libelle; });
        if (ok) { badge(2, 'done'); badge(3, 'active'); }
    });

    selSite.addEventListener("change", async function () {
        clearFrom(4);
        if (!this.value) { badge(3, 'active'); return; }
        const ok = await loadInto(`/api/souscripteur/batiments/${this.value}`, selBatiment, 'wrap_bat', (o, b) => { o.value = b; o.textContent = 'Bâtiment ' + b; });
        if (ok) { badge(3, 'done'); badge(4, 'active'); }
    });

    selBatiment.addEventListener("change", async function () {
        clearFrom(5);
        if (!this.value) { badge(4, 'active'); return; }
        const ok = await loadInto(`/api/souscripteur/etages/${selSite.value}/${this.value}`, selEtage, 'wrap_etage', (o, e) => { o.value = e; o.textContent = 'Étage ' + e; });
        if (ok) { badge(4, 'done'); badge(5, 'active'); }
    });

    selEtage.addEventListener("change", async function () {
        clearFrom(6);
        if (!this.value) { badge(5, 'active'); return; }
        const ok = await loadInto(`/api/souscripteur/portes/${selSite.value}/${selBatiment.value}/${this.value}`, selPorte, 'wrap_porte', (o, p) => { o.value = p.id; o.textContent = 'Porte ' + p.num_porte; });
        if (ok) { badge(5, 'done'); badge(6, 'active'); }
    });

    selPorte.addEventListener("change", function () {
        hiddenId.value = this.value;
        if (this.value) {
            badge(6, 'done');
            recapText.textContent = [
                selProg.options[selProg.selectedIndex]?.text || '',
                selSite.options[selSite.selectedIndex]?.text || '',
                'Bât. ' + selBatiment.value,
                'Ét. '  + selEtage.value,
                'Porte '+ (this.options[this.selectedIndex]?.text.replace('Porte ','') || '')
            ].filter(Boolean).join('  —  ');
            recap.classList.remove('d-none');
        } else {
            badge(6, 'active');
            hiddenId.value = '';
            recap.classList.add('d-none');
        }
    });

    // Réinitialisation du formulaire
    $("btn_reset").addEventListener('click', function () {
        setTimeout(() => {
            // Réinitialiser la wilaya à sa valeur par défaut (Alger si possible)
            if (setDefaultAlger(selWilaya)) {
                // Déclencher le changement pour charger la cascade
                selWilaya.dispatchEvent(new Event('change'));
            } else {
                for (let i = 2; i <= 6; i++) reset(levels[i].sel, levels[i].msg);
                for (let i = 1; i <= 6; i++) badge(i, i === 1 ? 'active' : 'pending');
                hiddenId.value = '';
                recap.classList.add('d-none');
            }
        }, 10);
    });

    // --- Sélection par défaut d'Alger au chargement ---
    // Si aucune wilaya n'est déjà sélectionnée (pas de old value), on met Alger par défaut
    if (!selWilaya.value) {
        if (setDefaultAlger(selWilaya)) {
            // Déclencher l'événement change pour charger les programmes
            selWilaya.dispatchEvent(new Event('change'));
        }
    } else {
        // Si une wilaya est déjà sélectionnée (après erreur de validation par ex), on charge quand même la cascade
        selWilaya.dispatchEvent(new Event('change'));
    }

    // Auto-fermeture des alertes
    const alertEl = document.getElementById('alert');
    if (alertEl) setTimeout(() => new bootstrap.Alert(alertEl).close(), 4000);
});
</script>
</x-app-layout>