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
    .aide-card  { border-left: 4px solid #28a745; background: #f8fff9; }
</style>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card custom-card" style="border:none; border-radius:15px">
        <div class="card-header card-header-gradient text-white" style="padding:1.5rem;">
            <h4 class="mb-0" style="font-size:18px">
                <i class="bi bi-file-earmark-text-fill me-2"></i>
                Générer OV LSP —
                <strong>{{ strtoupper($souscripteur->nom) }} {{ strtoupper($souscripteur->prenom) }}</strong>
            </h4>
            <small class="opacity-75">Code Logement : {{ $souscripteur->code_loge_lpl }}</small>
        </div>

        <div class="card-body p-4">

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- SECTION 1 : Aides CNL / FNPOS --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <div class="row mb-4">
                {{-- CNL --}}
                <div class="col-md-6">
                    @if($aideCnl)
                        <div class="card aide-card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>Aide CNL enregistrée
                                </h6>
                                <div class="mb-2"><strong>Montant :</strong> {{ number_format($aideCnl->montant, 2, ',', ' ') }} DA</div>
                                <div class="mb-2"><strong>N° Convention :</strong> {{ $aideCnl->num_convention }}</div>
                                <div class="mb-2"><strong>N° Décision :</strong> {{ $aideCnl->num_decision }}</div>
                                <div><strong>Date :</strong> {{ $aideCnl->date->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="card border shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-info-circle text-info" style="font-size:2rem;"></i>
                                <p class="text-muted mb-2">Aide CNL optionnelle</p>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalCnl">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter CNL
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- FNPOS --}}
                <div class="col-md-6">
                    @if($aideFnpos)
                        <div class="card aide-card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="bi bi-check-circle-fill me-2"></i>Aide FNPOS enregistrée
                                </h6>
                                <div class="mb-2"><strong>Montant :</strong> {{ number_format($aideFnpos->montant, 2, ',', ' ') }} DA</div>
                                <div class="mb-2"><strong>N° Décision :</strong> {{ $aideFnpos->num_decision }}</div>
                                <div><strong>Date :</strong> {{ $aideFnpos->date->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="card border shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-info-circle text-info" style="font-size:2rem;"></i>
                                <p class="text-muted mb-2">Aide FNPOS optionnelle</p>
                                <button type="button" class="btn btn-info btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalFnpos">
                                    <i class="bi bi-plus-circle me-1"></i> Ajouter FNPOS
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- SECTION 2 : Historique des tranches déjà générées --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            @if($ovsDone->count() > 0)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header text-white" style="background-color: #6c757d;">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i> Tranches déjà versées</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tranche</th>
                                <th>Montant versé</th>
                                <th>Reste après</th>
                                <th>Date</th>
                                <th>PDF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ovsDone as $ov)
                            <tr>
                                <td><span class="badge bg-primary">T{{ $ov->numero_tranche }}</span></td>
                                <td class="fw-bold text-success">{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</td>
                                <td>{{ number_format($ov->montant_restant, 2, ',', ' ') }} DA</td>
                                <td>{{ $ov->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('ov.pdf', \Vinkla\Hashids\Facades\Hashids::encode($ov->id)) }}"
                                       target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- SECTION 3 : Formulaire nouvelle tranche --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            @if($resteAPayer <= 0)
                <div class="alert alert-success text-center fw-bold">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Ce logement est entièrement soldé. Aucune tranche supplémentaire à générer.
                </div>
            @else
            <form action="{{ route('ov.store.lsp') }}" method="POST">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="code_loge"       value="{{ $code_loge }}">

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                        <h6 class="mb-0">
                            <i class="bi bi-calculator me-2"></i>
                            Tranche N° {{ $prochaineTranche }} — Saisie du versement
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label class="form-label-custom">Prix total logement</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-cash-stack"></i></span>
                                    <input type="text" class="form-control"
                                           value="{{ number_format($prixLogement, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label-custom text-primary">Total aides</label>
                                <div class="input-group">
                                    <span class="input-group-text border-primary text-primary"><i class="bi bi-gift"></i></span>
                                    <input type="text" class="form-control border-primary fw-bold"
                                           value="{{ number_format($totalAides, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label-custom text-warning">Reste à payer</label>
                                <div class="input-group">
                                    <span class="input-group-text border-warning text-warning"><i class="bi bi-hourglass-split"></i></span>
                                    <input type="text" class="form-control border-warning fw-bold text-warning"
                                           value="{{ number_format($resteAPayer, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label-custom text-success">
                                    Montant à verser <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text border-success text-success"><i class="bi bi-bank"></i></span>
                                    <input type="number"
                                           name="montant_a_payer"
                                           class="form-control border-success fw-bold"
                                           step="0.01"
                                           min="1"
                                           max="{{ $resteAPayer }}"
                                           placeholder="Saisir le montant"
                                           required
                                           id="montantInput">
                                </div>
                                <small class="text-muted">Maximum : {{ number_format($resteAPayer, 2, ',', ' ') }} DA</small>
                            </div>

                        </div>

                        {{-- Résumé dynamique --}}
                        <div class="row mt-3" id="resumeDiv" style="display:none!important">
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <strong>Reste après ce versement :</strong>
                                    <span id="resteAffiche" class="fw-bold text-danger ms-2"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm"
                            style="border-radius:8px">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Générer la tranche {{ $prochaineTranche }}
                    </button>
                </div>
            </form>
            @endif

        </div>
    </div>
</div>

{{-- MODAL CNL --}}
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
                        Le montant CNL sera déduit du reste à payer.
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
                            <input type="file" name="pieces_jointes" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
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
                        Le montant FNPOS sera déduit du reste à payer.
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
                            <input type="file" name="pieces_jointes" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
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
const resteMax = {{ $resteAPayer }};

document.getElementById('montantInput')?.addEventListener('input', function () {
    const val   = parseFloat(this.value) || 0;
    const reste = Math.max(0, resteMax - val);
    const div   = document.getElementById('resumeDiv');
    const span  = document.getElementById('resteAffiche');

    if (val > 0 && val <= resteMax) {
        span.textContent = new Intl.NumberFormat('fr-DZ', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        }).format(reste) + ' DA';
        div.style.removeProperty('display');
    } else {
        div.style.setProperty('display', 'none', 'important');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const alert = document.querySelector('.alert-dismissible');
    if (alert) setTimeout(() => { new bootstrap.Alert(alert).close(); }, 3000);
});
</script>
</x-app-layout>