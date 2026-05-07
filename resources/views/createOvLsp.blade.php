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
    .aide-missing { border-left: 4px solid #dc3545; background: #fff8f8; }
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
            {{-- SECTION 2 : OV déjà généré (si existant) --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            @if($ovExistant)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Un OV LSP a déjà été généré pour ce souscripteur
                    ({{ number_format($ovExistant->montant_paye, 2, ',', ' ') }} DA).
                </div>
            @else

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- SECTION 3 : Calcul et formulaire --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <form action="{{ route('ov.store.lsp') }}" method="POST">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="code_loge"       value="{{ $code_loge }}">

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i> Calcul de l'OV LSP</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label-custom">Prix total logement</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-cash-stack"></i></span>
                                    <input type="text" class="form-control"
                                           value="{{ number_format($prixLogement, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label-custom text-primary">Total aides (CNL + FNPOS)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-primary text-primary"><i class="bi bi-gift"></i></span>
                                    <input type="text" class="form-control border-primary fw-bold"
                                           value="{{ number_format($totalAides, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label-custom text-success">Montant OV à verser</label>
                                <div class="input-group">
                                    <span class="input-group-text border-success text-success"><i class="bi bi-bank"></i></span>
                                    <input type="text" class="form-control border-success text-success fw-bold"
                                           value="{{ number_format($montantOv, 2, ',', ' ') }} DA" readonly>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    @if($montantOv <= 0)
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Le montant est entièrement couvert par les aides. Aucun OV à générer.
                        </div>
                    @else
                        <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm"
                                style="border-radius:8px">
                            <i class="bi bi-check-circle-fill me-2"></i> Générer l'OV LSP
                        </button>
                    @endif
                </div>
            </form>

            @endif {{-- fin @if($ovExistant) --}}

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- MODAL CNL --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Montant <span class="text-danger">*</span></label>
                            <input type="number" name="montant" class="form-control" step="0.01" required>
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
                            <label class="form-label fw-bold">Pièces jointes (PDF, JPG, PNG)</label>
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

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- MODAL FNPOS --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Montant <span class="text-danger">*</span></label>
                            <input type="number" name="montant" class="form-control" step="0.01" required>
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
                            <label class="form-label fw-bold">Pièces jointes (PDF, JPG, PNG)</label>
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
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => { const a = new bootstrap.Alert(alert); a.close(); }, 3000);
    }
});
</script>
</x-app-layout>