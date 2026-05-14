<x-app-layout>
<style>
    /* ── BASE ── */
    .custom-card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
    .card-header-gradient { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; padding: 1rem; }
    .card-header-green    { background: linear-gradient(45deg, #198754, #28a745); color: white; padding: 1rem; }
    .table-modern thead { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
    .table-modern tbody tr { vertical-align: middle; }
    .table-modern tbody tr:hover { background-color: #f1f4f9; box-shadow: inset 4px 0 0 #2a5298; }
    .form-select, .form-control { border: 1px solid #dee2e6; border-radius: 8px; padding: 0.5rem 0.75rem; transition: all 0.3s ease; }
    .form-select:focus, .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.25rem rgba(102,126,234,0.25); }
    .form-select:disabled { background-color: #f0f0f0; color: #aaa; cursor: not-allowed; }
    .form-label { color: #495057; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .btn-primary { border: none; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; transition: all 0.3s ease; }
    .btn-primary:hover { box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
    .btn-outline-secondary { border-radius: 8px; border-color: #6c757d; color: #6c757d; transition: all 0.3s ease; }
    .btn-outline-secondary:hover { background-color: #6c757d; border-color: #6c757d; color: white; }

    /* ── STAT CARDS ROW 1 ── */
    #cardstat1 { background-color: #1e3c72; transition: all 0.3s ease; cursor: pointer; }
    #cardstat1:hover { background-color: #152a52 !important; box-shadow: 0 4px 12px rgba(206,214,248,0.4); transform: translateY(-1px); }
    #cardstat2 { background-color: #1e3c72; transition: all 0.3s ease; cursor: pointer; }
    #cardstat2:hover { background-color: rgb(9,82,189) !important; box-shadow: 0 4px 12px rgba(206,214,248,0.4); transform: translateY(-1px); }
    #cardstat3 { transition: all 0.3s ease; cursor: pointer; }
    #cardstat3:hover { background-color: rgb(5,142,170) !important; box-shadow: 0 4px 12px rgba(206,214,248,0.4); transform: translateY(-1px); }
    #cardstat4 { transition: all 0.3s ease; cursor: pointer; }
    #cardstat4:hover { background-color: rgb(23,98,63) !important; box-shadow: 0 4px 12px rgba(206,214,248,0.4); transform: translateY(-1px); }
    #cardstat5 { transition: all 0.3s ease; cursor: pointer; }
    #cardstat5:hover { background-color: rgb(163,28,41) !important; box-shadow: 0 4px 12px rgba(206,214,248,0.4); transform: translateY(-1px); }

    /* ── STAT CARDS ROW 2 (nouvelles) ── */
    .stat-card-new { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.07); overflow: hidden; transition: all 0.3s ease; cursor: pointer; }
    .stat-card-new:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }

    #cardstat_bnh { background: linear-gradient(135deg, #6f42c1, #8b5cf6); }
    #cardstat_bnh:hover { background: linear-gradient(135deg, #5a32a3, #7c3aed) !important; }

    #cardstat_ov_paye { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
    #cardstat_ov_paye:hover { background: linear-gradient(135deg, #0284c7, #0369a1) !important; }

    #cardstat_ov_npaye { background: linear-gradient(135deg, #f59e0b, #d97706); }
    #cardstat_ov_npaye:hover { background: linear-gradient(135deg, #d97706, #b45309) !important; }

    #cardstat_vsp { background: linear-gradient(135deg, #10b981, #059669); }
    #cardstat_vsp:hover { background: linear-gradient(135deg, #059669, #047857) !important; }

    .stat-card-new .card-body { padding: 1rem; }
    .stat-card-new h6 { font-size: 0.72rem; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 0.3rem; }
    .stat-card-new h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 0; }
    .stat-card-new .stat-sub { font-size: 0.7rem; opacity: 0.75; margin-top: 2px; }

    /* ── Séparateur section stats ── */
    .stats-section-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.4rem;
        padding-left: 2px;
    }

    /* ── BUTTONS ── */
    .btn-action-site { background: linear-gradient(45deg,#1e3c72,#2a5298); border:none; border-radius:10px; color:white; font-weight:600; padding:10px 20px; transition:all 0.3s ease; box-shadow:0 4px 12px rgba(30,60,114,0.3); }
    .btn-action-site:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(30,60,114,0.4); color:white; }
    .btn-action-logement { background: linear-gradient(45deg,#198754,#28a745); border:none; border-radius:10px; color:white; font-weight:600; padding:10px 20px; transition:all 0.3s ease; box-shadow:0 4px 12px rgba(25,135,84,0.3); }
    .btn-action-logement:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(25,135,84,0.4); color:white; }
    .btn-voir { background: linear-gradient(45deg,#0d6efd,#0a58ca); border:none; border-radius:8px; color:white; font-size:0.8rem; font-weight:600; padding:5px 14px; transition:all 0.3s ease; white-space:nowrap; }
    .btn-voir:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(13,110,253,0.4); color:white; }

    /* ── MODAL STEPS ── */
    .step-badge { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; font-size:11px; font-weight:700; background-color:#6c757d; color:white; margin-right:5px; flex-shrink:0; transition:background-color .3s; }
    .step-badge.active { background-color:#1e3c72; }
    .step-badge.done   { background-color:#198754; }
    .sel-loading { position:relative; }
    .sel-loading::after { content:''; position:absolute; right:34px; top:50%; transform:translateY(-50%); width:14px; height:14px; border:2px solid #dee2e6; border-top-color:#2a5298; border-radius:50%; animation:spin .6s linear infinite; pointer-events:none; }
    @keyframes spin { to { transform:translateY(-50%) rotate(360deg); } }

    /* ── MODAL LOGEMENTS ── */
    .modal-header-site     { background: linear-gradient(45deg,#1e3c72,#2a5298); color:white; border-radius:14px 14px 0 0; }
    .modal-header-logement { background: linear-gradient(45deg,#198754,#28a745); color:white; border-radius:14px 14px 0 0; }
    .modal-content { border-radius:14px; border:none; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
    .modal-header .btn-close { filter:invert(1); }

    /* ── BADGE STATUT ── */
    .badge-libre   { background:#0d6efd; color:white; border-radius:20px; padding:3px 10px; font-size:0.72rem; font-weight:600; white-space:nowrap; }
    .badge-inscrit { background:#0dcaf0; color:white; border-radius:20px; padding:3px 10px; font-size:0.72rem; font-weight:600; white-space:nowrap; }
    .badge-vendu   { background:#198754; color:white; border-radius:20px; padding:3px 10px; font-size:0.72rem; font-weight:600; white-space:nowrap; }
    .badge-desiste { background:#dc3545; color:white; border-radius:20px; padding:3px 10px; font-size:0.72rem; font-weight:600; white-space:nowrap; }

    /* ── BTN SOUSCRIPTEUR ── */
    .btn-souscripteur { background:linear-gradient(45deg,#198754,#28a745); border:none; border-radius:8px; color:white; font-size:0.72rem; font-weight:600; padding:4px 12px; transition:all 0.2s; white-space:nowrap; }
    .btn-souscripteur:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(25,135,84,0.4); color:white; }
    .btn-souscripteur:disabled { background:#adb5bd; cursor:not-allowed; transform:none; box-shadow:none; color:white; }

    /* ── POPUP LOGEMENTS TABLE ── */
    #modalLogementsSite .modal-body-inner { padding:0; }
    #modalLogementsSite .filtres-bar { background:#f8f9fa; padding:0.75rem 1rem; border-bottom:1px solid #e9ecef; }
    #modalLogementsSite .table-zone { max-height: 420px; overflow-y: auto; }
    #modalLogementsSite table thead { position:sticky; top:0; z-index:2; background:#f1f4f9; }
    #modalLogementsSite table { font-size:0.83rem; }
    .pag-bar { background:#f8f9fa; border-top:1px solid #e9ecef; padding:0.5rem 1rem; display:flex; align-items:center; justify-content:space-between; font-size:0.82rem; }

    /* ── POPUP SOUSCRIPTEUR ── */
    .info-block { background:#f8f9fa; border-radius:10px; padding:0.85rem 1rem; height:100%; }
    .info-label { font-size:0.7rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px; }
    .info-value { font-size:0.9rem; font-weight:600; color:#212529; }
    .logement-recap-bar { background:linear-gradient(135deg,#1e3c72,#2a5298); color:white; border-radius:10px; padding:0.75rem 1rem; margin-bottom:1rem; display:flex; gap:1rem; flex-wrap:wrap; align-items:center; font-size:0.83rem; }
    .logement-recap-bar .litem { background:rgba(255,255,255,0.15); border-radius:20px; padding:2px 12px; }
    .no-souscripteur { text-align:center; padding:2rem; color:#adb5bd; }
    .no-souscripteur i { font-size:3rem; display:block; margin-bottom:0.5rem; }
</style>

<div class="py-4">
<div class="container-fluid px-4">

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

{{-- ─── STATISTIQUES — LIGNE 1 : Logements ──────────────────────────────── --}}
<div class="stats-section-label"><i class="bi bi-houses me-1"></i> Logements</div>
<div class="row mb-3 g-2">
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card text-white shadow-sm border-0" id="cardstat1">
            <div class="card-body py-3 px-3">
                <h6 class="text-uppercase small mb-1" style="font-size:0.7rem;opacity:0.85">Total Logements</h6>
                <h2 class="mb-0 fw-bold">{{ $totalLogements }}</h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card bg-primary text-white shadow-sm border-0" id="cardstat2">
            <div class="card-body py-3 px-3">
                <h6 class="text-uppercase small mb-1" style="font-size:0.7rem;opacity:0.85">Libres</h6>
                <h2 class="mb-0 fw-bold">{{ $libres }}</h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="card bg-info text-white shadow-sm border-0" id="cardstat3">
            <div class="card-body py-3 px-3">
                <h6 class="text-uppercase small mb-1" style="font-size:0.7rem;opacity:0.85">Affectés</h6>
                <h2 class="mb-0 fw-bold">{{ $inscrits }}</h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        {{-- Anciennement "Vendus" → renommé "Soldés" --}}
        <div class="card bg-success text-white shadow-sm border-0" id="cardstat4">
            <div class="card-body py-3 px-3">
                <h6 class="text-uppercase small mb-1" style="font-size:0.7rem;opacity:0.85">Soldés</h6>
                <h2 class="mb-0 fw-bold">{{ $soldes }}</h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        {{-- Anciennement "Désistés" → renommé "Remplacés" --}}
        <div class="card bg-danger text-white shadow-sm border-0" id="cardstat5">
            <div class="card-body py-3 px-3">
                <h6 class="text-uppercase small mb-1" style="font-size:0.7rem;opacity:0.85">Remplacés</h6>
                <h2 class="mb-0 fw-bold">{{ $remplaces }}</h2>
            </div>
        </div>
    </div>
</div>

{{-- ─── STATISTIQUES — LIGNE 2 : Nouvelles stats ───────────────────────── --}}
<div class="stats-section-label mt-1"><i class="bi bi-bar-chart-fill me-1"></i> Suivi BNH / OV / VSP</div>
<div class="row mb-4 g-2">

    {{-- Décision BNH --}}
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card-new text-white" id="cardstat_bnh">
            <div class="card-body">
                <h6 class="text-uppercase"><i class="bi bi-bank me-1"></i> Décision BNH</h6>
                <h2>{{ $decisionBnh }}</h2>
                <div class="stat-sub">Souscripteurs avec décision BNH</div>
            </div>
        </div>
    </div>

    {{-- OV Payées --}}
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card-new text-white" id="cardstat_ov_paye">
            <div class="card-body">
                <h6 class="text-uppercase"><i class="bi bi-check2-circle me-1"></i> OV Payées</h6>
                <h2>{{ $ovPayees }}</h2>
                <div class="stat-sub">Ordres de virement réglés</div>
            </div>
        </div>
    </div>

    {{-- OV Non Payées --}}
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card-new text-white" id="cardstat_ov_npaye">
            <div class="card-body">
                <h6 class="text-uppercase"><i class="bi bi-hourglass-split me-1"></i> OV Non Payées</h6>
                <h2>{{ $ovNonPayees }}</h2>
                <div class="stat-sub">Ordres de virement en attente</div>
            </div>
        </div>
    </div>

    {{-- VSP Total --}}
    <div class="col-6 col-sm-6 col-md-3">
        <div class="card stat-card-new text-white" id="cardstat_vsp"
             data-bs-toggle="modal" data-bs-target="#modalVspProjet"
             title="Voir le détail par projet">
            <div class="card-body">
                <h6 class="text-uppercase"><i class="bi bi-list-ol me-1"></i> Total VSP</h6>
                <h2>{{ $totalVsp }}</h2>
                <div class="stat-sub">Ventes sur plan — <span style="text-decoration:underline;opacity:0.9">Détail par projet ›</span></div>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     LISTE DES PROJETS
     ══════════════════════════════════════════════════════════════════════════ --}}

{{-- Filtres projets --}}
<div class="card custom-card mb-3">
    <div class="card-header card-header-gradient">
        <h4 class="mb-0">
            <i class="bi bi-funnel-fill me-2"></i> Filtres de recherche
        </h4>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('dashboard') }}">
            <div class="row g-3 align-items-end">

                <div class="col-sm-12 col-md-4 col-lg-3">
                    <label class="form-label fw-semibold"><span class="step-badge active" id="fb1">1</span> Wilaya</label>
                    <select name="wilaya_id" id="f_wilaya" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($wilayas as $w)
                            <option value="{{ $w->id }}" {{ request('wilaya_id') == $w->id ? 'selected' : '' }}>{{ $w->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-3">
                    <label class="form-label fw-semibold"><span class="step-badge" id="fb2">2</span> Programme</label>
                    <div id="fwrap_prog">
                        <select name="programme_id" id="f_programme" class="form-select" {{ request('wilaya_id') ? '' : 'disabled' }}>
                            <option value="">Tous</option>
                            @foreach($programmes as $p)
                                <option value="{{ $p->id }}" {{ request('programme_id') == $p->id ? 'selected' : '' }}>{{ $p->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-3">
                    <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i> Libellé du projet</label>
                    <input type="text" name="search" class="form-control" placeholder="Rechercher un projet..." value="{{ request('search') }}">
                </div>

                <div class="col-sm-12 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filtrer</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bi bi-x-lg"></i></a>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

{{-- Tableau projets --}}
<div class="card custom-card mb-2">
    <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="bi bi-geo-alt-fill me-2"></i> Liste des projets
        </h4>
        <span class="badge bg-secondary fs-6">{{ $sitesPaginated->total() }} projet(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern table-hover mb-0">
                <thead>
                    <tr class="text-center">
                        <th>N°</th>
                        <th>Programme</th>
                        <th>Wilaya</th>
                        <th>Projet</th>
                        <th>Nb Logements</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sitesPaginated as $site)
                        <tr class="text-center">
                            <td class="text-muted">{{ $sitesPaginated->firstItem() + $loop->index }}</td>
                            <td>{{ $site->programme->libelle ?? 'N/A' }}</td>
                            <td>{{ $site->wilaya->nom ?? 'N/A' }}</td>
                            <td class="fw-semibold">{{ $site->libelle }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $site->logements->count() }}</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-voir"
                                    onclick="ouvrirLogements(
                                        {{ $site->id }},
                                        '{{ addslashes($site->libelle) }}',
                                        '{{ addslashes($site->programme->libelle ?? 'N/A') }}',
                                        '{{ addslashes($site->wilaya->nom ?? 'N/A') }}'
                                    )">
                                    <i class="bi bi-eye me-1"></i> Voir logements
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-geo-alt display-4 d-block mb-3 opacity-50"></i>
                                <h5>Aucun projet trouvé</h5>
                                <p class="small mb-0">Essayez de modifier vos filtres</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($sitesPaginated->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-start">{{ $sitesPaginated->links('pagination::bootstrap-5') }}</div>
    </div>
@endif

{{-- ─── BOUTONS D'ACTION EN BAS ─────────────────────────────────────────── --}}
<div class="d-flex justify-content-center gap-3 mt-4 pt-2 border-top">
    <button class="btn btn-action-site px-4" data-bs-toggle="modal" data-bs-target="#modalSite">
        <i class="bi bi-geo-alt-fill me-2"></i> Ajouter un nouveau projet
    </button>
    <button class="btn btn-action-logement px-4" data-bs-toggle="modal" data-bs-target="#modalLogement">
        <i class="bi bi-house-fill me-2"></i> Ajouter un nouveau logement
    </button>
</div>

</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — VSP PAR PROJET
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalVspProjet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#10b981,#059669);color:white;border-radius:14px 14px 0 0;padding:1.1rem 1.5rem">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-list-ol me-2"></i> VSP par projet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-0">
                <div style="max-height:460px;overflow-y:auto">
                    <table class="table table-hover mb-0" style="font-size:0.85rem">
                        <thead style="position:sticky;top:0;z-index:2;background:#f1f4f9;text-transform:uppercase;font-size:0.72rem;letter-spacing:0.5px">
                            <tr>
                                <th class="ps-3">N°</th>
                                <th>Projet</th>
                                <th class="text-center">Nb VSP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vspParProjet->where('vsp_count', '>', 0) as $index => $projet)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $projet->libelle }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill" style="background:#10b981;font-size:0.8rem;padding:4px 12px">
                                            {{ $projet->vsp_count }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox d-block fs-3 mb-2 opacity-50"></i>
                                        Aucun VSP enregistré
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="background:#f8f9fa;border-top:1px solid #e9ecef;padding:0.6rem 1rem;font-size:0.82rem" class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total général</span>
                    <span class="badge" style="background:#10b981;font-size:0.85rem;padding:4px 14px">{{ $totalVsp }} VSP</span>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e9ecef;padding:0.6rem 1rem">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL 1 — LOGEMENTS DU PROJET (popup)
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalLogementsSite" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width:95vw">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header modal-header-site" style="border-radius:14px 14px 0 0; padding:1.1rem 1.5rem">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="ml_title">
                        <i class="bi bi-houses-fill me-2"></i> Logements
                    </h5>
                    <div style="font-size:0.8rem;opacity:0.85" id="ml_meta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>

            {{-- Filtres inline --}}
            <div class="filtres-bar" style="background:#f8f9fa;padding:0.75rem 1rem;border-bottom:1px solid #e9ecef">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-sm-4 col-md-3">
                        <input type="text" id="ml_search" class="form-control form-control-sm"
                            placeholder="🔍 Nom / Prénom du souscripteur...">
                    </div>
                    <div class="col-6 col-sm-3 col-md-2">
                        <select id="ml_batiment" class="form-select form-select-sm">
                            <option value="">Tous bâtiments</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-3 col-md-2">
                        <select id="ml_etage" class="form-select form-select-sm" disabled>
                            <option value="">Tous étages</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <select id="ml_status" class="form-select form-select-sm">
                            <option value="">Tous statuts</option>
                            <option value="0">Libre</option>
                            <option value="1">Inscrit</option>
                            <option value="2">Soldé</option>
                            <option value="3">Remplacé</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-auto">
                        <span id="ml_count" class="badge bg-secondary">0 logement(s)</span>
                    </div>
                    <div class="col-auto ms-auto">
                        <button class="btn btn-sm btn-outline-secondary" onclick="mlResetFiltres()">
                            <i class="bi bi-x-lg"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="modal-body p-0">
                <div style="max-height:420px;overflow-y:auto">
                    <table class="table table-hover mb-0" style="font-size:0.83rem">
                        <thead style="position:sticky;top:0;z-index:2;background:#f1f4f9;text-transform:uppercase;font-size:0.72rem;letter-spacing:0.5px">
                            <tr class="text-center">
                                <th>N°</th>
                                <th>Bâtiment</th>
                                <th>Étage</th>
                                <th>Porte</th>
                                <th>Lot</th>
                                <th>Typologie</th>
                                <th>Surface</th>
                                <th>Prix (DA)</th>
                                <th>État</th>
                                <th>Souscripteur</th>
                            </tr>
                        </thead>
                        <tbody id="ml_tbody">
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div class="spinner-border text-primary mb-2" role="status"></div>
                                    <div>Chargement des logements...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="pag-bar">
                    <span id="ml_page_info" class="text-muted"></span>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" id="ml_prev" onclick="mlChangePage(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span id="ml_page_num" class="fw-semibold text-muted" style="font-size:0.82rem"></span>
                        <button class="btn btn-sm btn-outline-secondary" id="ml_next" onclick="mlChangePage(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL 2 — INFOS SOUSCRIPTEUR (popup sur popup)
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalSouscripteur" tabindex="-1" aria-hidden="true" style="z-index:1060">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-logement" style="border-radius:14px 14px 0 0;padding:1.1rem 1.5rem">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-vcard-fill me-2"></i> Informations du souscripteur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button>
            </div>
            <div class="modal-body p-3" id="souscripteur_body">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="mt-2">Chargement...</div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e9ecef;padding:0.6rem 1rem">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Fermer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─── MODAL PROJET ──────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalSite" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-site">
                <h5 class="modal-title fw-bold"><i class="bi bi-geo-alt-fill me-2"></i> Ajouter un nouveau projet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('site.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-map me-1"></i> Wilaya</label>
            <select name="wilaya_id" id="modal_wilaya" class="form-select" required>
                <option value="">-- Choisir une wilaya --</option>
                @foreach($wilayas as $wilaya)
                    <option value="{{ $wilaya->id }}">{{ $wilaya->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-pin-map me-1"></i> Commune</label>
            <select name="commune_id" id="modal_commune" class="form-select" required disabled>
                <option value="">-- Choisir d'abord une wilaya --</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold"><i class="bi bi-grid me-1"></i> Programme</label>
            <select name="programme_id" class="form-select" required>
                <option value="">-- Choisir un programme --</option>
                @foreach($programmes as $programme)
                    <option value="{{ $programme->id }}">{{ $programme->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold"><i class="bi bi-tag me-1"></i> Libellé du projet</label>
            <input type="text" name="libelle" class="form-control" placeholder="Ex : Cité des 500 logements" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-file-earmark-text me-1"></i> N° Convention BNH</label>
            <input type="text" name="num_convention_bnh" class="form-control" placeholder="Ex : CONV-2025-001">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-building me-1"></i> Nom de l'agence</label>
            <input type="text" name="nom_agence" class="form-control" placeholder="Ex : Agence El Djazair">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-hash me-1"></i> N° Agence</label>
            <input type="text" name="num_agence" class="form-control" placeholder="Ex : AG-007">
        </div>

        {{-- ← NOUVEAU --}}
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-credit-card me-1"></i> N° Compte agence</label>
            <input type="text" name="num_compte_agence" class="form-control" placeholder="Ex : 00012345678901234567">
        </div>

        {{-- ← NOUVEAU --}}
        <div class="col-12">
            <label class="form-label fw-semibold"><i class="bi bi-geo me-1"></i> Adresse agence</label>
            <input type="text" name="adresse_agence" class="form-control" placeholder="Ex : 12 Rue Didouche Mourad, Alger">
        </div>
    </div>
</div>
              <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-action-site"><i class="bi bi-check-circle me-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── MODAL LOGEMENT ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="modalLogement" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-logement">
                <h5 class="modal-title fw-bold"><i class="bi bi-house-fill me-2"></i> Ajouter un nouveau logement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('logement.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold"><span class="step-badge active" id="mb1">1</span> Wilaya</label>
            <select id="log_wilaya" class="form-select" required>
                <option value="">-- Choisir une wilaya --</option>
                @foreach($wilayas as $w)
                    <option value="{{ $w->id }}">{{ $w->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><span class="step-badge" id="mb2">2</span> Programme</label>
            <select id="log_programme" name="programme_id" class="form-select" required disabled>
                <option value="">-- Choisir d'abord une wilaya --</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold"><span class="step-badge" id="mb3">3</span> Projet</label>
            <div id="mwrap_site">
                <select name="site_id" id="log_site" class="form-select" required disabled>
                    <option value="">-- Choisir d'abord un programme --</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-building me-1"></i> Bâtiment</label>
            <input type="text" name="num_batiment" class="form-control" placeholder="Ex : A, B, C" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-layers me-1"></i> Étage</label>
            <input type="number" name="num_etage" class="form-control" placeholder="Ex : 1" min="0" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-door-closed me-1"></i> N° Porte</label>
            <input type="number" name="num_porte" class="form-control" placeholder="Ex : 12" min="1" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-hash me-1"></i> N° Lot</label>
            <input type="text" name="num_lot" class="form-control" placeholder="Ex : LOT-001">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-rulers me-1"></i> Surface (m²)</label>
            <input type="number" step="0.01" name="surface" class="form-control" placeholder="Ex : 85.50" min="0">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-layout-text-window me-1"></i> Typologie</label>
            <select name="typologie" class="form-select">
                <option value="">-- Choisir --</option>
                <option value="F2">F2</option>
                <option value="F3">F3</option>
                <option value="F4">F4</option>
                <option value="F5">F5</option>
                <option value="F6">F6</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-cash-stack me-1"></i> Prix (DA)</label>
            <input type="number" step="0.01" name="prix" class="form-control" placeholder="Ex : 5000000" min="0" required>
        </div>
    </div>
</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-action-logement"><i class="bi bi-check-circle me-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     JAVASCRIPT
     ══════════════════════════════════════════════════════════════════════════ --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const $ = id => document.getElementById(id);

    // ─── Helpers ──────────────────────────────────────────────────────────
    function resetSel(sel, msg) {
        if (!sel) return;
        sel.innerHTML = `<option value="">${msg}</option>`;
        sel.disabled = true;
    }

    function spin(wrapId, on) {
        const el = wrapId ? $(wrapId) : null;
        if (el) el.classList.toggle('sel-loading', on);
    }

    function badge(id, state) {
        const el = $(id);
        if (!el) return;
        el.classList.remove('active','done');
        if (state === 'active') el.classList.add('active');
        if (state === 'done')   { el.classList.add('done'); el.textContent = '✓'; }
    }

    async function loadInto(url, sel, wrapId, buildOption) {
        spin(wrapId, true);
        try {
            const data = await fetch(url).then(r => r.json());
            if (!data.length) { sel.innerHTML = '<option value="">Aucun résultat</option>'; return false; }
            data.forEach(item => {
                const o = document.createElement('option');
                buildOption(o, item);
                sel.appendChild(o);
            });
            sel.disabled = false;
            return true;
        } catch(e) { console.error(e); return false; }
        finally { spin(wrapId, false); }
    }

    function setDefaultAlger(selectEl) {
        if (!selectEl) return;
        const options = Array.from(selectEl.options);
        const algerOption = options.find(opt => opt.textContent.trim() === 'Alger');
        if (algerOption && !selectEl.value) {
            selectEl.value = algerOption.value;
            return true;
        }
        return false;
    }

    // ─── FILTRES PROJETS EN CASCADE ──────────────────────────────────────
    const fWilaya = $("f_wilaya");
    const fProg   = $("f_programme");

    if (fWilaya && fProg) {
        const onFWilayaChange = async function () {
            resetSel(fProg, "Tous");
            if (!this.value) { badge('fb1','active'); return; }
            fProg.innerHTML = '<option value="">Tous</option>';
            const ok = await loadInto(
                `/api/souscripteur/programmes-by-wilaya/${this.value}`,
                fProg, 'fwrap_prog',
                (o,p) => { o.value=p.id; o.textContent=p.libelle; }
            );
            if (ok) { badge('fb1','done'); badge('fb2','active'); }
        };
        fWilaya.addEventListener('change', onFWilayaChange);

        if (!fWilaya.value) {
            if (setDefaultAlger(fWilaya)) {
                fWilaya.dispatchEvent(new Event('change'));
            }
        } else {
            if (fWilaya.value) {
                fWilaya.dispatchEvent(new Event('change'));
            }
        }
    }

   // ─── MODAL LOGEMENT — Wilaya → Programme → Projet ────────────────────
const logWilaya   = $("log_wilaya");
const logProg     = $("log_programme");
const logSite     = $("log_site");

if (logWilaya && logProg && logSite) {

    // Wilaya change → charger les programmes
    logWilaya.addEventListener('change', async function () {
        resetSel(logProg, "-- Choisir d'abord une wilaya --");
        resetSel(logSite, "-- Choisir d'abord un programme --");
        badge('mb1', 'active'); badge('mb2', 'pending'); badge('mb3', 'pending');
        if (!this.value) return;

        logProg.innerHTML = '<option value="">-- Choisir un programme --</option>';
        const ok = await loadInto(
            `/api/souscripteur/programmes-by-wilaya/${this.value}`,
            logProg, null,
            (o, p) => { o.value = p.id; o.textContent = p.libelle; }
        );
        if (ok) { badge('mb1', 'done'); badge('mb2', 'active'); }
    });

    // Programme change → charger les projets de ce programme dans cette wilaya
    logProg.addEventListener('change', async function () {
        resetSel(logSite, "-- Choisir d'abord un programme --");
        badge('mb2', 'active'); badge('mb3', 'pending');
        if (!this.value) return;

        logSite.innerHTML = '<option value="">-- Choisir un projet --</option>';
        const ok = await loadInto(
            `/api/souscripteur/sites/${logWilaya.value}/${this.value}`,
            logSite, 'mwrap_site',
            (o, s) => { o.value = s.id; o.textContent = s.libelle; }
        );
        if (ok) { badge('mb2', 'done'); badge('mb3', 'active'); }
    });

    logSite.addEventListener('change', function () {
        if (this.value) badge('mb3', 'done');
    });

    if (setDefaultAlger(logWilaya)) {
        logWilaya.dispatchEvent(new Event('change'));
    }
}
    // ─── MODAL PROJET — Wilaya → Communes ──────────────────────────────
    const modalWilaya  = $("modal_wilaya");
    const modalCommune = $("modal_commune");

    if (modalWilaya && modalCommune) {
        const onModalWilayaChange = async function () {
            resetSel(modalCommune, "-- Choisir d'abord une wilaya --");
            if (!this.value) return;
            modalCommune.innerHTML = '<option value="">-- Choisir une commune --</option>';
            await loadInto(
                `/api/communes/${this.value}`,
                modalCommune, null,
                (o,c) => { o.value=c.id; o.textContent=c.nom; }
            );
        };
        modalWilaya.addEventListener('change', onModalWilayaChange);

        if (setDefaultAlger(modalWilaya)) {
            modalWilaya.dispatchEvent(new Event('change'));
        }
    }

    // ─── Auto-close alertes ───────────────────────────────────────────
    const alertEl = $('alert');
    if (alertEl) setTimeout(() => new bootstrap.Alert(alertEl).close(), 3000);

    // ═══════════════════════════════════════════════════════════════════
    // LOGIQUE POPUP LOGEMENTS
    // ═══════════════════════════════════════════════════════════════════

    window._allLogements      = [];
    window._filteredLogements = [];
    window._currentPage       = 1;
    window._perPage           = 12;
    window._activeSiteId      = null;

    window.ouvrirLogements = async function(siteId, libelle, programme, wilaya) {
        window._activeSiteId = siteId;
        window._currentPage  = 1;

        $('ml_title').innerHTML = `<i class="bi bi-houses-fill me-2"></i> Logements — ${libelle}`;
        $('ml_meta').innerHTML  =
            `<span class="me-3"><i class="bi bi-grid me-1"></i>${programme}</span>` +
            `<span><i class="bi bi-map me-1"></i>${wilaya}</span>`;

        $('ml_search').value   = '';
        $('ml_status').value   = '';
        $('ml_batiment').value = '';
        $('ml_etage').value    = '';
        $('ml_etage').disabled = true;

        const modal = new bootstrap.Modal($('modalLogementsSite'), { backdrop: true, keyboard: true });
        modal.show();

        $('ml_tbody').innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div>Chargement des logements...</div>
                </td>
            </tr>`;

        try {
            const res  = await fetch(`/api/logements-site/${siteId}`);
            const json = await res.json();
            window._allLogements = json.logements || [];

            const batiments = [...new Set(window._allLogements.map(l => l.num_batiment))].sort();
            $('ml_batiment').innerHTML = '<option value="">Tous bâtiments</option>';
            batiments.forEach(b => {
                const o = document.createElement('option');
                o.value = b; o.textContent = 'Bât. ' + b;
                $('ml_batiment').appendChild(o);
            });

            mlAppliquerFiltres();
        } catch(e) {
            $('ml_tbody').innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2"></i>
                        Erreur lors du chargement des logements.
                    </td>
                </tr>`;
        }
    };

    function statutBadge(flag) {
        const map = {
            0: '<span class="badge-libre">Libre</span>',
            1: '<span class="badge-inscrit">Inscrit</span>',
            2: '<span class="badge-vendu">Soldé</span>',
            3: '<span class="badge-desiste">Remplacé</span>',
        };
        return map[flag] ?? `<span class="badge bg-secondary">${flag}</span>`;
    }

    function formatPrix(val) {
        if (!val) return '-';
        return Number(val).toLocaleString('fr-DZ') + ' DA';
    }

    function mlRendreTable() {
        const tbody  = $('ml_tbody');
        const total  = window._filteredLogements.length;
        const pages  = Math.ceil(total / window._perPage) || 1;
        const page   = Math.max(1, Math.min(window._currentPage, pages));
        window._currentPage = page;

        const debut  = (page - 1) * window._perPage;
        const slice  = window._filteredLogements.slice(debut, debut + window._perPage);

        $('ml_count').textContent    = `${total} logement(s)`;
        $('ml_page_info').textContent = total ? `Affichage ${debut + 1}–${Math.min(debut + window._perPage, total)} sur ${total}` : '';
        $('ml_page_num').textContent  = pages > 1 ? `Page ${page} / ${pages}` : '';
        $('ml_prev').disabled = page <= 1;
        $('ml_next').disabled = page >= pages;

        if (!slice.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                        <h6>Aucun logement trouvé</h6>
                        <p class="small mb-0">Essayez de modifier vos filtres</p>
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = slice.map((l, i) => {
            const souscBtn = l.souscripteur
                ? `<button class="btn-souscripteur" onclick="ouvrirSouscripteur(${JSON.stringify(l).replace(/"/g, '&quot;')})">
                        <i class="bi bi-person-fill me-1"></i> Voir
                   </button>`
                : `<button class="btn-souscripteur" disabled title="Aucun souscripteur">
                        <i class="bi bi-person-slash me-1"></i> Aucun
                   </button>`;

            return `
                <tr class="text-center">
                    <td class="text-muted">${debut + i + 1}</td>
                    <td><span class="fw-semibold">Bât. ${l.num_batiment}</span></td>
                    <td>Ét. ${l.num_etage}</td>
                    <td>${l.num_porte}</td>
                    <td class="text-muted">${l.num_lot ?? '-'}</td>
                    <td>${l.typologie ?? '-'}</td>
                    <td>${l.surface ? l.surface + ' m²' : '-'}</td>
                    <td style="white-space:nowrap">${formatPrix(l.prix)}</td>
                    <td>${statutBadge(l.flag)}</td>
                    <td>${souscBtn}</td>
                 </tr>`;
        }).join('');
    }

    window.mlAppliquerFiltres = function() {
        const search  = ($('ml_search').value  || '').toLowerCase().trim();
        const batiment= $('ml_batiment').value;
        const etage   = $('ml_etage').value;
        const status  = $('ml_status').value;

        window._filteredLogements = window._allLogements.filter(l => {
            if (batiment && l.num_batiment != batiment) return false;
            if (etage    && l.num_etage    != etage)    return false;
            if (status !== '' && l.flag != status)      return false;
            if (search) {
                const s = l.souscripteur;
                if (!s) return false;
                const hay = [s.nom, s.prenom, s.nom_arabe, s.prenom_arabe]
                    .filter(Boolean).join(' ').toLowerCase();
                if (!hay.includes(search)) return false;
            }
            return true;
        });

        window._currentPage = 1;
        mlRendreTable();
    };

    window.mlChangePage = function(dir) {
        window._currentPage += dir;
        mlRendreTable();
    };

    window.mlResetFiltres = function() {
        $('ml_search').value   = '';
        $('ml_status').value   = '';
        $('ml_batiment').value = '';
        $('ml_etage').value    = '';
        $('ml_etage').disabled = true;
        mlAppliquerFiltres();
    };

    ['ml_search','ml_status','ml_etage'].forEach(id => {
        const el = $(id);
        if (el) el.addEventListener('input', () => mlAppliquerFiltres());
    });

    const mlBatiment = $('ml_batiment');
    const mlEtage    = $('ml_etage');
    if (mlBatiment) {
        mlBatiment.addEventListener('change', function() {
            mlEtage.innerHTML  = '<option value="">Tous étages</option>';
            mlEtage.disabled   = true;
            if (this.value) {
                const etages = [...new Set(
                    window._allLogements
                        .filter(l => l.num_batiment == this.value)
                        .map(l => l.num_etage)
                )].sort((a,b) => a-b);
                etages.forEach(e => {
                    const o = document.createElement('option');
                    o.value = e; o.textContent = 'Ét. ' + e;
                    mlEtage.appendChild(o);
                });
                mlEtage.disabled = false;
            }
            mlAppliquerFiltres();
        });
    }

    // ═══════════════════════════════════════════════════════════════════
    // LOGIQUE POPUP SOUSCRIPTEUR
    // ═══════════════════════════════════════════════════════════════════

    window.ouvrirSouscripteur = function(logement) {
        const s = logement.souscripteur;

        const recapHtml = `
            <div class="logement-recap-bar">
                <strong><i class="bi bi-house-fill me-1"></i> Logement :</strong>
                <span class="litem">Bât. ${logement.num_batiment}</span>
                <span class="litem">Ét. ${logement.num_etage}</span>
                <span class="litem">Porte ${logement.num_porte}</span>
                ${logement.num_lot ? `<span class="litem">Lot ${logement.num_lot}</span>` : ''}
                ${logement.typologie ? `<span class="litem">${logement.typologie}</span>` : ''}
                ${logement.surface ? `<span class="litem">${logement.surface} m²</span>` : ''}
            </div>`;

        if (!s) {
            $('souscripteur_body').innerHTML = recapHtml + `
                <div class="no-souscripteur text-muted">
                    <i class="bi bi-person-slash"></i>
                    <h6>Aucun souscripteur lié à ce logement</h6>
                    <p class="small">Ce logement est libre ou sans inscription.</p>
                </div>`;
        } else {
            const infoRow = (label, value) => `
                <div class="col-sm-6 mb-3">
                    <div class="info-block">
                        <div class="info-label">${label}</div>
                        <div class="info-value">${value || '<span class="text-muted fst-italic">N/A</span>'}</div>
                    </div>
                </div>`;

            $('souscripteur_body').innerHTML = recapHtml + `
                <div class="row g-0">
                    ${infoRow('Nom et prénom', [s.nom, s.prenom].filter(Boolean).join(' '))}
                    ${infoRow('Nom et prénom (arabe)', [s.nom_arabe, s.prenom_arabe].filter(Boolean).join(' '))}
                    ${infoRow('NIN', s.nin)}
                    ${infoRow('Date de naissance', s.date_naissance)}
                </div>`;
        }

        const modalSous = new bootstrap.Modal($('modalSouscripteur'), { backdrop: false });
        modalSous.show();
    };

    // Correction du blocage après fermeture du modal souscripteur
    const modalSousElem = $('modalSouscripteur');
    if (modalSousElem) {
        modalSousElem.addEventListener('hidden.bs.modal', function () {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                for (let i = 1; i < backdrops.length; i++) {
                    backdrops[i].remove();
                }
            }
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            const parentModal = $('modalLogementsSite');
            if (parentModal && parentModal.classList.contains('show')) {
                document.body.classList.add('modal-open');
                const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
                if (scrollbarWidth > 0) {
                    document.body.style.paddingRight = scrollbarWidth + 'px';
                }
            }
        });
    }

});
</script>

</x-app-layout>