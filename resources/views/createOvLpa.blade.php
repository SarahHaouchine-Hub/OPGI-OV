<x-app-layout>
<style>
    .card-header-gradient { background: linear-gradient(45deg, #1e3c72, #2a5298); }
    .custom-card { box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
    .form-control[readonly] { background-color: #f8f9fa; font-weight: 600; color: #333; }
    .form-label-custom {
        font-size: 0.85rem; font-weight: 600; color: #555;
        text-transform: uppercase; margin-bottom: 0.5rem; display: block;
    }
    .input-group-text { background-color: #f8f9fa; color: #1e3c72; border-right: none; }
    .input-group .form-control { border-left: none; }
    .tranche-indicator { font-size: 2rem; font-weight: 700; color: #1e3c72; }
    .aide-card    { border-left: 4px solid #28a745; background: #f8fff9; }
    .aide-missing { border-left: 4px solid #dc3545; background: #fff8f8; }
    .tranche-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 48px; height: 48px; border-radius: 50%;
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    }
    .recap-box {
        border-radius: 10px;
        padding: 0.9rem 1.2rem;
        font-size: 0.9rem;
    }
    .arrow-separator {
        font-size: 1.4rem; color: #adb5bd;
        display: flex; align-items: center; justify-content: center;
    }
    .credit-card-section { border-left: 4px solid #6f42c1; background: #f8f5ff; }
</style>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" id="flash-alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" id="flash-alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert" id="flash-alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {!! session('warning') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        // T1 = tranche normale, T2 = credit_reel (payé auto), T3 = credit_diff (différence)
        $ovsDoneNormaux = $ovsDone->where('type_ov', null);
        $ovCreditReel   = $ovsDone->firstWhere('type_ov', 'credit_reel');
        $ovCreditDiff   = $ovsDone->firstWhere('type_ov', 'credit_diff');

        // Vérifier si T2 normale existe déjà (pour masquer le bouton crédit)
        $ovT2NormaleExiste = $ovsDoneNormaux->where('numero_tranche', 2)->first() !== null;

        // ── Calcul "dossier soldé" mode crédit ──────────────────────────────
        // Complet = dernier OV complémentaire payé :
        //   • diff = 0 → T2 est le dernier OV → payé automatiquement = toujours vrai si T2 existe
        //   • diff > 0 → T3 est le dernier OV → complet seulement quand T3 payée
        $diffCreditCalc = $creditBancaire
            ? ($creditBancaire->montant_attestation - $creditBancaire->montant_reel)
            : 0;

        $dossierSoldeResume = $creditBancaire
            && $ovCreditReel                             // T2 existe (payée auto)
            && (
                $diffCreditCalc <= 0                     // diff=0 → T2 est le dernier → complet
                || ($ovCreditDiff && $ovCreditDiff->paiement !== null)  // diff>0 → T3 payée
            );
    @endphp

    <div class="card custom-card" style="border:none; border-radius:15px">
        <div class="card-header card-header-gradient text-white" style="padding:1.5rem;">
            <h4 class="mb-0" style="font-size:18px">
                <i class="bi bi-file-earmark-text-fill me-2"></i>
                @if($creditBancaire)
                    Dossier LPA — Crédit Bancaire —
                @else
                    Générer OV LPA — Tranche {{ $prochaineTranche }}/5 —
                @endif
                <strong>{{ strtoupper($souscripteur->nom) }} {{ strtoupper($souscripteur->prenom) }}</strong>
            </h4>
            <small class="opacity-75">Code Logement : {{ $souscripteur->code_loge_lpl }}</small>
        </div>

        <div class="card-body p-4">

            {{-- ══════════════════════════════════════════════════════
                 RÉCAPITULATIF FINANCIER
            ══════════════════════════════════════════════════════ --}}
            @php
                $labelBase = $prochaineTranche === 1
                    ? "Prix − BNH"
                    : "Reste après T" . ($prochaineTranche - 1);
            @endphp

            <div class="row g-2 mb-4 align-items-center">
                <div class="col">
                    <div class="recap-box text-center" style="background:#e9ecef; border:1px solid #dee2e6;">
                        <div class="text-muted small mb-1 fw-semibold">Prix Logement</div>
                        <div class="fw-bold text-dark" style="font-size:1rem;">
                            {{ number_format($prixLogement, 2, ',', ' ') }} <small>DA</small>
                        </div>
                    </div>
                </div>

                <div class="col-auto arrow-separator">−</div>
                <div class="col">
                    <div class="recap-box text-center" style="background:#fff3cd; border:1px solid #ffc107;">
                        <div class="text-warning-emphasis small mb-1 fw-semibold">Aide BNH</div>
                        <div class="fw-bold text-warning-emphasis" style="font-size:1rem;">
                            {{ number_format($montantBnh, 2, ',', ' ') }} <small>DA</small>
                        </div>
                        @if($site && $site->num_convention_bnh)
                            <div class="mt-1" style="font-size:0.72rem; color:#6c757d;">
                                Conv. : {{ $site->num_convention_bnh }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($prochaineTranche > 1)
                    <div class="col-auto arrow-separator">−</div>
                    <div class="col">
                        <div class="recap-box text-center" style="background:#d4edda; border:1px solid #c3e6cb;">
                            <div class="text-success small mb-1 fw-semibold">Déjà Versé</div>
                            <div class="fw-bold text-success" style="font-size:1rem;">
                                {{ number_format($totalPaye, 2, ',', ' ') }} <small>DA</small>
                            </div>
                            <small class="text-success">{{ $ovsDoneNormaux->count() }} tranche(s)</small>
                        </div>
                    </div>
                @endif

                <div class="col-auto arrow-separator">=</div>
                <div class="col">
                    <div class="recap-box text-center" style="background:#d1e7dd; border:2px solid #0f5132;">
                        <div class="text-success-emphasis small mb-1 fw-semibold">Reste Global</div>
                        <div class="fw-bold text-success" style="font-size:1.1rem;">
                            {{ number_format($baseCalcul, 2, ',', ' ') }} <small>DA</small>
                        </div>
                        <small class="text-success-emphasis">{{ $labelBase }}</small>
                    </div>
                </div>

                @if(!$creditBancaire && $prochaineTranche === 4 && $aideFnpos)
                    <div class="col-12 mt-2">
                        <div class="alert alert-info py-2 mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Tranche 4 :</strong>
                            Montant T4 = ({{ number_format($prix2, 0, ',', ' ') }} DA × 25%)
                            − FNPOS {{ number_format($fnposMontant, 0, ',', ' ') }} DA
                            = <strong>{{ number_format($montantTranche, 2, ',', ' ') }} DA</strong>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ══════════════════════════════════════════════════════
                 SECTION 1 : Aides BNH / FNPOS
            ══════════════════════════════════════════════════════ --}}
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    @if($aideBnh)
                        <div class="card aide-card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>Aide BNH enregistrée
                                </h6>
                                <div class="mb-2"><strong>Montant :</strong> {{ number_format($aideBnh->montant, 2, ',', ' ') }} DA</div>
                                @if($site && $site->num_convention_bnh)
                                    <div class="mb-2"><strong>N° Convention (projet) :</strong> {{ $site->num_convention_bnh }}</div>
                                @endif
                                <div class="mb-2"><strong>N° Décision :</strong> {{ $aideBnh->num_decision }}</div>
                                <div><strong>Date :</strong> {{ $aideBnh->date->format('d/m/Y') }}</div>
                                @if($aideBnh->pieces_jointes)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$aideBnh->pieces_jointes) }}" target="_blank"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-paperclip me-1"></i>Voir PJ
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card aide-missing shadow-sm h-100">
                            <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                                <i class="bi bi-exclamation-triangle text-danger mb-2" style="font-size:2rem;"></i>
                                <p class="text-danger fw-bold mb-3">
                                    Aide BNH manquante <span class="badge bg-danger">Obligatoire T1</span>
                                </p>
                                @if($site && !$site->num_convention_bnh)
                                    <div class="alert alert-warning py-1 px-2 mb-2 small">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        N° Convention BNH non configuré pour ce projet.
                                    </div>
                                @endif
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalBnh">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter BNH
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-6 mb-3">
                    @if($aideFnpos)
                        <div class="card aide-card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>Aide FNPOS enregistrée
                                </h6>
                                <div class="mb-2">
                                    <strong>Montant :</strong>
                                    {{ number_format($aideFnpos->montant, 2, ',', ' ') }} DA
                                    <span class="badge bg-secondary ms-1">Fixe</span>
                                </div>
                                <div class="mb-2"><strong>N° Décision :</strong> {{ $aideFnpos->num_decision }}</div>
                                <div><strong>Date :</strong> {{ $aideFnpos->date->format('d/m/Y') }}</div>
                                <div class="mt-2 small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Déduite uniquement dans la Tranche 4
                                </div>
                                @if($aideFnpos->pieces_jointes)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$aideFnpos->pieces_jointes) }}" target="_blank"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-paperclip me-1"></i>Voir PJ
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card border shadow-sm h-100">
                            <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                                <i class="bi bi-info-circle text-info mb-2" style="font-size:2rem;"></i>
                                <p class="text-muted mb-1">Aide FNPOS <span class="badge bg-secondary">Optionnelle</span></p>
                                <small class="text-muted mb-3 d-block">
                                    Montant fixe : <strong>500 000 DA</strong><br>
                                    Déduite uniquement dans le calcul de la T4
                                </small>
                                <button type="button" class="btn btn-info btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalFnpos">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter FNPOS
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 SECTION 2 : Historique des OVs
            ══════════════════════════════════════════════════════ --}}
            @if($ovsDoneNormaux->count() > 0 || $ovCreditReel || $ovCreditDiff)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>
                        OVs déjà générés
                        ({{ $ovsDoneNormaux->count() }} tranche(s) normale(s)
                        @if($ovCreditReel) + T2 Crédit Réel @endif
                        @if($ovCreditDiff) + T3 Différence @endif)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Tranche</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Montant versé</th>
                                    <th class="text-center">VSP</th>
                                    <th class="text-center">Statut paiement</th>
                                    <th class="text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Tranches normales --}}
                                @foreach($ovsDoneNormaux as $ovDone)
                                <tr>
                                    <td class="text-center fw-bold">T{{ $ovDone->numero_tranche }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">Normale</span>
                                    </td>
                                    <td class="text-center">{{ $ovDone->pourcentage }}%</td>
                                    <td class="text-center">{{ number_format($ovDone->montant_paye, 2, ',', ' ') }} DA</td>
                                    <td class="text-center">
                                        @if($ovDone->vsp)
                                            <span class="badge bg-success">OUI</span>
                                        @else
                                            <span class="badge bg-light text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($ovDone->paiement)
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Payé</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>En attente</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $ovDone->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach

                                {{-- T2 : OV Crédit Réel --}}
                                @if($ovCreditReel)
                                <tr style="background:#e8f5e9;">
                                    <td class="text-center fw-bold text-success">T2</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            <i class="bi bi-bank me-1"></i>T2 Crédit Réel
                                        </span>
                                    </td>
                                    {{-- Pas de % pour les OVs crédit --}}
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center fw-bold text-success">
                                        {{ number_format($ovCreditReel->montant_paye, 2, ',', ' ') }} DA
                                    </td>
                                    <td class="text-center"><span class="badge bg-light text-muted">—</span></td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            <i class="bi bi-bank me-1"></i>Payé par banque
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $ovCreditReel->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endif

                                {{-- T3 : OV Différence --}}
                                @if($ovCreditDiff)
                                <tr style="background:#fff3e0;">
                                    <td class="text-center fw-bold" style="color:#e65100;">T3</td>
                                    <td class="text-center">
                                        <span class="badge" style="background:#e65100;">
                                            <i class="bi bi-exclamation-triangle me-1"></i>T3 Différence
                                        </span>
                                    </td>
                                    {{-- Pas de % pour les OVs crédit --}}
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center fw-bold" style="color:#e65100;">
                                        {{ number_format($ovCreditDiff->montant_paye, 2, ',', ' ') }} DA
                                    </td>
                                    <td class="text-center"><span class="badge bg-light text-muted">—</span></td>
                                    <td class="text-center">
                                        @if($ovCreditDiff->paiement)
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Payé</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>En attente</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $ovCreditDiff->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Total versé (tranches normales) :</td>
                                    <td class="text-center text-success">
                                        {{ number_format($totalPaye, 2, ',', ' ') }} DA
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                @if($ovCreditReel)
                                <tr class="table-secondary">
                                    <td colspan="3" class="text-end text-success">T2 Crédit Réel (Payé par banque) :</td>
                                    <td class="text-center fw-bold text-success">
                                        {{ number_format($ovCreditReel->montant_paye, 2, ',', ' ') }} DA
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                @endif
                                @if($ovCreditDiff)
                                <tr class="table-secondary">
                                    <td colspan="3" class="text-end" style="color:#e65100;">T3 Différence :</td>
                                    <td class="text-center fw-bold" style="color:#e65100;">
                                        {{ number_format($ovCreditDiff->montant_paye, 2, ',', ' ') }} DA
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════════════════
                 SECTION 3 : Progression des 5 tranches (mode normal)
            ══════════════════════════════════════════════════════ --}}
            @if(!$creditBancaire)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart-steps me-2"></i>
                        Progression des tranches
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        @foreach($tranches as $num => $pct)
                        @php
                            $isDone    = $ovsDoneNormaux->contains('numero_tranche', $num);
                            $isCurrent = ($num === $prochaineTranche);

                            if ($isDone) {
                                $montantT = $ovsDoneNormaux->firstWhere('numero_tranche', $num)->montant_paye;
                            } elseif ($isCurrent) {
                                $montantT = $montantTranche;
                            } else {
                                $montantT = round($prix2 * $pct / 100);
                                if ($num === 4 && $aideFnpos) {
                                    $montantT = max(0, $montantT - $fnposMontant);
                                }
                            }
                        @endphp
                        <div class="text-center flex-fill">
                            <div class="tranche-badge mb-1
                                {{ $isDone    ? 'bg-success text-white' : '' }}
                                {{ $isCurrent ? 'bg-primary text-white' : '' }}
                                {{ !$isDone && !$isCurrent ? 'bg-light text-muted border' : '' }}">
                                T{{ $num }}
                            </div>
                            <div class="fw-bold {{ $isCurrent ? 'text-primary' : ($isDone ? 'text-success' : 'text-muted') }}">
                                {{ $pct }}%
                            </div>
                            <small class="d-block text-muted" style="font-size:0.72rem;">
                                {{ number_format($montantT, 0, ',', ' ') }} DA
                                @if($num === 4 && $aideFnpos && !$isDone)
                                    <br><span class="text-info">(− FNPOS)</span>
                                @endif
                            </small>
                            <small class="{{ $isDone ? 'text-success' : ($isCurrent ? 'text-primary' : 'text-muted') }}">
                                {{ $isDone ? '✓ Généré' : ($isCurrent ? '← En cours' : 'À venir') }}
                            </small>
                        </div>
                        @if($num < 5)
                            <div class="text-muted" style="font-size:1.2rem;">→</div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            {{-- ══════════════════════════════════════════════════════
                 SECTION 3 (mode crédit) : Progression crédit bancaire
                 Complet = T2 payée ET (diff=0 OU T3 payée)
            ══════════════════════════════════════════════════════ --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart-steps me-2"></i>
                        Progression — Mode Crédit Bancaire
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 flex-wrap">

                        {{-- ── T1 ── --}}
                        <div class="text-center">
                            <span class="badge bg-success px-3 py-2" style="font-size:0.85rem;">
                                <i class="bi bi-check-circle me-1"></i>
                                T1 — {{ number_format($ovsDoneNormaux->firstWhere('numero_tranche',1)->montant_paye ?? 0, 0, ',', ' ') }} DA
                            </span>
                        </div>
                        <div class="text-muted">→</div>

                        {{-- ── T2 Crédit Réel ── --}}
                        <div class="text-center">
                            @if($ovCreditReel)
                                <span class="badge bg-success px-3 py-2" style="font-size:0.85rem;">
                                    <i class="bi bi-bank me-1"></i>
                                    T2 Crédit Réel — {{ number_format($ovCreditReel->montant_paye, 0, ',', ' ') }} DA ✓ Payé par banque
                                </span>
                            @else
                                <span class="badge bg-secondary px-3 py-2" style="font-size:0.85rem;">
                                    <i class="bi bi-bank me-1"></i>T2 Crédit Réel — En cours
                                </span>
                            @endif
                        </div>

                        {{-- ── T3 Différence (si diff > 0) ── --}}
                        @if($diffCreditCalc > 0)
                            <div class="text-muted">→</div>
                            <div class="text-center">
                                @if($ovCreditDiff)
                                    @if($ovCreditDiff->paiement)
                                        <span class="badge bg-success px-3 py-2" style="font-size:0.85rem;">
                                            <i class="bi bi-check-circle me-1"></i>
                                            T3 Différence — {{ number_format($ovCreditDiff->montant_paye, 0, ',', ' ') }} DA ✓ Payé
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2" style="font-size:0.85rem;">
                                            <i class="bi bi-clock me-1"></i>
                                            T3 Différence — {{ number_format($ovCreditDiff->montant_paye, 0, ',', ' ') }} DA — En attente
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary px-3 py-2" style="font-size:0.85rem;">
                                        <i class="bi bi-exclamation-triangle me-1"></i>T3 Différence — Non générée
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- ── Statut global : Complet ou non ── --}}
                        <div class="ms-auto">
                            @if($dossierSoldeResume)
                                {{-- Complet uniquement si T2 payée + (diff=0 OU T3 payée) --}}
                                <span class="badge bg-success px-3 py-2" style="font-size:0.9rem;">
                                    <i class="bi bi-check-all me-1"></i>
                                    <i class="bi bi-bank me-1"></i> Dossier Complet
                                </span>
                            @else
                                <small class="text-muted">T2→T5 remplacées par crédit bancaire</small>
                            @endif
                        </div>
                    </div>

                    {{-- Résumé textuel sous la barre --}}
                    <div class="mt-3">
                        @if($dossierSoldeResume)
                            <div class="alert alert-success py-2 mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>Dossier entièrement soldé.</strong>
                                T1 ({{ number_format($ovsDoneNormaux->firstWhere('numero_tranche',1)->montant_paye ?? 0, 0, ',', ' ') }} DA)
                                + T2 Crédit Réel ({{ number_format($creditBancaire->montant_reel, 0, ',', ' ') }} DA)
                                @if($ovCreditDiff && $ovCreditDiff->paiement)
                                    + T3 Différence ({{ number_format($ovCreditDiff->montant_paye, 0, ',', ' ') }} DA)
                                @endif
                                = Complet.
                            </div>
                        @elseif($ovCreditReel && $diffCreditCalc > 0 && $ovCreditDiff && !$ovCreditDiff->paiement)
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>T3 en attente de paiement :</strong>
                                {{ number_format($ovCreditDiff->montant_paye, 2, ',', ' ') }} DA restants à payer.
                                Utilisez le bouton <strong>"Payer T3"</strong> dans la liste des OVs.
                            </div>
                        @elseif(!$ovCreditReel)
                            <div class="alert alert-info py-2 mb-0">
                                <i class="bi bi-info-circle me-1"></i>
                                Crédit enregistré — OVs T2/T3 en cours de génération.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════════════════
                 SECTION 4 : Formulaire nouvelle tranche
                 — MASQUÉ si crédit bancaire enregistré
            ══════════════════════════════════════════════════════ --}}
            @if(!$creditBancaire)
            <form action="{{ route('ov.store.lpa') }}" method="POST">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="code_loge"       value="{{ $code_loge }}">
                <input type="hidden" name="numero_tranche"  value="{{ $prochaineTranche }}">

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                        <h6 class="mb-0">
                            <i class="bi bi-calculator me-2"></i>
                            Tranche {{ $prochaineTranche }}/5 — {{ $pourcentage }}%
                            @if($prochaineTranche === 4 && $aideFnpos)
                                ((Prix − BNH) × 25% − FNPOS 500 000 DA)
                            @else
                                ({{ $pourcentage }}% × (Prix − BNH) {{ number_format($prix2, 0, ',', ' ') }} DA)
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">

                            <div class="col-md-2 text-center">
                                <div class="tranche-indicator">{{ $pourcentage }}%</div>
                                <small class="text-muted">Tranche {{ $prochaineTranche }}</small>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label-custom">Reste global disponible</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
                                    <input type="text" class="form-control"
                                           value="{{ number_format($baseCalcul, 2, ',', ' ') }} DA" readonly>
                                </div>
                                <small class="text-muted">Prix − BNH − versements précédents</small>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label-custom text-success">Montant de cette tranche</label>
                                <div class="input-group">
                                    <span class="input-group-text border-success text-success"><i class="bi bi-plus-circle"></i></span>
                                    <input type="text" class="form-control border-success text-success fw-bold"
                                           value="{{ number_format($montantTranche, 2, ',', ' ') }} DA" readonly>
                                </div>
                                <small class="text-muted">
                                    @if($prochaineTranche === 4 && $aideFnpos)
                                        ({{ number_format($prix2 * 25 / 100, 0, ',', ' ') }} − 500 000) DA
                                    @else
                                        {{ $pourcentage }}% × {{ number_format($prix2, 0, ',', ' ') }} DA
                                    @endif
                                </small>
                            </div>

                            @if($prochaineTranche > 1)
                            <div class="col-md-6">
                                <label class="form-label-custom">Total déjà versé</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                                    <input type="text" class="form-control"
                                           value="{{ number_format($totalPaye, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-{{ $prochaineTranche > 1 ? '6' : '12' }}">
                                <label class="form-label-custom text-danger">Reste après cette tranche</label>
                                <div class="input-group">
                                    <span class="input-group-text border-danger text-danger"><i class="bi bi-dash-circle"></i></span>
                                    <input type="text" class="form-control border-danger text-danger fw-bold"
                                           value="{{ number_format($montantRestant, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>
                        </div>

                        @php
                            $vspDejaFait = $ovsDoneNormaux->contains(fn($ov) => (bool)$ov->vsp);
                        @endphp

                        @if($vspDejaFait)
                            <div class="mt-4">
                                <div class="alert alert-success py-2 mb-0 d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                    <span>VSP (Visite Sur Place) déjà effectuée.</span>
                                </div>
                            </div>
                        @else
                            <div class="mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="vsp" id="vsp" value="1">
                                    <label class="form-check-label text-muted" for="vsp">
                                        <i class="bi bi-house-check me-1"></i>
                                        VSP (Visite Sur Place) effectué pour cette tranche
                                        <span class="badge bg-light text-dark border ms-1">Optionnel</span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-center mt-4">
                    @if(!$aideBnh)
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Vous devez d'abord enregistrer l'aide BNH avant de générer un OV LPA.
                        </div>
                    @else
                        <a href="{{ route('ov.index') }}" class="btn btn-secondary me-3">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm"
                                style="border-radius:8px">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Générer la Tranche {{ $prochaineTranche }}
                        </button>
                    @endif
                </div>
            </form>
            @else
            {{-- Crédit enregistré : pas de formulaire tranche, juste bouton retour --}}
            <div class="text-center mt-2 mb-4">
                <a href="{{ route('ov.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
            @endif

            {{-- ══════════════════════════════════════════════════════
                 SECTION 5 : CRÉDIT BANCAIRE
            ══════════════════════════════════════════════════════ --}}
            @if($peutAfficherCredit)
            <div class="mt-5">
                <hr>
                <div class="card border-0 shadow-sm credit-card-section">
                    <div class="card-header text-white" style="background: linear-gradient(45deg,#6f42c1,#5a3199);">
                        <h6 class="mb-0">
                            <i class="bi bi-bank me-2"></i>
                            Crédit Bancaire
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($creditBancaire)
                            <div class="row g-3">
                                <div class="col-md-3 text-center">
                                    <div class="text-muted small fw-semibold mb-1">Montant Attestation</div>
                                    <div class="fw-bold" style="font-size:1.1rem;color:#6f42c1;">
                                        {{ number_format($creditBancaire->montant_attestation, 2, ',', ' ') }} DA
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="text-muted small fw-semibold mb-1">Montant Réel Banque (T2)</div>
                                    <div class="fw-bold text-success" style="font-size:1.1rem;">
                                        {{ number_format($creditBancaire->montant_reel, 2, ',', ' ') }} DA
                                    </div>
                                    <small class="text-success"><i class="bi bi-bank me-1"></i>Payé par banque</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="text-muted small fw-semibold mb-1">Différence (T3)</div>
                                    <div class="fw-bold {{ $diffCreditCalc > 0 ? 'text-warning' : 'text-success' }}" style="font-size:1.1rem;">
                                        {{ number_format(max(0, $diffCreditCalc), 2, ',', ' ') }} DA
                                    </div>
                                    @if($diffCreditCalc > 0)
                                        @if($ovCreditDiff && $ovCreditDiff->paiement)
                                            <small class="text-success">✓ T3 payée</small>
                                        @elseif($ovCreditDiff)
                                            <small class="text-warning">⏳ T3 en attente de paiement</small>
                                        @else
                                            <small class="text-danger">⚠️ T3 non générée</small>
                                        @endif
                                    @else
                                        <small class="text-success">✓ Aucune différence</small>
                                    @endif
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="text-muted small fw-semibold mb-1">Date Attestation</div>
                                    <div class="fw-bold">
                                        {{ $creditBancaire->date_attestation?->format('d/m/Y') ?? '—' }}
                                    </div>
                                    @if($creditBancaire->pieces_jointes)
                                        <a href="{{ asset('storage/'.$creditBancaire->pieces_jointes) }}" target="_blank"
                                           class="btn btn-sm btn-outline-secondary mt-2">
                                            <i class="bi bi-paperclip me-1"></i>Voir PJ
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                @if($dossierSoldeResume)
                                    <div class="alert alert-success py-2 mb-0">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <strong>Dossier entièrement soldé.</strong>
                                        T1 ({{ number_format($ovsDoneNormaux->firstWhere('numero_tranche',1)->montant_paye ?? 0, 0, ',', ' ') }} DA)
                                        + T2 Crédit Réel ({{ number_format($creditBancaire->montant_reel, 0, ',', ' ') }} DA)
                                        @if($ovCreditDiff && $ovCreditDiff->paiement)
                                            + T3 Différence ({{ number_format($ovCreditDiff->montant_paye, 0, ',', ' ') }} DA)
                                        @endif
                                        = Complet.
                                    </div>
                                @elseif($ovCreditReel && $diffCreditCalc > 0 && $ovCreditDiff && !$ovCreditDiff->paiement)
                                    <div class="alert alert-warning py-2 mb-0">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>T3 en attente de paiement :</strong>
                                        {{ number_format($ovCreditDiff->montant_paye, 2, ',', ' ') }} DA restants à payer.
                                        Utilisez le bouton <strong>"Payer T3"</strong> dans la liste des OVs.
                                    </div>
                                @elseif(!$ovCreditReel)
                                    <div class="alert alert-info py-2 mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Crédit enregistré — OVs T2/T3 en cours de génération.
                                    </div>
                                @endif
                            </div>
                        @else
                            @if($aideFnpos)
                                <div class="text-center py-3">
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Aucun crédit bancaire enregistré. Si le souscripteur bénéficie d'un prêt bancaire, enregistrez-le ici.
                                    </p>
                                    <button type="button" class="btn btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#modalCredit">
                                        <i class="bi bi-bank me-2"></i>
                                        Enregistrer un Crédit Bancaire
                                    </button>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <div class="alert alert-warning py-2 mb-0 d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                        <span>
                                            <strong>Aide FNPOS requise</strong> avant d'enregistrer un crédit bancaire.
                                            Veuillez d'abord ajouter l'aide FNPOS ci-dessus.
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            @elseif(!$creditBancaire && $ovT2NormaleExiste)
            <div class="mt-5">
                <hr>
                <div class="alert alert-secondary d-flex align-items-center gap-2">
                    <i class="bi bi-lock-fill fs-5 text-muted"></i>
                    <span>
                        <strong>Crédit bancaire non disponible :</strong>
                        la Tranche 2 normale a déjà été générée. Le crédit bancaire n'est autorisé qu'après la Tranche 1 uniquement.
                    </span>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- MODAL BNH --}}
