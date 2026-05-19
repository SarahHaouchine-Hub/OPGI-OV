<x-app-layout>
<style>
/* ══════════════════════════════════════════════════════
   DESIGN SYSTEM — TABLEAU DE BORD
   ══════════════════════════════════════════════════════ */

/* ── Variables ── */
:root {
    --navy:       #0f2a52;
    --navy-mid:   #1e3c72;
    --navy-light: #2a5298;
    --accent:     #3b82f6;
    --success:    #059669;
    --warning:    #d97706;
    --danger:     #dc2626;
    --info:       #0891b2;
    --purple:     #7c3aed;
    --surface:    #f8fafc;
    --border:     #e2e8f0;
    --text-main:  #0f172a;
    --text-muted: #64748b;
    --radius:     10px;
    --radius-lg:  14px;
}

/* ── Layout ── */
.db-wrapper    { max-width: 1400px; margin: 0 auto; padding: 1.5rem 1.25rem; }
.db-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--border);
}
.db-page-title    { font-size: 1.35rem; font-weight: 700; color: var(--navy); margin: 0; }
.db-page-subtitle { font-size: .8rem; color: var(--text-muted); margin: .2rem 0 0; }

/* ── Stat cards ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .75rem;
    margin-bottom: .5rem;
}
.stat-card {
    border-radius: var(--radius);
    padding: 1rem 1.1rem;
    color: white;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
}
.stat-card::after {
    content: '';
    position: absolute;
    right: -14px; top: -14px;
    width: 60px; height: 60px;
    border-radius: 50%;
    background: rgba(255,255,255,.1);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.15); }
.stat-card .sc-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .6px; opacity: .8; margin-bottom: .3rem; }
.stat-card .sc-value { font-size: 1.9rem; font-weight: 700; line-height: 1; }
.stat-card .sc-sub   { font-size: .68rem; opacity: .7; margin-top: .35rem; }

.sc-navy    { background: var(--navy-mid); }
.sc-blue    { background: var(--accent); }
.sc-info    { background: var(--info); }
.sc-success { background: var(--success); }
.sc-danger  { background: var(--danger); }
.sc-purple  { background: var(--purple); }
.sc-sky     { background: #0284c7; }
.sc-amber   { background: var(--warning); }

/* ── Section label ── */
.section-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--text-muted);
    margin-bottom: .5rem;
    display: flex;
    align-items: center;
    gap: .35rem;
}

/* ── Cards ── */
.db-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}
.db-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .9rem 1.25rem;
    background: var(--navy-mid);
    color: white;
}
.db-card-head.green { background: var(--success); }
.db-card-head h5 { margin: 0; font-size: .95rem; font-weight: 600; }

