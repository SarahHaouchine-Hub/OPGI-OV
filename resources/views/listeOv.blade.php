<x-app-layout>
<style>
    .custom-card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
    .card-header-gradient { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; padding: 1.5rem; }
    .table-modern thead { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
    .table-modern tbody tr { vertical-align: middle; }
    .table-modern tbody tr:hover { background-color: #f1f4f9; box-shadow: inset 4px 0 0 #2a5298; }
    .price-tag { font-weight: 700; color: #2d3436; font-family: 'Monaco', monospace; }
    .btn-action { border-radius: 8px; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
    .info-sub { font-size: 0.85rem; color: #636e72; }
    .ov-item { transition: all 0.3s ease; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); }
    .ov-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; transform: translateY(-2px); }
    .ov-item:last-child { margin-bottom: 0 !important; }
    .badge { font-weight: 500; }
    .programme-badge { font-size: 0.7rem; padding: 3px 8px; border-radius: 20px; font-weight: 700; letter-spacing: 0.5px; }

    /* ══ Site badge ══ */
    .site-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #1e3c72;
        background: #e8eef8;
        border: 1px solid #c5d3ed;
        border-radius: 20px;
        padding: 2px 9px;
        margin-bottom: 5px;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    .localisation-bloc { font-size: 0.82rem; color: #555; line-height: 1.7; }
    .localisation-bloc i { font-size: 0.68rem; }

    /* ══ Pagination ══ */
    .pagination-wrapper { background: #fff; border-radius: 0 0 15px 15px; }
    .pagination-info { font-size: 0.85rem; color: #636e72; }
    .pagination-info strong { color: #2a5298; font-weight: 700; }
    .pagination-custom { display: flex; align-items: center; gap: 4px; list-style: none; padding: 0; margin: 0; }
    .pc-link {
        display: flex; align-items: center; justify-content: center;
        min-width: 36px; height: 36px; padding: 0 8px;
        border-radius: 9px; font-size: 0.85rem; font-weight: 600;
        color: #2a5298; background: #f1f4f9; text-decoration: none;
        transition: all 0.18s ease; cursor: pointer; border: 1.5px solid transparent;
    }
    .pc-link:hover {
        background: #dce8fb; border-color: #2a5298; color: #1e3c72;
        transform: translateY(-1px); box-shadow: 0 3px 8px rgba(42,82,152,0.15);
    }
    .pc-item.active .pc-link {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
        color: #fff; border-color: transparent;
        box-shadow: 0 4px 12px rgba(42,82,152,0.35);
        cursor: default; transform: none;
    }
    .pc-item.disabled .pc-link {
        color: #c0c9d4; background: #f8f9fa;
        cursor: not-allowed; transform: none; box-shadow: none; pointer-events: none;
    }
    .pc-dots {
        background: transparent !important; border: none !important;
        color: #b2becd !important; letter-spacing: 2px;
        font-weight: 400 !important; cursor: default !important; box-shadow: none !important;
    }
</style>

@if(session('pdf_url'))
<script>window.open("{{ session('pdf_url') }}", "_blank");</script>
@endif

<div class="container py-5">
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
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert" id="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {!! session('warning') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card custom-card">

        {{-- ══ EN-TÊTE ══ --}}
        <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i> Gestion des Ordres de versement</h5>
            <span class="badge bg-light text-dark">{{ $souscripteurs->total() }} Total</span>
        </div>

        {{-- ══ FILTRES ══ --}}
        <div class="card-body bg-light border-bottom p-3">
            <form method="GET" action="{{ route('ov.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Programme</label>
                    <select name="programme" class="form-select">
                        <option value="">Tous</option>
                        <option value="LPA" {{ request('programme') == 'LPA' ? 'selected' : '' }}>LPA</option>
                        <option value="LSP" {{ request('programme') == 'LSP' ? 'selected' : '' }}>LSP</option>
                        <option value="LPL" {{ request('programme') == 'LPL' ? 'selected' : '' }}>LPL</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Site</label>
                    <input type="text" name="site" class="form-control" placeholder="Ex: Alger" value="{{ request('site') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Souscripteur</label>
                    <input type="text" name="souscripteur" class="form-control" placeholder="Nom ou prénom" value="{{ request('souscripteur') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Code logement</label>
                    <input type="text" name="code" class="form-control" placeholder="Code" value="{{ request('code') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Bâtiment</label>
                    <input type="text" name="batiment" class="form-control" placeholder="N° bâtiment" value="{{ request('batiment') }}">
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrer
                        </button>
                        <a href="{{ route('ov.index') }}" class="btn btn-secondary w-100">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ TABLEAU ══ --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0" style="text-align:center;">
                    <thead>
                        <tr>
                            <th>Souscripteur</th>
                            <th>Code / Programme</th>
                            <th>PROJET / Localisation</th>
                            <th>Prix du Logement</th>
                            <th>Ordres de versement</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($souscripteurs as $s)
                            @php
                                $programme      = strtoupper(trim($s->logement->programme->libelle ?? 'LPL'));
                                $creditBancaire = $s->creditBancaire ?? null;

                                $creditEnregistre  = $programme === 'LPA' && $creditBancaire !== null;
                                $ovT2Fait          = false;
                                $ovDiffDejaFait    = false;
                                $ovT3Paye          = false;
                                $diffCredit        = 0;
                                $paiementParCredit = false;
                                $ovT3Credit        = null;

                                if ($creditEnregistre) {
                             // APRÈS
$ovCreditReel     = $s->ovs->firstWhere('type_ov', 'credit_reel');
$ovCreditDiff     = $s->ovs->firstWhere('type_ov', 'credit_diff');
$ovsDoneNormaux   = $s->ovs->whereNull('type_ov');

$ovCreditReelFait = $ovCreditReel !== null;
$ovCreditDiffFait = $ovCreditDiff !== null;
$ovCreditDiffPaye = $ovCreditDiff !== null && $ovCreditDiff->paiement !== null;

$diffCredit        = $creditBancaire
    ? ($creditBancaire->montant_attestation - ($creditBancaire->montant_reel ?? $creditBancaire->montant_attestation))
    : 0;
$paiementParCredit = $ovCreditReelFait && ($diffCredit <= 0 || $ovCreditDiffPaye);

$totalOvsCredit = $ovsDoneNormaux->count() + ($diffCredit > 0 ? 2 : 1);
$nbOvsCrees     = $s->ovs->count();
$tousOvsGeneres = $ovCreditReelFait && ($diffCredit <= 0 || $ovCreditDiffFait);
                                }

                                $nbTranchesNormales = $creditEnregistre
                                    ? $s->ovs->where('numero_tranche', 1)->count()
                                    : $s->ovs->whereNull('type_ov')->count();

                                $ovT2NormaleExiste = !$creditEnregistre && $s->ovs
                                    ->whereNull('type_ov')
                                    ->where('numero_tranche', 2)
                                    ->isNotEmpty();

                                $peutGenerer = true;
                                if ($programme === 'LPA') {
                                    $peutGenerer = $creditEnregistre ? !$paiementParCredit : $nbTranchesNormales < 5;
                                }
                                if ($programme === 'LSP') {
                                    $prixLogement = $s->logement->prix ?? 0;
                                    $totalAides   = $s->aides->sum('montant');
                                    $totalPaye    = $s->ovs->sum('montant_paye');
                                    $reste        = max(0, $prixLogement - $totalAides - $totalPaye);
                                    $peutGenerer  = $reste > 0;
                                }
                                if ($programme === 'LPL') {
                                    $dernierOv   = $s->ovs->sortByDesc('created_at')->first();
                                    $peutGenerer = !($dernierOv && $dernierOv->montant_restant <= 0);
                                }

                                $prochaineTrancheLabel = $nbTranchesNormales + 1;

                                $nomSite = $s->logement->site->nom
                                        ?? $s->logement->site->libelle
                                        ?? $s->logement->programme->site
                                        ?? null;
                            @endphp

                            <tr>
                                {{-- Souscripteur --}}
                                <td class="text-start">
                                    <div class="fw-bold text-dark">{{ strtoupper($s->nom) }} {{ $s->prenom }}</div>
                                </td>

                                {{-- Code + badge programme --}}
                                <td>
                                    <div class="text-primary fw-bold">{{ $s->code_loge_lpl }}</div>
                                    <span class="programme-badge
                                        @if($programme === 'LPA') bg-warning text-dark
                                        @elseif($programme === 'LSP') bg-info text-white
                                        @else bg-secondary text-white
                                        @endif">
                                        {{ $programme }}
                                    </span>
                                </td>

                                {{-- ══ LOCALISATION + SITE ══ --}}
                                <td>
                                    @if($nomSite)
                                        <div>
                                            <span class="site-badge">
                                                <i class="bi bi-buildings"></i>
                                                {{ $nomSite }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="localisation-bloc">
                                        <div>
                                            <i class="bi bi-building text-muted me-1"></i>
                                            Bât. <strong>{{ $s->logement->num_batiment }}</strong>
                                        </div>
                                        <div>
                                            <i class="bi bi-layers text-muted me-1"></i>
                                            Étage {{ $s->logement->num_etage }}
                                        </div>
                                        <div>
                                            <i class="bi bi-door-open text-muted me-1"></i>
                                            Porte {{ $s->logement->num_porte }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Prix --}}
                                <td>
                                    <span class="price-tag text-success">
                                        {{ number_format($s->logement->prix, 2, ',', ' ') }} <small>DA</small>
                                    </span>
                                </td>

                                {{-- ══ OVs ══ --}}
                                <td class="p-3">
                                    @forelse($s->ovs as $ov)
                                    <div class="ov-item mb-3 p-3 border rounded-3 shadow-sm">

                                        {{-- En-tête OV : badges + statut paiement --}}
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                            <div class="d-flex align-items-center gap-2">

                                          {{-- APRÈS — basé sur type_ov, numéro de tranche dynamique --}}
@php $ovPos = $s->ovs->values()->search(fn($o) => $o->id === $ov->id) + 1; @endphp

@if($ov->type_ov === 'credit_reel')
    <span class="badge bg-secondary" style="font-size:0.7rem;">{{ $ovPos }}/{{ $totalOvsCredit }}</span>
    <span class="badge bg-info text-dark">
        <i class="bi bi-bank me-1"></i>T{{ $ov->numero_tranche }} Crédit Réel
    </span>

@elseif($ov->type_ov === 'credit_diff')
    <span class="badge bg-secondary" style="font-size:0.7rem;">{{ $ovPos }}/{{ $totalOvsCredit }}</span>
    <span class="badge" style="background:#6f42c1;">
        <i class="bi bi-exclamation-triangle me-1"></i>T{{ $ov->numero_tranche }} Différence
    </span>

@else
    {{-- Tranche normale --}}
    @if($creditEnregistre)
        <span class="badge bg-secondary" style="font-size:0.7rem;">{{ $ovPos }}/{{ $totalOvsCredit }}</span>
    @endif
    <span class="badge bg-primary">{{ $ov->pourcentage }}%</span>
    @if($programme === 'LPA' && $ov->numero_tranche)
        <span class="badge bg-warning text-dark">T{{ $ov->numero_tranche }}/5</span>
    @endif
@endif

                                                @if($ov->vsp)
                                                    <span class="badge bg-success" title="VSP effectué">
                                                        <i class="bi bi-house-check-fill"></i> VSP
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Statut paiement --}}
                                            @if($ov->paiement)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i> PAYÉ
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-clock me-1"></i> En attente
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Montant --}}
                                        <div class="text-muted small mb-2">
                                            Montant : <strong class="text-dark">{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</strong>
                                        </div>

                                        {{-- Boutons --}}
                                        <div class="d-flex gap-2 flex-wrap justify-content-center">

                                            {{-- Payer / Reçu --}}
                                            @if($ov->paiement)
                                                @if($ov->type_ov === 'credit_reel')
                                                    <span class="badge bg-secondary px-2 py-2">
                                                        <i class="bi bi-bank me-1"></i> Payé par banque
                                                    </span>
                                                @elseif($ov->paiement->recu_pdf)
                                                    <a href="{{ asset('storage/' . $ov->paiement->recu_pdf) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-file-pdf me-1"></i> Reçu
                                                    </a>
                                                @else
                                                    <span class="badge bg-secondary px-2 py-2">
                                                        <i class="bi bi-check me-1"></i> Payé
                                                    </span>
                                                @endif
                                            @else
                                                <a href="{{ route('paiement.create', Hashids::encode($ov->id)) }}"
                                                   class="btn btn-sm btn-success">
                                                    <i class="bi bi-bank me-1"></i> Payer
                                                </a>
                                            @endif

                                            {{-- Imprimer OV --}}
                                            @if($ov->paiement)
                                                <button class="btn btn-sm btn-outline-secondary" disabled
                                                        title="OV déjà payé — impression désactivée">
                                                    <i class="bi bi-printer me-1"></i> Imprimer OV
                                                </button>
                                            @else
                                                <a href="{{ route('ov.pdf', Hashids::encode($ov->id)) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-printer me-1"></i> Imprimer OV
                                                </a>
                                            @endif

                                            {{-- ══ BOUTON MODIFIER — chef_service_com + OV non payé ══ --}}
                                            @if(Auth::user()->role === 'chef_service_com' && !$ov->paiement)
                                                <a href="{{ route('ov.edit', Hashids::encode($ov->id)) }}"
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-pencil-square me-1"></i> Modifier
                                                </a>
                                            @endif

                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-3">
                                        <i class="bi bi-inbox text-muted" style="font-size:2rem;"></i>
                                        <p class="text-muted mb-0 small mt-1">Aucun OV</p>
                                    </div>
                                    @endforelse

                                    {{-- ══ PROGRESSION LPA ══ --}}
                                    @if($programme === 'LPA')
                                        <div class="mt-2">
                                            @if($creditEnregistre)
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="badge {{ $tousOvsGeneres ? 'bg-success' : 'bg-secondary' }} fs-6">
                                                        {{ $nbOvsCrees }}/{{ $totalOvsCredit }} généré
                                                    </span>
                                                    @if($paiementParCredit)
                                                        <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Dossier Soldé</span>
                                                    @elseif($tousOvsGeneres)
                                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Paiement en attente</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="progress" style="height:6px; border-radius:3px;">
                                                    <div class="progress-bar bg-success"
                                                         style="width: {{ ($nbTranchesNormales / 5) * 100 }}%">
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $nbTranchesNormales }}/5 tranches générées</small>
                                                @if($ovT2NormaleExiste && $nbTranchesNormales < 5)
                                                    <br>
                                                    <small class="text-secondary">
                                                        <i class="bi bi-lock-fill me-1"></i>Crédit bancaire non disponible
                                                    </small>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                {{-- ══ ACTIONS ══ --}}
                                <td class="text-center">
                                    @if($programme === 'LPA' && $creditEnregistre)
                                        @if($paiementParCredit)
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="bi bi-check-all me-1"></i> Complet
                                            </span>
                                        {{-- APRÈS — basé sur type_ov --}}
@elseif($ovCreditDiff !== null && $ovCreditDiff->paiement === null)
    <div class="d-flex flex-column gap-2 align-items-center">
        <a href="{{ route('paiement.create', Hashids::encode($ovCreditDiff->id)) }}"
           class="btn btn-warning btn-action btn-sm shadow-sm fw-bold w-100">
            <i class="bi bi-bank me-1"></i> Payer T{{ $ovCreditDiff->numero_tranche }}
        </a>
                                                <a href="{{ route('ov.create', Hashids::encode($s->id)) }}"
                                                   class="btn btn-secondary btn-action btn-sm shadow-sm w-100">
                                                    <i class="bi bi-eye me-1"></i> Voir Dossier
                                                </a>
                                            </div>
                                        @else
                                            <a href="{{ route('ov.create', Hashids::encode($s->id)) }}"
                                               class="btn btn-secondary btn-action btn-sm shadow-sm">
                                                <i class="bi bi-eye me-1"></i> Voir Dossier
                                            </a>
                                        @endif

                                    @elseif($peutGenerer)
                                        @if($s->ovs->isEmpty() && Auth::user()->role !== 'dg' && $programme !== 'LPA')
                                            <span class="badge bg-secondary px-3 py-2" title="Réservé au DG">
                                                <i class="bi bi-lock-fill me-1"></i> DG uniquement
                                            </span>
                                        @else
                                            <a href="{{ route('ov.create', Hashids::encode($s->id)) }}"
                                               class="btn btn-primary btn-action btn-sm shadow-sm">
                                                <i class="fas fa-plus-circle"></i>
                                                @if($programme === 'LPA') Tranche {{ $prochaineTrancheLabel }}
                                                @elseif($programme === 'LSP') Nouvelle tranche
                                                @else Nouveau OV
                                                @endif
                                            </a>
                                        @endif

                                    @else
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-all me-1"></i> Complet
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-5 text-muted text-center">
                                    <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                    <h5>Aucun souscripteur trouvé</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ══ PAGINATION ══ --}}
            @if($souscripteurs->hasPages())
            <div class="pagination-wrapper d-flex justify-content-between align-items-center px-4 py-3 border-top">
                <div class="pagination-info">
                    Affichage de
                    <strong>{{ $souscripteurs->firstItem() }}</strong>
                    à
                    <strong>{{ $souscripteurs->lastItem() }}</strong>
                    sur
                    <strong>{{ $souscripteurs->total() }}</strong>
                    souscripteurs
                </div>
                <nav aria-label="Pagination">
                    <ul class="pagination-custom">
                        @php
                            $currentPage = $souscripteurs->currentPage();
                            $lastPage    = $souscripteurs->lastPage();
                            $start       = max(1, $currentPage - 2);
                            $end         = min($lastPage, $currentPage + 2);
                            $baseUrl     = $souscripteurs->appends(request()->query());
                        @endphp

                        {{-- Première page --}}
                        <li class="pc-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                            @if($currentPage > 1)
                                <a class="pc-link" href="{{ $baseUrl->url(1) }}" title="Première page">
                                    <i class="bi bi-chevron-double-left"></i>
                                </a>
                            @else
                                <span class="pc-link"><i class="bi bi-chevron-double-left"></i></span>
                            @endif
                        </li>

                        {{-- Précédent --}}
                        <li class="pc-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                            @if($currentPage > 1)
                                <a class="pc-link" href="{{ $baseUrl->previousPageUrl() }}" title="Précédent">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            @else
                                <span class="pc-link"><i class="bi bi-chevron-left"></i></span>
                            @endif
                        </li>

                        {{-- "..." début --}}
                        @if($start > 1)
                            <li class="pc-item">
                                <a class="pc-link" href="{{ $baseUrl->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="pc-item disabled"><span class="pc-link pc-dots">···</span></li>
                            @endif
                        @endif

                        {{-- Pages numérotées --}}
                        @for($i = $start; $i <= $end; $i++)
                            <li class="pc-item {{ $i == $currentPage ? 'active' : '' }}">
                                @if($i == $currentPage)
                                    <span class="pc-link">{{ $i }}</span>
                                @else
                                    <a class="pc-link" href="{{ $baseUrl->url($i) }}">{{ $i }}</a>
                                @endif
                            </li>
                        @endfor

                        {{-- "..." fin --}}
                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <li class="pc-item disabled"><span class="pc-link pc-dots">···</span></li>
                            @endif
                            <li class="pc-item">
                                <a class="pc-link" href="{{ $baseUrl->url($lastPage) }}">{{ $lastPage }}</a>
                            </li>
                        @endif

                        {{-- Suivant --}}
                        <li class="pc-item {{ !$souscripteurs->hasMorePages() ? 'disabled' : '' }}">
                            @if($souscripteurs->hasMorePages())
                                <a class="pc-link" href="{{ $baseUrl->nextPageUrl() }}" title="Suivant">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @else
                                <span class="pc-link"><i class="bi bi-chevron-right"></i></span>
                            @endif
                        </li>

                        {{-- Dernière page --}}
                        <li class="pc-item {{ $currentPage == $lastPage ? 'disabled' : '' }}">
                            @if($currentPage < $lastPage)
                                <a class="pc-link" href="{{ $baseUrl->url($lastPage) }}" title="Dernière page">
                                    <i class="bi bi-chevron-double-right"></i>
                                </a>
                            @else
                                <span class="pc-link"><i class="bi bi-chevron-double-right"></i></span>
                            @endif
                        </li>
                    </ul>
                </nav>
            </div>
            @endif

        </div>{{-- /card-body --}}
    </div>{{-- /card --}}
</div>{{-- /container --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.getElementById('alert');
    if (alert) {
        setTimeout(function () {
            alert.classList.remove('show');
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 600);
        }, 3000);
    }
});
</script>
</x-app-layout>