<div class="modal fade" id="modalBnh" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Enregistrer l'aide BNH</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ov.aide.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="type" value="bnh">
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Le montant BNH sera déduit du prix logement avant tout calcul de tranche.
                        Le N° de convention est automatiquement récupéré depuis le projet
                        @if($site && $site->num_convention_bnh)
                            : <strong>{{ $site->num_convention_bnh }}</strong>
                        @else
                            <span class="text-danger">(⚠ Non configuré).</span>
                        @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Montant BNH <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="montant" class="form-control" step="0.01" min="1" required>
                                <span class="input-group-text">DA</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">N° Décision <span class="text-danger">*</span></label>
                            <input type="text" name="num_decision" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Pièces jointes <small class="text-muted fw-normal">(optionnel)</small></label>
                            <input type="file" name="pieces_jointes" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-save me-1"></i> Enregistrer BNH</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL FNPOS --}}
<div class="modal fade" id="modalFnpos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Enregistrer l'aide FNPOS</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ov.aide.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="type" value="fnpos">
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Le montant FNPOS est <strong>fixe : 500 000 DA</strong>.
                        Il sera déduit <strong>uniquement lors du calcul de la Tranche 4</strong>.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Montant FNPOS</label>
                            <div class="input-group">
                                <input type="text" class="form-control fw-bold text-info" value="500 000 DA" readonly>
                                <span class="input-group-text">DA</span>
                            </div>
                            <small class="text-muted">Montant fixe non modifiable</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">N° Décision <span class="text-danger">*</span></label>
                            <input type="text" name="num_decision" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Pièces jointes <small class="text-muted fw-normal">(optionnel)</small></label>
                            <input type="file" name="pieces_jointes" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info"><i class="bi bi-save me-1"></i> Enregistrer FNPOS</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CRÉDIT BANCAIRE --}}