/* ── Filters ── */
.filters-body {
    padding: 1rem 1.25rem;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
}
.form-label { font-size: .78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: .35rem; }
.form-control, .form-select {
    font-size: .85rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: .45rem .75rem;
    transition: border-color .2s, box-shadow .2s;
    color: var(--text-main);
}
.form-control:focus, .form-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
    outline: none;
}
.form-select:disabled { background: #f1f5f9; color: #aaa; cursor: not-allowed; }

/* ── Buttons ── */
.btn-primary {
    background: var(--navy-mid);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: .85rem;
    font-weight: 600;
    padding: .48rem 1rem;
    transition: background .2s, box-shadow .2s;
}
.btn-primary:hover { background: var(--navy-light); color: white; box-shadow: 0 4px 12px rgba(30,60,114,.3); }
.btn-outline-secondary {
    border: 1px solid var(--border);
    background: white;
    border-radius: 8px;
    color: var(--text-muted);
    font-size: .85rem;
    padding: .46rem .9rem;
    transition: all .2s;
}
.btn-outline-secondary:hover { background: var(--surface); border-color: #94a3b8; }
.btn-action {
    border: none;
    border-radius: 9px;
    color: white;
    font-weight: 600;
    padding: .6rem 1.4rem;
    transition: transform .2s, box-shadow .2s;
}
.btn-action:hover { transform: translateY(-2px); color: white; }
.btn-action.navy   { background: var(--navy-mid);  box-shadow: 0 4px 12px rgba(30,60,114,.25); }
.btn-action.navy:hover { box-shadow: 0 6px 18px rgba(30,60,114,.35); }
.btn-action.green  { background: var(--success); box-shadow: 0 4px 12px rgba(5,150,105,.25); }
.btn-action.green:hover { box-shadow: 0 6px 18px rgba(5,150,105,.35); }
.btn-voir {
    background: var(--accent);
    border: none;
    border-radius: 7px;
    color: white;
    font-size: .75rem;
    font-weight: 600;
    padding: .3rem .85rem;
    transition: background .2s, transform .2s;
    white-space: nowrap;
}
.btn-voir:hover { background: #2563eb; transform: translateY(-1px); color: white; }

/* ── Table ── */
.db-table { font-size: .84rem; margin: 0; }
.db-table thead th {
    background: #f8fafc;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--text-muted);
    padding: .65rem 1rem;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.db-table tbody td { padding: .7rem 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
.db-table tbody tr:last-child td { border-bottom: none; }
.db-table tbody tr:hover { background: #f8fafc; }

/* ── Badges statut ── */
.badge-status {
    font-size: .68rem;
    font-weight: 700;
    padding: .25rem .7rem;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .3px;
    white-space: nowrap;
}
.badge-libre   { background: #dbeafe; color: #1d4ed8; }
.badge-inscrit { background: #cffafe; color: #0e7490; }
.badge-vendu   { background: #d1fae5; color: #065f46; }
.badge-desiste { background: #fee2e2; color: #991b1b; }

/* ── Step badges ── */
.step-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 50%;
    font-size: 10px; font-weight: 700;
    background: #cbd5e1; color: white;
    margin-right: 4px; flex-shrink: 0; transition: background .3s;
}
.step-badge.active { background: var(--navy-mid); }
.step-badge.done   { background: var(--success); }

/* ── Modaux ── */
.modal-content { border: none; border-radius: var(--radius-lg); box-shadow: 0 20px 60px rgba(0,0,0,.18); }
.modal-head-navy  { background: var(--navy-mid); color: white; border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 1.1rem 1.5rem; }
.modal-head-green { background: var(--success);  color: white; border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 1.1rem 1.5rem; }
.modal-head-vsp   { background: linear-gradient(135deg,#059669,#10b981); color: white; border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 1.1rem 1.5rem; }
.modal-content .btn-close { filter: invert(1) opacity(.75); }

/* ── Popup logements ── */
.ml-filters { background: #f8fafc; padding: .75rem 1.1rem; border-bottom: 1px solid var(--border); }
.ml-table-wrap { max-height: 400px; overflow-y: auto; }
.ml-table { font-size: .8rem; margin: 0; }
.ml-table thead th { position: sticky; top: 0; z-index: 2; background: #f1f5f9; font-size: .65rem; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); font-weight: 700; padding: .55rem .8rem; border-bottom: 1px solid var(--border); }
.ml-table tbody td { padding: .55rem .8rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; }
.ml-pag { background: #f8fafc; border-top: 1px solid var(--border); padding: .5rem 1rem; display: flex; align-items: center; justify-content: space-between; font-size: .78rem; }

/* ── Souscripteur info ── */
.info-block { background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: .8rem 1rem; height: 100%; }
.info-label { font-size: .65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; font-weight: 700; }
.info-value { font-size: .88rem; font-weight: 600; color: var(--text-main); }
.logement-recap { background: var(--navy-mid); color: white; border-radius: 8px; padding: .65rem 1rem; margin-bottom: 1rem; display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; font-size: .8rem; }
.logement-recap .litem { background: rgba(255,255,255,.15); border-radius: 20px; padding: 2px 10px; font-size: .75rem; }
.no-souscripteur { text-align: center; padding: 2rem; color: #94a3b8; }

/* ── Bouton souscripteur ── */
.btn-sous {
    background: var(--success); border: none; border-radius: 7px;
    color: white; font-size: .72rem; font-weight: 600;
    padding: 3px 11px; transition: all .2s; white-space: nowrap;
}
.btn-sous:hover { background: #047857; color: white; }
.btn-sous:disabled { background: #cbd5e1; cursor: not-allowed; color: white; }

/* ── sel-loading spinner ── */
.sel-loading { position: relative; }
.sel-loading::after {
    content: ''; position: absolute; right: 34px; top: 50%;
    transform: translateY(-50%); width: 13px; height: 13px;
    border: 2px solid #e2e8f0; border-top-color: var(--accent);
    border-radius: 50%; animation: db-spin .6s linear infinite; pointer-events: none;
}
@keyframes db-spin { to { transform: translateY(-50%) rotate(360deg); } }
</style>

<div class="py-3">
<div class="db-wrapper">

{{-- ── Alertes ──────────────────────────────────────────────────── --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     EN-TÊTE DE PAGE
     ══════════════════════════════════════════════════════════════ --}}
<div class="db-page-header">
    <div>
        <h1 class="db-page-title"><i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Tableau de bord</h1>
        <p class="db-page-subtitle">Vue d'ensemble des projets et logements</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn-action navy" data-bs-toggle="modal" data-bs-target="#modalSite">
            <i class="bi bi-geo-alt-fill me-1"></i> Nouveau projet
        </button>
        <button class="btn-action green" data-bs-toggle="modal" data-bs-target="#modalLogement">
            <i class="bi bi-house-fill me-1"></i> Nouveau logement
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     STATISTIQUES — LIGNE 1 : Logements
     ══════════════════════════════════════════════════════════════ --}}
<div class="section-label"><i class="bi bi-houses"></i> État du parc logement</div>
<div class="stats-grid mb-3">
    <div class="stat-card sc-navy" id="cardstat1">
        <div class="sc-label">Total logements</div>
        <div class="sc-value">{{ $totalLogements }}</div>
    </div>
    <div class="stat-card sc-blue" id="cardstat2">
        <div class="sc-label">Libres</div>
        <div class="sc-value">{{ $libres }}</div>
        <div class="sc-sub">Disponibles</div>
    </div>
    <div class="stat-card sc-info" id="cardstat3">
        <div class="sc-label">Affectés</div>
        <div class="sc-value">{{ $inscrits }}</div>
        <div class="sc-sub">Inscrits</div>
    </div>
    <div class="stat-card sc-success" id="cardstat4">
        <div class="sc-label">Soldés</div>
        <div class="sc-value">{{ $soldes }}</div>
        <div class="sc-sub">Transactions clôturées</div>
    </div>
    <div class="stat-card sc-danger" id="cardstat5">
        <div class="sc-label">Remplacés</div>
        <div class="sc-value">{{ $remplaces }}</div>
        <div class="sc-sub">Désistements</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     STATISTIQUES — LIGNE 2 : BNH / OV / VSP
     ══════════════════════════════════════════════════════════════ --}}
<div class="section-label mt-1"><i class="bi bi-bar-chart-fill"></i> Suivi BNH · OV · VSP</div>
<div class="stats-grid mb-4">
    <div class="stat-card sc-purple" id="cardstat_bnh">
        <div class="sc-label"><i class="bi bi-bank me-1"></i>Décision BNH</div>
        <div class="sc-value">{{ $decisionBnh }}</div>
        <div class="sc-sub">Avec décision BNH</div>
    </div>
    <div class="stat-card sc-sky" id="cardstat_ov_paye">
        <div class="sc-label"><i class="bi bi-check2-circle me-1"></i>OV Payés</div>
        <div class="sc-value">{{ $ovPayees }}</div>
        <div class="sc-sub">Ordres de virement réglés</div>
    </div>
    <div class="stat-card sc-amber" id="cardstat_ov_npaye">
        <div class="sc-label"><i class="bi bi-hourglass-split me-1"></i>OV Non payés</div>
        <div class="sc-value">{{ $ovNonPayees }}</div>
        <div class="sc-sub">En attente</div>
    </div>
    <div class="stat-card sc-success" id="cardstat_vsp"
         data-bs-toggle="modal" data-bs-target="#modalVspProjet"
         title="Voir le détail par projet" style="cursor:pointer">
        <div class="sc-label"><i class="bi bi-list-ol me-1"></i>Total VSP</div>
        <div class="sc-value">{{ $totalVsp }}</div>
        <div class="sc-sub" style="text-decoration:underline;opacity:.8">Détail par projet ›</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     FILTRES + TABLEAU PROJETS
     ══════════════════════════════════════════════════════════════ --}}
<div class="db-card mb-3">

    {{-- En-tête --}}
    <div class="db-card-head">
        <h5><i class="bi bi-geo-alt-fill me-2"></i>Liste des projets</h5>
        <span class="badge bg-white text-secondary fw-semibold" style="font-size:.8rem">
            {{ $sitesPaginated->total() }} projet(s)
        </span>
    </div>

    {{-- Filtres intégrés --}}
    <div class="filters-body">
        <form method="GET" action="{{ route('dashboard') }}">
            <div class="row g-2 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label"><span class="step-badge active" id="fb1">1</span>Wilaya</label>
                    <select name="wilaya_id" id="f_wilaya" class="form-select form-select-sm">
                        <option value="">Toutes les wilayas</option>
                        @foreach($wilayas as $w)
                            <option value="{{ $w->id }}" {{ request('wilaya_id') == $w->id ? 'selected' : '' }}>{{ $w->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label"><span class="step-badge" id="fb2">2</span>Programme</label>
                    <div id="fwrap_prog">
                        <select name="programme_id" id="f_programme" class="form-select form-select-sm" {{ request('wilaya_id') ? '' : 'disabled' }}>
                            <option value="">Tous les programmes</option>
                            @foreach($programmes as $p)
                                <option value="{{ $p->id }}" {{ request('programme_id') == $p->id ? 'selected' : '' }}>{{ $p->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <label class="form-label"><i class="bi bi-search me-1"></i>Libellé du projet</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Rechercher un projet..." value="{{ request('search') }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-primary w-100" style="font-size:.82rem">
                            <i class="bi bi-search me-1"></i>Filtrer
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn-outline-secondary" title="Réinitialiser"
                           style="display:inline-flex;align-items:center;justify-content:center;width:38px;flex-shrink:0">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="table-responsive">
        <table class="db-table table table-hover">
            <thead>
                <tr class="text-center">
                    <th style="width:50px">N°</th>
                    <th>Programme</th>
                    <th>Wilaya</th>
                    <th class="text-start">Projet</th>
                    <th>Logements</th>
                    <th style="width:130px">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sitesPaginated as $site)
                    <tr class="text-center">
                        <td class="text-muted fw-normal">{{ $sitesPaginated->firstItem() + $loop->index }}</td>
                        <td>
                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-weight:600;font-size:.72rem;padding:.25rem .6rem;border-radius:6px">
                                {{ $site->programme->libelle ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $site->wilaya->nom ?? 'N/A' }}</td>
                        <td class="text-start fw-semibold text-dark">{{ $site->libelle }}</td>
                        <td>
                            <span class="badge" style="background:#f1f5f9;color:#475569;font-weight:700;font-size:.8rem;padding:.25rem .65rem;border-radius:8px">
                                {{ $site->logements->count() }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn-voir"
                                onclick="ouvrirLogements(
                                    {{ $site->id }},
                                    '{{ addslashes($site->libelle) }}',
                                    '{{ addslashes($site->programme->libelle ?? 'N/A') }}',
                                    '{{ addslashes($site->wilaya->nom ?? 'N/A') }}'
                                )">
                                <i class="bi bi-eye me-1"></i>Voir logements
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-geo-alt display-4 d-block mb-3" style="color:#cbd5e1"></i>
                            <h6 class="text-muted">Aucun projet trouvé</h6>
                            <p class="small text-muted mb-0">Essayez de modifier vos filtres</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($sitesPaginated->hasPages())
        <div style="padding:.65rem 1rem;border-top:1px solid var(--border);background:#fafafa">
            {{ $sitesPaginated->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

</div>{{-- /db-wrapper --}}
</div>{{-- /py-3 --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — VSP PAR PROJET
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalVspProjet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-head-vsp d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="bi bi-list-ol me-2"></i>VSP par projet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div style="max-height:440px;overflow-y:auto">
                    <table class="ml-table table table-hover">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width:40px">N°</th>
                                <th>Projet</th>
                                <th class="text-center" style="width:90px">Nb VSP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vspParProjet->where('vsp_count', '>', 0) as $index => $projet)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $projet->libelle }}</td>
                                    <td class="text-center">
                                        <span class="badge" style="background:#d1fae5;color:#065f46;font-weight:700;font-size:.78rem;padding:.25rem .75rem;border-radius:20px">
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
                <div class="d-flex justify-content-between align-items-center"
                     style="background:#f8fafc;border-top:1px solid var(--border);padding:.6rem 1rem;font-size:.82rem">
                    <span class="text-muted fw-semibold">Total général</span>
                    <span class="badge" style="background:#d1fae5;color:#065f46;font-weight:700;font-size:.85rem;padding:.3rem .9rem;border-radius:20px">
                        {{ $totalVsp }} VSP
                    </span>
                </div>
            </div>
            <div class="modal-footer" style="padding:.6rem 1rem;border-top:1px solid var(--border)">
                <button type="button" class="btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — LOGEMENTS DU PROJET
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalLogementsSite" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width:95vw">
        <div class="modal-content">

            <div class="modal-head-navy d-flex align-items-start justify-content-between">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="ml_title">
                        <i class="bi bi-houses-fill me-2"></i>Logements
                    </h5>
                    <div style="font-size:.78rem;opacity:.8" id="ml_meta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="ml-filters">
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
                    <div class="col-6 col-sm-3 col-md-2">
                        <select id="ml_status" class="form-select form-select-sm">
                            <option value="">Tous statuts</option>
                            <option value="0">Libre</option>
                            <option value="1">Inscrit</option>
                            <option value="2">Soldé</option>
                            <option value="3">Remplacé</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-auto">
                        <span id="ml_count" class="badge" style="background:#f1f5f9;color:#475569;font-weight:700;font-size:.78rem">0 logement(s)</span>
                    </div>
                    <div class="col-auto ms-auto">
                        <button class="btn-outline-secondary" style="font-size:.78rem;padding:.28rem .7rem" onclick="mlResetFiltres()">
                            <i class="bi bi-x-lg me-1"></i>Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-body p-0">
                <div class="ml-table-wrap">
                    <table class="ml-table table table-hover">
                        <thead>
                            <tr class="text-center">
                                <th>N°</th>
                                <th>Bâtiment</th>
                                <th>Étage</th>
                                <th>Porte</th>
                                <th>Lot</th>
                                <th>Typo.</th>
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
                                    <div style="font-size:.85rem">Chargement des logements...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="ml-pag">
                    <span id="ml_page_info" class="text-muted"></span>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn-outline-secondary" style="padding:.22rem .55rem;font-size:.78rem" id="ml_prev" onclick="mlChangePage(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <span id="ml_page_num" class="text-muted fw-semibold" style="font-size:.78rem"></span>
                        <button class="btn-outline-secondary" style="padding:.22rem .55rem;font-size:.78rem" id="ml_next" onclick="mlChangePage(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — INFOS SOUSCRIPTEUR
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalSouscripteur" tabindex="-1" aria-hidden="true" style="z-index:1060">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-head-green d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="bi bi-person-vcard-fill me-2"></i>Informations du souscripteur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="souscripteur_body">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="mt-2">Chargement...</div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--border);padding:.6rem 1rem">
                <button type="button" class="btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — NOUVEAU PROJET
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalSite" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-head-navy d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="bi bi-geo-alt-fill me-2"></i>Ajouter un nouveau projet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('site.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-map me-1"></i>Wilaya</label>
                            <select name="wilaya_id" id="modal_wilaya" class="form-select" required>
                                <option value="">-- Choisir une wilaya --</option>
                                @foreach($wilayas as $wilaya)
                                    <option value="{{ $wilaya->id }}">{{ $wilaya->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-pin-map me-1"></i>Commune</label>
                            <select name="commune_id" id="modal_commune" class="form-select" required disabled>
                                <option value="">-- Choisir d'abord une wilaya --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-grid me-1"></i>Programme</label>
                            <select name="programme_id" class="form-select" required>
                                <option value="">-- Choisir un programme --</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-tag me-1"></i>Libellé du projet</label>
                            <input type="text" name="libelle" class="form-control" placeholder="Ex : Cité des 500 logements" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-file-earmark-text me-1"></i>N° Convention BNH</label>
                            <input type="text" name="num_convention_bnh" class="form-control" placeholder="Ex : CONV-2025-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-building me-1"></i>Nom de l'agence</label>
                            <input type="text" name="nom_agence" class="form-control" placeholder="Ex : BNH">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-hash me-1"></i>N° Agence</label>
                            <input type="text" name="num_agence" class="form-control" placeholder="Ex : AG-007">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-credit-card me-1"></i>N° Compte agence</label>
                            <input type="text" name="num_compte_agence" class="form-control" placeholder="Ex : 00012345678901234567">
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-geo me-1"></i>Adresse agence</label>
                            <input type="text" name="adresse_agence" class="form-control" placeholder="Ex : 12 Rue Didouche Mourad, Alger">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);padding:.75rem 1.25rem;gap:.5rem">
                    <button type="button" class="btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-action navy"><i class="bi bi-check-circle me-1"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODAL — NOUVEAU LOGEMENT
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalLogement" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-head-green d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="bi bi-house-fill me-2"></i>Ajouter un nouveau logement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('logement.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><span class="step-badge active" id="mb1">1</span>Wilaya</label>
                            <select id="log_wilaya" class="form-select" required>
                                <option value="">-- Choisir une wilaya --</option>
                                @foreach($wilayas as $w)
                                    <option value="{{ $w->id }}">{{ $w->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><span class="step-badge" id="mb2">2</span>Programme</label>
                            <select id="log_programme" name="programme_id" class="form-select" required disabled>
                                <option value="">-- Choisir d'abord une wilaya --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><span class="step-badge" id="mb3">3</span>Projet</label>
                            <div id="mwrap_site">
                                <select name="site_id" id="log_site" class="form-select" required disabled>
                                    <option value="">-- Choisir d'abord un programme --</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12"><hr class="my-1" style="border-color:var(--border)"></div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-building me-1"></i>Bâtiment</label>
                            <input type="text" name="num_batiment" class="form-control" placeholder="Ex : A" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-layers me-1"></i>Étage</label>
                            <input type="number" name="num_etage" class="form-control" placeholder="Ex : 1" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-door-closed me-1"></i>N° Porte</label>
                            <input type="number" name="num_porte" class="form-control" placeholder="Ex : 12" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-hash me-1"></i>N° Lot</label>
                            <input type="text" name="num_lot" class="form-control" placeholder="Ex : LOT-001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-rulers me-1"></i>Surface (m²)</label>
                            <input type="number" step="0.01" name="surface" class="form-control" placeholder="Ex : 85.50" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bi bi-layout-text-window me-1"></i>Typologie</label>
                            <select name="typologie" class="form-select">
                                <option value="">-- Choisir --</option>
                                <option>F2</option><option>F3</option><option>F4</option><option>F5</option><option>F6</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-cash-stack me-1"></i>Prix (DA)</label>
                            <input type="number" step="0.01" name="prix" class="form-control" placeholder="Ex : 5000000" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);padding:.75rem 1.25rem;gap:.5rem">
                    <button type="button" class="btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-action green"><i class="bi bi-check-circle me-1"></i>Enregistrer</button>
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
        if (!selectEl) return false;
        const algerOpt = Array.from(selectEl.options).find(o => o.textContent.trim() === 'Alger');
        if (algerOpt && !selectEl.value) { selectEl.value = algerOpt.value; return true; }
        return false;
    }

    // ─── Filtres projets cascade ──────────────────────────────────────────
    const fWilaya = $("f_wilaya"), fProg = $("f_programme");
    if (fWilaya && fProg) {
        const onChange = async function() {
            resetSel(fProg, "Tous");
            if (!this.value) { badge('fb1','active'); return; }
            fProg.innerHTML = '<option value="">Tous</option>';
            const ok = await loadInto(`/api/souscripteur/programmes-by-wilaya/${this.value}`, fProg, 'fwrap_prog',
                (o,p) => { o.value=p.id; o.textContent=p.libelle; });
            if (ok) { badge('fb1','done'); badge('fb2','active'); }
        };
        fWilaya.addEventListener('change', onChange);
        if (!fWilaya.value && setDefaultAlger(fWilaya)) fWilaya.dispatchEvent(new Event('change'));
        else if (fWilaya.value) fWilaya.dispatchEvent(new Event('change'));
    }

    // ─── Modal logement cascade ───────────────────────────────────────────
    const logWilaya = $("log_wilaya"), logProg = $("log_programme"), logSite = $("log_site");
    if (logWilaya && logProg && logSite) {
        logWilaya.addEventListener('change', async function() {
            resetSel(logProg, "-- Choisir d'abord une wilaya --");
            resetSel(logSite, "-- Choisir d'abord un programme --");
            badge('mb1','active');
            if (!this.value) return;
            logProg.innerHTML = '<option value="">-- Choisir un programme --</option>';
            const ok = await loadInto(`/api/souscripteur/programmes-by-wilaya/${this.value}`, logProg, null,
                (o,p) => { o.value=p.id; o.textContent=p.libelle; });
            if (ok) { badge('mb1','done'); badge('mb2','active'); }
        });
        logProg.addEventListener('change', async function() {
            resetSel(logSite, "-- Choisir d'abord un programme --");
            badge('mb2','active');
            if (!this.value) return;
            logSite.innerHTML = '<option value="">-- Choisir un projet --</option>';
            const ok = await loadInto(`/api/souscripteur/sites/${logWilaya.value}/${this.value}`, logSite, 'mwrap_site',
                (o,s) => { o.value=s.id; o.textContent=s.libelle; });
            if (ok) { badge('mb2','done'); badge('mb3','active'); }
        });
        logSite.addEventListener('change', function() { if (this.value) badge('mb3','done'); });
        if (setDefaultAlger(logWilaya)) logWilaya.dispatchEvent(new Event('change'));
    }

    // ─── Modal projet — Wilaya → Communes ────────────────────────────────
    const modalWilaya = $("modal_wilaya"), modalCommune = $("modal_commune");
    if (modalWilaya && modalCommune) {
        modalWilaya.addEventListener('change', async function() {
            resetSel(modalCommune, "-- Choisir d'abord une wilaya --");
            if (!this.value) return;
            modalCommune.innerHTML = '<option value="">-- Choisir une commune --</option>';
            await loadInto(`/api/communes/${this.value}`, modalCommune, null,
                (o,c) => { o.value=c.id; o.textContent=c.nom; });
        });
        if (setDefaultAlger(modalWilaya)) modalWilaya.dispatchEvent(new Event('change'));
    }

    // ─── Auto-close alertes ───────────────────────────────────────────────
    const alertEl = $('alert');
    if (alertEl) setTimeout(() => new bootstrap.Alert(alertEl).close(), 3000);

    // ═══════════════════════════════════════════════════════════════════════
    // POPUP LOGEMENTS
    // ═══════════════════════════════════════════════════════════════════════

    window._allLogements = []; window._filteredLogements = [];
    window._currentPage = 1; window._perPage = 12; window._activeSiteId = null;

    window.ouvrirLogements = async function(siteId, libelle, programme, wilaya) {
        window._activeSiteId = siteId; window._currentPage = 1;
        $('ml_title').innerHTML = `<i class="bi bi-houses-fill me-2"></i>${libelle}`;
        $('ml_meta').innerHTML  = `<span class="me-3"><i class="bi bi-grid me-1"></i>${programme}</span><span><i class="bi bi-map me-1"></i>${wilaya}</span>`;
        $('ml_search').value = ''; $('ml_status').value = '';
        $('ml_batiment').value = ''; $('ml_etage').value = ''; $('ml_etage').disabled = true;

        new bootstrap.Modal($('modalLogementsSite'), {backdrop:true, keyboard:true}).show();
        $('ml_tbody').innerHTML = `<tr><td colspan="10" class="text-center py-5 text-muted">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <div style="font-size:.85rem">Chargement des logements...</div>
        </td></tr>`;

        try {
            const json = await fetch(`/api/logements-site/${siteId}`).then(r => r.json());
            window._allLogements = json.logements || [];
            const bats = [...new Set(window._allLogements.map(l => l.num_batiment))].sort();
            $('ml_batiment').innerHTML = '<option value="">Tous bâtiments</option>';
            bats.forEach(b => {
                const o = document.createElement('option'); o.value=b; o.textContent='Bât. '+b;
                $('ml_batiment').appendChild(o);
            });
            mlAppliquerFiltres();
        } catch(e) {
            $('ml_tbody').innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2"></i>Erreur lors du chargement.
            </td></tr>`;
        }
    };

    function statutBadge(flag) {
        return {
            0: '<span class="badge-status badge-libre">Libre</span>',
            1: '<span class="badge-status badge-inscrit">Inscrit</span>',
            2: '<span class="badge-status badge-vendu">Soldé</span>',
            3: '<span class="badge-status badge-desiste">Remplacé</span>',
        }[flag] ?? `<span class="badge bg-secondary">${flag}</span>`;
    }
    function formatPrix(val) {
        return val ? Number(val).toLocaleString('fr-DZ') + ' DA' : '-';
    }

    function mlRendreTable() {
        const tbody = $('ml_tbody');
        const total = window._filteredLogements.length;
        const pages = Math.ceil(total / window._perPage) || 1;
        const page  = Math.max(1, Math.min(window._currentPage, pages));
        window._currentPage = page;
        const debut = (page - 1) * window._perPage;
        const slice = window._filteredLogements.slice(debut, debut + window._perPage);

        $('ml_count').textContent     = `${total} logement(s)`;
        $('ml_page_info').textContent = total ? `Affichage ${debut+1}–${Math.min(debut+window._perPage,total)} sur ${total}` : '';
        $('ml_page_num').textContent  = pages > 1 ? `Page ${page} / ${pages}` : '';
        $('ml_prev').disabled = page <= 1; $('ml_next').disabled = page >= pages;

        if (!slice.length) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3" style="opacity:.4"></i>
                <h6>Aucun logement trouvé</h6>
                <p class="small mb-0">Essayez de modifier vos filtres</p>
            </td></tr>`; return;
        }

        tbody.innerHTML = slice.map((l, i) => {
            const btn = l.souscripteur
                ? `<button class="btn-sous" onclick="ouvrirSouscripteur(${JSON.stringify(l).replace(/"/g,'&quot;')})">
                        <i class="bi bi-person-fill me-1"></i>Voir</button>`
                : `<button class="btn-sous" disabled><i class="bi bi-person-slash me-1"></i>Aucun</button>`;
            return `<tr class="text-center">
                <td class="text-muted">${debut+i+1}</td>
                <td class="fw-semibold">Bât. ${l.num_batiment}</td>
                <td>Ét. ${l.num_etage}</td>
                <td>${l.num_porte}</td>
                <td class="text-muted">${l.num_lot ?? '-'}</td>
                <td>${l.typologie ?? '-'}</td>
                <td>${l.surface ? l.surface+' m²' : '-'}</td>
                <td style="white-space:nowrap">${formatPrix(l.prix)}</td>
                <td>${statutBadge(l.flag)}</td>
                <td>${btn}</td>
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
                const s = l.souscripteur; if (!s) return false;
                const hay = [s.nom,s.prenom,s.nom_arabe,s.prenom_arabe].filter(Boolean).join(' ').toLowerCase();
                if (!hay.includes(search)) return false;
            }
            return true;
        });
        window._currentPage = 1; mlRendreTable();
    };
    window.mlChangePage = function(dir) { window._currentPage += dir; mlRendreTable(); };
    window.mlResetFiltres = function() {
        $('ml_search').value=''; $('ml_status').value='';
        $('ml_batiment').value=''; $('ml_etage').value=''; $('ml_etage').disabled=true;
        mlAppliquerFiltres();
    };

    ['ml_search','ml_status','ml_etage'].forEach(id => {
        const el = $(id); if (el) el.addEventListener('input', () => mlAppliquerFiltres());
    });
    const mlBatiment = $('ml_batiment'), mlEtage = $('ml_etage');
    if (mlBatiment) {
        mlBatiment.addEventListener('change', function() {
            mlEtage.innerHTML = '<option value="">Tous étages</option>'; mlEtage.disabled = true;
            if (this.value) {
                const etages = [...new Set(window._allLogements.filter(l => l.num_batiment==this.value).map(l=>l.num_etage))].sort((a,b)=>a-b);
                etages.forEach(e => { const o=document.createElement('option'); o.value=e; o.textContent='Ét. '+e; mlEtage.appendChild(o); });
                mlEtage.disabled = false;
            }
            mlAppliquerFiltres();
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // POPUP SOUSCRIPTEUR
    // ═══════════════════════════════════════════════════════════════════════
    window.ouvrirSouscripteur = function(logement) {
        const s = logement.souscripteur;
        const recap = `<div class="logement-recap">
            <strong><i class="bi bi-house-fill me-1"></i>Logement :</strong>
            <span class="litem">Bât. ${logement.num_batiment}</span>
            <span class="litem">Ét. ${logement.num_etage}</span>
            <span class="litem">Porte ${logement.num_porte}</span>
            ${logement.num_lot  ? `<span class="litem">Lot ${logement.num_lot}</span>`   : ''}
            ${logement.typologie ? `<span class="litem">${logement.typologie}</span>`   : ''}
            ${logement.surface   ? `<span class="litem">${logement.surface} m²</span>` : ''}
        </div>`;

        if (!s) {
            $('souscripteur_body').innerHTML = recap + `<div class="no-souscripteur">
                <i class="bi bi-person-slash fs-1 d-block mb-2"></i>
                <h6 class="text-muted">Aucun souscripteur lié à ce logement</h6>
                <p class="small text-muted">Ce logement est libre ou sans inscription.</p>
            </div>`;
        } else {
            const row = (label, value) => `<div class="col-sm-6 mb-3"><div class="info-block">
                <div class="info-label">${label}</div>
                <div class="info-value">${value || '<span class="text-muted fst-italic">N/A</span>'}</div>
            </div></div>`;
            $('souscripteur_body').innerHTML = recap + `<div class="row g-0">
                ${row('Nom et prénom', [s.nom,s.prenom].filter(Boolean).join(' '))}
                ${row('Nom et prénom (arabe)', [s.nom_arabe,s.prenom_arabe].filter(Boolean).join(' '))}
                ${row('NIN', s.nin)}
                ${row('Date de naissance', s.date_naissance)}
            </div>`;
        }
        new bootstrap.Modal($('modalSouscripteur'), {backdrop:false}).show();
    };

    const modalSousElem = $('modalSouscripteur');
    if (modalSousElem) {
        modalSousElem.addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll('.modal-backdrop').forEach((b,i) => { if (i>0) b.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.overflow = ''; document.body.style.paddingRight = '';
            const parentModal = $('modalLogementsSite');
            if (parentModal && parentModal.classList.contains('show')) {
                document.body.classList.add('modal-open');
                const sw = window.innerWidth - document.documentElement.clientWidth;
                if (sw > 0) document.body.style.paddingRight = sw + 'px';
            }
        });
    }
});
</script>

</x-app-layout>