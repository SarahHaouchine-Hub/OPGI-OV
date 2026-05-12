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
        <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i> Gestion des Ordres de versement</h5>
            <span class="badge bg-light text-dark">{{ count($souscripteurs) }} Total</span>
        </div>

        {{-- Formulaire de filtres --}}
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

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0" style="text-align:center;">
                    <thead>
                        <tr>
                            <th>Souscripteur</th>
                            <th>Code / Programme</th>
                            <th>Localisation</th>
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
        // Identification par numero_tranche (robuste même si type_ov était null)
        $ovT2Fait       = $s->ovs->contains(fn($o) => $o->numero_tranche === 2);
        $ovDiffDejaFait = $s->ovs->contains(fn($o) => $o->numero_tranche === 3);
        $ovT3Credit     = $s->ovs->firstWhere('numero_tranche', 3);
        $ovT3Paye       = $ovT3Credit !== null && $ovT3Credit->paiement !== null;

        $diffCredit        = $creditBancaire->montant_attestation - $creditBancaire->montant_reel;
        $paiementParCredit = $ovT2Fait && ($diffCredit <= 0 || $ovT3Paye);

        $totalOvsCredit = $diffCredit > 0 ? 3 : 2;
        // Compte T1 + T2 + T3 selon numero_tranche
        $nbOvsCrees     = $s->ovs->whereIn('numero_tranche', [1, 2, 3])->count();
        $tousOvsGeneres = $nbOvsCrees === $totalOvsCredit;
    }

    // Tranches normales = uniquement celles sans crédit (type_ov null OU numero_tranche 1 si crédit)
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

                                {{-- Localisation --}}
                                <td>
                                    <div>Bât. {{ $s->logement->num_batiment }}</div>
                                    <div>Étage {{ $s->logement->num_etage }}</div>
                                    <div>Porte {{ $s->logement->num_porte }}</div>
                                </td>

                                {{-- Prix --}}
                                <td>
                                    <span class="price-tag text-success">
                                        {{ number_format($s->logement->prix, 2, ',', ' ') }} <small>DA</small>
                                    </span>
                                </td>

                                {{-- ══════════ OVs ══════════ --}}
                                <td class="p-3">
                                    @forelse($s->ovs as $ov)
                                    <div class="ov-item mb-3 p-3 border rounded-3 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                            <div class="d-flex align-items-center gap-2">

                                                @if($ov->type_ov === 'credit_reel')
                                                    {{-- Numérotation X/Y en mode crédit --}}
                                                    @if($creditEnregistre)
    @if($ov->numero_tranche === 1)
        <span class="badge bg-secondary" style="font-size:0.7rem;">1/{{ $totalOvsCredit }}</span>
        <span class="badge bg-warning text-dark">T1</span>
    @elseif($ov->numero_tranche === 2)
        <span class="badge bg-secondary" style="font-size:0.7rem;">2/{{ $totalOvsCredit }}</span>
        <span class="badge bg-info text-dark">
            <i class="bi bi-bank me-1"></i>T2 Crédit Réel
        </span>
    @elseif($ov->numero_tranche === 3)
        <span class="badge bg-secondary" style="font-size:0.7rem;">3/{{ $totalOvsCredit }}</span>
        <span class="badge" style="background:#6f42c1;">
            <i class="bi bi-exclamation-triangle me-1"></i>T3 Différence
        </span>
    @endif
@else
    <span class="badge bg-primary">{{ $ov->pourcentage }}%</span>
    @if($programme === 'LPA' && $ov->numero_tranche)
        <span class="badge bg-warning text-dark">T{{ $ov->numero_tranche }}/5</span>
    @endif
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

                                        <div class="text-muted small mb-2">
                                            Montant : <strong class="text-dark">{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</strong>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap justify-content-center">
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
                                            <a href="{{ route('ov.pdf', Hashids::encode($ov->id)) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-printer me-1"></i> Imprimer OV
                                            </a>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-3">
                                        <i class="bi bi-inbox text-muted" style="font-size:2rem;"></i>
                                        <p class="text-muted mb-0 small mt-1">Aucun OV</p>
                                    </div>
                                    @endforelse

                                    {{-- ══════════ PROGRESSION LPA ══════════ --}}
                                    @if($programme === 'LPA')
                                        <div class="mt-2">
                                            @if($creditEnregistre)
                                                {{-- Mode Crédit : badge X/Y généré + statut paiement --}}
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
                                                {{-- Mode Normal : barre de progression 5 tranches --}}
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

                                {{-- ══════════ COLONNE ACTIONS ══════════ --}}
                                <td class="text-center">
    @if($programme === 'LPA' && $creditEnregistre)
        @if($paiementParCredit)
            {{-- Dossier 100% soldé --}}
            <span class="badge bg-success px-3 py-2">
                <i class="bi bi-check-all me-1"></i> Complet
            </span>
        @elseif($ovT3Credit !== null && $ovT3Credit->paiement === null)
            {{-- T3 générée mais pas encore payée --}}
            <div class="d-flex flex-column gap-2 align-items-center">
                <a href="{{ route('paiement.create', Hashids::encode($ovT3Credit->id)) }}"
                   class="btn btn-warning btn-action btn-sm shadow-sm fw-bold w-100">
                    <i class="bi bi-bank me-1"></i> Payer T3
                </a>
                <a href="{{ route('ov.create', Hashids::encode($s->id)) }}"
                   class="btn btn-secondary btn-action btn-sm shadow-sm w-100">
                    <i class="bi bi-eye me-1"></i> Voir Dossier
                </a>
            </div>
        @else
            {{-- Crédit enregistré, T2 pas encore générée ou en cours --}}
            <a href="{{ route('ov.create', Hashids::encode($s->id)) }}"
               class="btn btn-secondary btn-action btn-sm shadow-sm">
                <i class="bi bi-eye me-1"></i> Voir Dossier
            </a>
        @endif
    @elseif($peutGenerer)
        <a href="{{ route('ov.create', Hashids::encode($s->id)) }}"
           class="btn btn-primary btn-action btn-sm shadow-sm">
            <i class="fas fa-plus-circle"></i>
            @if($programme === 'LPA')
                Tranche {{ $prochaineTrancheLabel }}
            @elseif($programme === 'LSP')
                Nouvelle tranche
            @else
                Nouveau OV
            @endif
        </a>
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
        </div>
    </div>
</div>

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