@if($peutAfficherCredit && !$creditBancaire && $aideFnpos)
<div class="modal fade" id="modalCredit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:linear-gradient(45deg,#6f42c1,#5a3199);">
                <h5 class="modal-title"><i class="bi bi-bank me-2"></i>Enregistrer un Crédit Bancaire</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ov.credit.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <div class="modal-body">
                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        En enregistrant un crédit bancaire, les tranches T2→T5 seront remplacées par :
                        <ul class="mb-0 mt-1">
                            <li><strong>T2 Crédit Réel</strong> : montant réel de la banque (payé automatiquement)</li>
                            <li><strong>T3 Différence</strong> : différence éventuelle entre attestation et montant réel (si applicable)</li>
                        </ul>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Montant Attestation <span class="text-danger">*</span>
                                <small class="text-muted fw-normal">(calculé automatiquement)</small>
                            </label>
                            <div class="input-group">
                                <input type="number" name="montant_attestation" class="form-control fw-bold"
                                       step="0.01" min="1" id="inp_attestation"
                                       value="{{ $montantAttestationAuto ?? '' }}" readonly>
                                <span class="input-group-text">DA</span>
                            </div>
                            @if($montantAttestationAuto)
                                <small class="text-muted">
                                    = (Prix − BNH) − T1 − FNPOS
                                    = {{ number_format($montantAttestationAuto, 2, ',', ' ') }} DA
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Montant Réel Banque <span class="text-danger">*</span>
                                <small class="text-muted fw-normal">(versé réellement)</small>
                            </label>
                            <div class="input-group">
                                <input type="number" name="montant_reel" class="form-control"
                                       step="0.01" min="1" id="inp_reel"
                                       oninput="calcDifference()" required>
                                <span class="input-group-text">DA</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div id="diff_alert" class="alert d-none py-2 mb-0"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date Attestation <span class="text-danger">*</span></label>
                            <input type="date" name="date_attestation" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date Versement Réel <small class="text-muted fw-normal">(optionnel)</small></label>
                            <input type="date" name="date_versement_reel" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Scan Attestation <small class="text-muted fw-normal">(optionnel)</small></label>
                            <input type="file" name="pieces_jointes" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn text-white fw-bold" style="background:linear-gradient(45deg,#6f42c1,#5a3199);">
                        <i class="bi bi-save me-1"></i> Enregistrer le Crédit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.getElementById('flash-alert');
    if (alert) {
        setTimeout(() => { new bootstrap.Alert(alert).close(); }, 4000);
    }
});

