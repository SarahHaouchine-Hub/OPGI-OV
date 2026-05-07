<x-app-layout>

<style>
    .custom-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .card-header-gradient {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
        color: white;
        padding: 1rem;
    }
    .table-modern thead {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }
    .table-modern tbody tr { vertical-align: middle; }
    .table-modern tbody tr:hover {
        background-color: #f1f4f9;
        box-shadow: inset 4px 0 0 #2a5298;
    }
    .form-select, .form-control {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        transition: all 0.3s ease;
    }
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }
    .form-select:disabled {
        background-color: #f0f0f0;
        color: #aaa;
        cursor: not-allowed;
    }
    .form-label { color: #495057; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .btn-primary { border: none; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; transition: all 0.3s ease; }
    .btn-primary:hover { box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .btn-outline-secondary { border-radius: 8px; border-color: #6c757d; color: #6c757d; transition: all 0.3s ease; }
    .btn-outline-secondary:hover { background-color: #6c757d; border-color: #6c757d; color: white; }

    /* Spinner cascade */
    .sel-loading { position: relative; }
    .sel-loading::after {
        content: ''; position: absolute; right: 34px; top: 50%;
        transform: translateY(-50%); width: 14px; height: 14px;
        border: 2px solid #dee2e6; border-top-color: #2a5298;
        border-radius: 50%; animation: spin .6s linear infinite; pointer-events: none;
    }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

    /* Badges étapes */
    .step-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; border-radius: 50%; font-size: 10px; font-weight: 700;
        background-color: #6c757d; color: white; margin-right: 4px;
        flex-shrink: 0; transition: background-color .3s;
    }
    .step-badge.active { background-color: #1e3c72; }
    .step-badge.done   { background-color: #198754; }

    /* Modal */
    .modal-header-desist { background: linear-gradient(45deg, #dc3545, #b02a37); color: white; border-radius: 14px 14px 0 0; }
    .modal-content { border-radius: 14px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
    .modal-header .btn-close { filter: invert(1); }
    .price-tag { font-weight: 700; font-family: 'Monaco', monospace; color: #2d3436; }
</style>

<div class="py-4">
    <div class="container-fluid px-4">

        {{-- ── Alertes ── --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════
             FILTRES — cascade Site → Bâtiment → Étage → Porte
             ══════════════════════════════════════════════════════ --}}
        <div class="card custom-card mb-3">
            <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-funnel-fill me-2"></i> Filtres de recherche
                </h4>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDesistement">
                    <i class="bi bi-archive-fill me-1"></i> Liste des désistements
                </button>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('desistement') }}">
                    <div class="row g-3 align-items-end">

                        {{-- Recherche libre --}}
                        <div class="col-sm-12 col-md-6 col-lg-2">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1"></i> Code Logement
                            </label>
                            <input type="text" name="search" class="form-control"
                                   value="{{ request('search') }}" placeholder="Code Logement">
                        </div>

                        {{-- ① Site (depuis la BDD via $listSites) --}}
                        <div class="col-sm-12 col-md-6 col-lg-2">
                            <label class="form-label fw-semibold">
                                <span class="step-badge" id="sb1">1</span> Site
                            </label>
                            <select name="site_id" id="site_id" class="form-select">
                                <option value="">Tous les sites</option>
                                @foreach ($listSites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ② Bâtiment — chargé dynamiquement --}}
                        <div class="col-sm-12 col-md-6 col-lg-2">
                            <label class="form-label fw-semibold">
                                <span class="step-badge" id="sb2">2</span> Bâtiment
                            </label>
                            <div id="wrap_bat">
                                <select name="num_batiment" id="num_batiment" class="form-select"
                                    {{ request('site_id') ? '' : 'disabled' }}>
                                    <option value="">Tous les bâtiments</option>
                                    @foreach ($listBatiments as $bat)
                                        <option value="{{ $bat }}" {{ request('num_batiment') == $bat ? 'selected' : '' }}>
                                            Bâtiment {{ $bat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- ③ Étage — chargé dynamiquement --}}
                        <div class="col-sm-12 col-md-6 col-lg-2">
                            <label class="form-label fw-semibold">
                                <span class="step-badge" id="sb3">3</span> Étage
                            </label>
                            <div id="wrap_etage">
                                <select name="num_etage" id="num_etage" class="form-select"
                                    {{ request('num_batiment') ? '' : 'disabled' }}>
                                    <option value="">Tous les étages</option>
                                    @foreach ($listEtages as $etage)
                                        <option value="{{ $etage }}" {{ request('num_etage') == $etage ? 'selected' : '' }}>
                                            Étage {{ $etage }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- ④ Porte — chargée dynamiquement --}}
                        <div class="col-sm-12 col-md-6 col-lg-1">
                            <label class="form-label fw-semibold">
                                <span class="step-badge" id="sb4">4</span> Porte
                            </label>
                            <div id="wrap_porte">
                                <select name="num_porte" id="num_porte" class="form-select"
                                    {{ request('num_etage') ? '' : 'disabled' }}>
                                    <option value="">Toutes</option>
                                    @foreach ($listPortes as $porte)
                                        <option value="{{ $porte }}" {{ request('num_porte') == $porte ? 'selected' : '' }}>
                                            Porte {{ $porte }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Statut --}}
                        <div class="col-sm-12 col-md-6 col-lg-1">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-flag me-1"></i> Statut
                            </label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tous</option>
                                <option value="1"   {{ request('status') == '1'    ? 'selected' : '' }}>Inscrit</option>
                                <option value="2"   {{ request('status') == '2'    ? 'selected' : '' }}>Vendu</option>
                            </select>
                        </div>

                        {{-- Boutons --}}
                        <div class="col-sm-12 col-lg-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-search me-1"></i> Filtrer
                                </button>
                                <a href="{{ route('desistement') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════
             TABLEAU DES LOGEMENTS
             ══════════════════════════════════════════════════════ --}}
        <div class="card custom-card mb-2">
            <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                         class="bi bi-houses-fill me-2" viewBox="0 0 16 18">
                        <path d="M7.207 1a1 1 0 0 0-1.414 0L.146 6.646a.5.5 0 0 0 .708.708L1 7.207V12.5A1.5 1.5 0 0 0 2.5 14h.55a2.5 2.5 0 0 1-.05-.5V9.415a1.5 1.5 0 0 1-.56-2.475l5.353-5.354z"/>
                        <path d="M8.793 2a1 1 0 0 1 1.414 0L12 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l1.854 1.853a.5.5 0 0 1-.708.708L15 8.207V13.5a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 4 13.5V8.207l-.146.147a.5.5 0 1 1-.708-.708z"/>
                    </svg>
                    Liste des logements
                </h4>
                <span class="badge bg-secondary fs-6">{{ $logements->total() }} résultat(s)</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr class="text-center">
                                <th>N°</th>
                                <th>Code Logement</th>
                                <th>Nom et Prénom du Souscripteur</th>
                                <th>N° Bâtiment</th>
                                <th>N° Étage</th>
                                <th>N° Porte</th>
                                <th>Site</th>
                                <th>État</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logements as $logement)
                                <tr class="text-center">
                                    <td class="text-muted">{{ $logements->firstItem() + $loop->index }}</td>
                                    <td class="fw-bold">{{ $logement->code_loge_lpl }}</td>
                                    <td>
                                        {{ strtoupper($logement->souscripteur->nom    ?? '') }}
                                        {{ strtoupper($logement->souscripteur->prenom ?? '') }}
                                    </td>
                                    <td>{{ $logement->num_batiment }}</td>
                                    <td>{{ $logement->num_etage }}</td>
                                    <td>{{ $logement->num_porte }}</td>
                                    {{-- ✅ Site lu depuis la relation BDD --}}
                                    <td>{{ $logement->site->libelle ?? '—' }}</td>
                                    <td>
                                        @if ($logement->flag == '1')
                                            <span class="badge bg-info">Inscrit</span>
                                        @elseif ($logement->flag == '2')
                                            <span class="badge bg-success">Vendu</span>
                                        @else
                                            <span class="badge bg-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('createDesistement', Hashids::encode($logement->id)) }}" method="POST">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="Swal.fire({
                                                    title: 'Confirmer le désistement ?',
                                                    text: 'Cette action est irréversible.',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Oui, désister',
                                                    cancelButtonText: 'Annuler'
                                                }).then(r => { if (r.isConfirmed) this.closest('form').submit(); })">
                                                <i class="bi bi-x-circle me-1"></i> Désister
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                            <h5>Aucun logement trouvé</h5>
                                            <p class="small mb-0">Essayez de modifier vos filtres de recherche</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($logements->hasPages())
            <div class="d-flex justify-content-start mt-2">
                {{ $logements->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL — Historique des désistements (filtre JS côté client)
     ══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDesistement" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width:95vw">
        <div class="modal-content">

            <div class="modal-header modal-header-desist" style="padding:1.1rem 1.5rem">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-archive-fill me-2"></i> Historique des désistements
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <input type="text" id="desist_search" class="form-control form-control-sm"
                       placeholder="🔍 Rechercher un souscripteur..." style="min-width:240px">
                <span class="badge bg-secondary" id="desist_count">{{ $desistements->total() }} résultat(s)</span>
            </div>

            <div class="modal-body p-0">
                <div style="max-height:60vh; overflow-y:auto">
                    <table class="table table-modern table-hover mb-0">
                        <thead style="position:sticky;top:0;z-index:2;background:#f1f4f9;
                                      text-transform:uppercase;font-size:0.75rem;letter-spacing:0.5px">
                            <tr class="text-center">
                                <th>N°</th>
                                <th>Code Logement</th>
                                <th>Souscripteur</th>
                                <th>Bât / Étage / Porte</th>
                                <th>Site</th>
                                <th>Date Désistement</th>
                                <th>Prix Logement</th>
                                <th>Total Payé</th>
                            </tr>
                        </thead>
                        <tbody id="desist_tbody">
                            @forelse ($desistements as $desistement)
                                <tr class="text-center desist-row"
                                    data-souscripteur="{{ strtolower(($desistement->souscripteur->nom ?? '').' '.($desistement->souscripteur->prenom ?? '')) }}">
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $desistement->code_loge_lpl }}</td>
                                    <td>
                                        {{ strtoupper($desistement->souscripteur->nom    ?? '') }}
                                        {{ strtoupper($desistement->souscripteur->prenom ?? '') }}
                                    </td>
                                    <td>
                                        Bât.&nbsp;{{ $desistement->logement->num_batiment ?? '—' }} —
                                        Ét.&nbsp;{{ $desistement->logement->num_etage    ?? '—' }} —
                                        Porte&nbsp;{{ $desistement->logement->num_porte  ?? '—' }}
                                    </td>
                                    {{-- ✅ Site lu depuis la relation BDD --}}
                                    <td>{{ $desistement->logement->site->libelle ?? '—' }}</td>
                                    <td class="text-danger fw-bold">
                                        {{ \Carbon\Carbon::parse($desistement->date_desistement)->format('d/m/Y') }}
                                    </td>
                                    <td class="price-tag">
                                        {{ number_format($desistement->logement->prix ?? 0, 2, ',', ' ') }} DA
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ number_format($desistement->souscripteur->ovs->sum('montant_paye'), 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                            <h5>Aucun désistement enregistré</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light justify-content-between">
                <small class="text-muted fst-italic">
                    <i class="bi bi-info-circle me-1"></i> Utilisez la recherche pour filtrer les résultats.
                </small>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const $ = id => document.getElementById(id);

    // ── Helpers ────────────────────────────────────────────────────────────
    function badge(id, state) {
        const el = $(id); if (!el) return;
        el.classList.remove('active','done');
        if (state === 'active') el.classList.add('active');
        if (state === 'done')   { el.classList.add('done'); el.textContent = '✓'; }
    }
    function resetSel(sel, msg) { sel.innerHTML = `<option value="">${msg}</option>`; sel.disabled = true; }
    function spin(wrapId, on)   { const el=$(wrapId); if(el) el.classList.toggle('sel-loading', on); }

    async function loadInto(url, sel, wrapId, buildOption) {
        spin(wrapId, true);
        try {
            const data = await fetch(url).then(r => r.json());
            if (!data.length) { sel.innerHTML = '<option value="">Aucun résultat</option>'; return false; }
            data.forEach(item => { const o = document.createElement('option'); buildOption(o, item); sel.appendChild(o); });
            sel.disabled = false;
            return true;
        } catch (e) { console.error(e); return false; }
        finally { spin(wrapId, false); }
    }

    // ── Éléments filtres ───────────────────────────────────────────────────
    const selSite  = $('site_id');
    const selBat   = $('num_batiment');
    const selEtage = $('num_etage');
    const selPorte = $('num_porte');

    // ① Site → Bâtiment
    selSite.addEventListener('change', async function () {
        resetSel(selBat,   'Tous les bâtiments');
        resetSel(selEtage, 'Tous les étages');
        resetSel(selPorte, 'Toutes les portes');

        if (!this.value) { badge('sb1','active'); return; }

        const ok = await loadInto(
            `/api/souscripteur/batiments/${this.value}`,
            selBat, 'wrap_bat',
            (o, b) => { o.value = b; o.textContent = 'Bâtiment ' + b; }
        );
        if (ok) { badge('sb1','done'); badge('sb2','active'); }
    });

    // ② Bâtiment → Étage
    selBat.addEventListener('change', async function () {
        resetSel(selEtage, 'Tous les étages');
        resetSel(selPorte, 'Toutes les portes');

        if (!this.value) { badge('sb2','active'); return; }

        const ok = await loadInto(
            `/api/souscripteur/etages/${selSite.value}/${this.value}`,
            selEtage, 'wrap_etage',
            (o, e) => { o.value = e; o.textContent = 'Étage ' + e; }
        );
        if (ok) { badge('sb2','done'); badge('sb3','active'); }
    });

    // ③ Étage → Porte
    selEtage.addEventListener('change', async function () {
        resetSel(selPorte, 'Toutes les portes');

        if (!this.value) { badge('sb3','active'); return; }

        const ok = await loadInto(
            `/api/souscripteur/portes/${selSite.value}/${selBat.value}/${this.value}`,
            selPorte, 'wrap_porte',
            (o, p) => { o.value = p.num_porte; o.textContent = 'Porte ' + p.num_porte; }
        );
        if (ok) { badge('sb3','done'); badge('sb4','active'); }
    });

    selPorte.addEventListener('change', function () {
        if (this.value) badge('sb4','done');
    });

    // ── Restaurer les badges si filtres déjà actifs (après submit) ─────────
    if (selSite.value)  { badge('sb1','done'); selBat.disabled  = false; }
    if (selBat.value)   { badge('sb2','done'); selEtage.disabled = false; }
    if (selEtage.value) { badge('sb3','done'); selPorte.disabled = false; }
    if (selPorte.value) { badge('sb4','done'); }

    // ── Filtre JS dans le modal (sans rechargement de page) ────────────────
    const searchInput = $('desist_search');
    const countBadge  = $('desist_count');
    const rows        = document.querySelectorAll('#desist_tbody .desist-row');

    if (searchInput && rows.length) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            let visible = 0;
            rows.forEach(row => {
                const show = !q || row.dataset.souscripteur.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            countBadge.textContent = visible + ' résultat(s)';
        });
    }

    // ── Auto-fermeture des alertes ──────────────────────────────────────────
    const alertEl = $('alert');
    if (alertEl) setTimeout(() => new bootstrap.Alert(alertEl).close(), 3000);
});
</script>

</x-app-layout>