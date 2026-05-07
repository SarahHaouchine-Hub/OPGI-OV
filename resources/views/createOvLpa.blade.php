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

    <div class="card custom-card" style="border:none; border-radius:15px">
        <div class="card-header card-header-gradient text-white" style="padding:1.5rem;">
            <h4 class="mb-0" style="font-size:18px">
                <i class="bi bi-file-earmark-text-fill me-2"></i>
                Générer OV LPA — Tranche {{ $prochaineTranche }}/5 —
                <strong>{{ strtoupper($souscripteur->nom) }} {{ strtoupper($souscripteur->prenom) }}</strong>
            </h4>
            <small class="opacity-75">Code Logement : {{ $souscripteur->code_loge_lpl }}</small>
        </div>

        <div class="card-body p-4">

            {{-- ══════════════════════════════════════════════════════
                 RÉCAPITULATIF FINANCIER
            ══════════════════════════════════════════════════════ --}}
          @php
    $totalAides = ($aideCnl->montant ?? 0) + ($aideFnpos->montant ?? 0);
    // $baseCalcul est déjà calculé correctement dans le controller
    $labelBase = $prochaineTranche === 1
        ? "Prix − Aides"
        : "Reste après T" . ($prochaineTranche - 1) . " − Aides";
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
                        <div class="text-warning-emphasis small mb-1 fw-semibold">Total Aides</div>
                        <div class="fw-bold text-warning-emphasis" style="font-size:1rem;">
                            {{ number_format($totalAides, 2, ',', ' ') }} <small>DA</small>
                        </div>
                        <div class="mt-1" style="font-size:0.72rem; color:#6c757d;">
                            CNL : {{ number_format($aideCnl->montant ?? 0, 2, ',', ' ') }} DA
                            &nbsp;|&nbsp;
                            FNPOS : {{ number_format($aideFnpos->montant ?? 0, 2, ',', ' ') }} DA
                        </div>
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
                        <small class="text-success">{{ $prochaineTranche - 1 }} tranche(s)</small>
                    </div>
                </div>
                @endif
                
                <div class="col-auto arrow-separator">=</div>
                <div class="col">
                    <div class="recap-box text-center" style="background:#d1e7dd; border:2px solid #0f5132;">
                        <div class="text-success-emphasis small mb-1 fw-semibold">
                            Base T{{ $prochaineTranche }}
                        </div>
                        <div class="fw-bold text-success" style="font-size:1.1rem;">
                            {{ number_format($baseCalcul, 2, ',', ' ') }} <small>DA</small>
                        </div>
                        <small class="text-success-emphasis">{{ $labelBase }}</small>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 SECTION 1 : Aides CNL / FNPOS
            ══════════════════════════════════════════════════════ --}}
            <div class="row mb-4">

                {{-- CNL --}}
                <div class="col-md-6 mb-3">
                    @if($aideCnl)
                        <div class="card aide-card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>Aide CNL enregistrée
                                </h6>
                                <div class="mb-2"><strong>Montant :</strong> {{ number_format($aideCnl->montant, 2, ',', ' ') }} DA</div>
                                <div class="mb-2"><strong>N° Convention :</strong> {{ $aideCnl->num_convention }}</div>
                                <div class="mb-2"><strong>N° Décision :</strong> {{ $aideCnl->num_decision }}</div>
                                <div><strong>Date :</strong> {{ $aideCnl->date->format('d/m/Y') }}</div>
                                @if($aideCnl->pieces_jointes)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$aideCnl->pieces_jointes) }}" target="_blank"
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
                                <p class="text-danger fw-bold mb-3">Aide CNL manquante <span class="badge bg-danger">Obligatoire</span></p>
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalCnl">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter CNL
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- FNPOS --}}
                <div class="col-md-6 mb-3">
                    @if($aideFnpos)
                        <div class="card aide-card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>Aide FNPOS enregistrée
                                </h6>
                                <div class="mb-2"><strong>Montant :</strong> {{ number_format($aideFnpos->montant, 2, ',', ' ') }} DA</div>
                                <div class="mb-2"><strong>N° Décision :</strong> {{ $aideFnpos->num_decision }}</div>
                                <div><strong>Date :</strong> {{ $aideFnpos->date->format('d/m/Y') }}</div>
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
                                <small class="text-muted mb-3">
                                    @if($prochaineTranche === 1)
                                        Si ajoutée avant la T2, elle réduira les tranches restantes.
                                    @else
                                        Ajoutée ! Elle réduit déjà le calcul de cette tranche.
                                    @endif
                                </small>
                                @if(!$aideFnpos)
                                <button type="button" class="btn btn-info btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalFnpos">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter FNPOS
                                </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 SECTION 2 : Historique des OVs déjà générés
            ══════════════════════════════════════════════════════ --}}
            @if($ovsDone->count() > 0)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-list-check me-2"></i> OVs déjà générés ({{ $ovsDone->count() }}/5)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Tranche</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Montant versé</th>
                                    <th class="text-center">Statut paiement</th>
                                    <th class="text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ovsDone as $ovDone)
                                <tr>
                                    <td class="text-center fw-bold">T{{ $ovDone->numero_tranche }}</td>
                                    <td class="text-center">{{ $ovDone->pourcentage }}%</td>
                                    <td class="text-center">{{ number_format($ovDone->montant_paye, 2, ',', ' ') }} DA</td>
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
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">Total versé :</td>
                                    <td class="text-center text-success">{{ number_format($totalPaye, 2, ',', ' ') }} DA</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════════════════
                 SECTION 3 : Progression des 5 tranches
            ══════════════════════════════════════════════════════ --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                    <h6 class="mb-0">
                        <i class="bi bi-bar-chart-steps me-2"></i>
                        Progression des tranches
                        @if($prochaineTranche === 1)
                            — T1 calculée sur {{ number_format($prixLogement - ($aideCnl->montant ?? 0), 2, ',', ' ') }} DA (Prix - CNL)
                        @else
                            — T{{ $prochaineTranche }} calculée sur le reste : {{ number_format($baseCalcul, 2, ',', ' ') }} DA
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        @foreach($tranches as $num => $pct)
                        @php
                            $isDone    = $ovsDone->contains('numero_tranche', $num);
                            $isCurrent = ($num === $prochaineTranche);
                            
                            // Calcul du montant selon la tranche
                            if ($num === 1) {
                                $baseTranche = $prixLogement - ($aideCnl->montant ?? 0);
                            } else {
                                // Pour les tranches futures, on estime avec les aides actuelles
                                $baseTranche = max(0, $prixLogement - $totalPaye - $totalAides);
                            }
                            $montantT = ($baseTranche * $pct) / 100;
                        @endphp
                        <div class="text-center flex-fill">
                            <div class="tranche-badge mb-1
                                {{ $isDone    ? 'bg-success text-white'  : '' }}
                                {{ $isCurrent ? 'bg-primary text-white'  : '' }}
                                {{ !$isDone && !$isCurrent ? 'bg-light text-muted border' : '' }}">
                                T{{ $num }}
                            </div>
                            <div class="fw-bold {{ $isCurrent ? 'text-primary' : ($isDone ? 'text-success' : 'text-muted') }}">
                                {{ $pct }}%
                            </div>
                            <small class="d-block text-muted" style="font-size:0.72rem;">
                                {{ number_format($montantT, 0, ',', ' ') }} DA
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

            {{-- ══════════════════════════════════════════════════════
                 SECTION 4 : Formulaire nouvelle tranche
            ══════════════════════════════════════════════════════ --}}
            <form action="{{ route('ov.store.lpa') }}" method="POST">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="code_loge"       value="{{ $code_loge }}">
                <input type="hidden" name="numero_tranche"  value="{{ $prochaineTranche }}">

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                        <h6 class="mb-0">
                            <i class="bi bi-calculator me-2"></i>
                            Tranche {{ $prochaineTranche }}/5 — {{ $pourcentage }}% ×
                            {{ number_format($baseCalcul, 2, ',', ' ') }} DA
                            @if($prochaineTranche === 1)
                                (Prix - CNL)
                            @else
                                (Reste après aides et versements)
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
                                <label class="form-label-custom">Base de calcul T{{ $prochaineTranche }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calculator"></i></span>
                                    <input type="text" class="form-control"
                                           value="{{ number_format($baseCalcul, 2, ',', ' ') }} DA" readonly>
                                </div>
                                <small class="text-muted">
                                    @if($prochaineTranche === 1)
                                        {{ number_format($prixLogement, 2, ',', ' ') }} DA
                                        − {{ number_format($aideCnl->montant ?? 0, 2, ',', ' ') }} DA (CNL)
                                    @else
                                        Prix − Versé − Aides
                                    @endif
                                </small>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label-custom text-success">Montant de cette tranche</label>
                                <div class="input-group">
                                    <span class="input-group-text border-success text-success"><i class="bi bi-plus-circle"></i></span>
                                    <input type="text" class="form-control border-success text-success fw-bold"
                                           value="{{ number_format($montantTranche, 2, ',', ' ') }} DA" readonly>
                                </div>
                                <small class="text-muted">
                                    {{ $pourcentage }}% × {{ number_format($baseCalcul, 2, ',', ' ') }} DA
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
                                           value="{{ number_format(max(0, $baseCalcul - $montantTranche), 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>

                        </div>

                        {{-- VSP uniquement pour la tranche 2 --}}
                        @if($prochaineTranche === 2)
                        <div class="alert alert-warning mt-4 mb-0" role="alert">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="vsp" id="vsp" value="1" required>
                                <label class="form-check-label fw-bold" for="vsp">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Je confirme que le VSP (Visite Sur Place) a été effectué
                                    — obligatoire pour générer la tranche 2
                                </label>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                <div class="text-center mt-4">
                    @if(!$aideCnl)
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Vous devez d'abord enregistrer l'aide CNL avant de générer un OV LPA.
                        </div>
                    @else
                        <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm"
                                style="border-radius:8px">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Générer la Tranche {{ $prochaineTranche }}
                        </button>
                    @endif
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL CNL
══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCnl" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Enregistrer l'aide CNL</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ov.aide.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="type" value="cnl">
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        @if($prochaineTranche === 1)
                            Le montant CNL sera déduit du prix avant le calcul de la T1.
                        @else
                            Le montant CNL réduit déjà la base de calcul des tranches.
                        @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Montant <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="montant" class="form-control" step="0.01" min="1" required>
                                <span class="input-group-text">DA</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">N° Convention <span class="text-danger">*</span></label>
                            <input type="text" name="num_convention" class="form-control" required>
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
                            <label class="form-label fw-bold">
                                Pièces jointes <small class="text-muted fw-normal">(PDF, JPG, PNG — optionnel)</small>
                            </label>
                            <input type="file" name="pieces_jointes" class="form-control"
                                   accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-save me-1"></i> Enregistrer CNL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     MODAL FNPOS
══════════════════════════════════════════════════════════════════ --}}
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
                        @if($prochaineTranche === 1)
                            Le montant FNPOS sera déduit avant le calcul de la tranche 2.
                        @else
                            Le montant FNPOS réduit déjà la base de calcul de cette tranche et des suivantes.
                        @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Montant <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-bold">
                                Pièces jointes <small class="text-muted fw-normal">(PDF, JPG, PNG — optionnel)</small>
                            </label>
                            <input type="file" name="pieces_jointes" class="form-control"
                                   accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-save me-1"></i> Enregistrer FNPOS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.getElementById('flash-alert');
    if (alert) {
        setTimeout(() => { new bootstrap.Alert(alert).close(); }, 3000);
    }
});
</script>
</x-app-layout>