function calcDifference() {
    const att  = parseFloat(document.getElementById('inp_attestation')?.value) || 0;
    const reel = parseFloat(document.getElementById('inp_reel')?.value)        || 0;
    const div  = document.getElementById('diff_alert');
    if (!div) return;

    if (att > 0 && reel > 0) {
        const diff = att - reel;
        div.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        if (diff > 0) {
            div.className = 'alert alert-danger py-2 mb-0';
            div.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i>'
                + '<strong>Différence : ' + diff.toLocaleString('fr-DZ') + ' DA</strong>'
                + ' — Un OV T3 Différence sera généré automatiquement et mis en attente de paiement.';
        } else if (diff < 0) {
            div.className = 'alert alert-warning py-2 mb-0';
            div.innerHTML = '<i class="bi bi-info-circle me-1"></i>'
                + 'Le montant réel est supérieur à l\'attestation de '
                + Math.abs(diff).toLocaleString('fr-DZ') + ' DA.';
        } else {
            div.className = 'alert alert-success py-2 mb-0';
            div.innerHTML = '<i class="bi bi-check-circle me-1"></i>Montants identiques — dossier soldé automatiquement. Aucun OV T3 généré.';
        }
    } else {
        div.classList.add('d-none');
    }
}
</script>
</x-app-layout>