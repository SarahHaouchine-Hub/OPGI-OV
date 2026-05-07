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
    .input-group .form-control, .input-group .form-select { border-left: none; }
</style>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card custom-card" style="border:none; border-radius:15px">
        <div class="card-header card-header-gradient text-white" style="padding:1.5rem;">
            <h4 class="mb-0" style="font-size:18px">
                <i class="bi bi-file-earmark-text-fill me-2"></i>
                Générer OV LPL —
                <strong>{{ strtoupper($souscripteur->nom) }} {{ strtoupper($souscripteur->prenom) }}</strong>
            </h4>
            <small class="opacity-75">Code Logement : {{ $souscripteur->code_loge_lpl }}</small>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('ov.store') }}" method="POST">
                @csrf
                <input type="hidden" name="souscripteur_id" value="{{ $souscripteur->id }}">
                <input type="hidden" name="code_loge"       value="{{ $code_loge }}">

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header text-white" style="background-color: rgb(60 88 130);">
                        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i> Calcul du versement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">

                            {{-- Prix total --}}
                            <div class="col-md-6">
                                <label class="form-label-custom">Prix total logement</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-cash-stack"></i></span>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ number_format($prixLogement, 2, ',', ' ') }} DA"
                                           readonly>
                                    <input type="hidden" name="montant_total" value="{{ $prixLogement }}">
                                </div>
                            </div>

                            {{-- Reste à payer --}}
                            <div class="col-md-6">
                                <label class="form-label-custom text-danger">Solde restant</label>
                                <div class="input-group">
                                    <span class="input-group-text border-danger text-danger"><i class="bi bi-dash-circle"></i></span>
                                    <input type="text"
                                           class="form-control border-danger text-danger fw-bold"
                                           value="{{ number_format($reste, 2, ',', ' ') }} DA"
                                           readonly>
                                    <input type="hidden" name="solde_reste"     value="{{ $reste }}">
                                    <input type="hidden" name="montant_restant" value="{{ $reste }}">
                                </div>
                            </div>

                            {{-- Pourcentage --}}
                            <div class="col-md-4">
                                <label class="form-label-custom">Pourcentage (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                    <input type="number"
                                           class="form-control"
                                           name="pourcentage"
                                           id="pourcentage"
                                           min="5" max="50" step="5"
                                           placeholder="ex: 20"
                                           required
                                           oninput="calculerMontant()">
                                </div>
                            </div>

                            {{-- Montant à payer (calculé) --}}
                            <div class="col-md-4">
                                <label class="form-label-custom text-success">Montant à payer</label>
                                <div class="input-group">
                                    <span class="input-group-text border-success text-success"><i class="bi bi-plus-circle"></i></span>
                                    <input type="text"
                                           class="form-control border-success text-success fw-bold"
                                           id="montant_display"
                                           readonly
                                           placeholder="—">
                                    <input type="hidden" name="montant_a_payer" id="montant_a_payer">
                                </div>
                            </div>

                            {{-- Reste après ce versement --}}
                            <div class="col-md-4">
                                <label class="form-label-custom text-warning">Reste après versement</label>
                                <div class="input-group">
                                    <span class="input-group-text border-warning text-warning"><i class="bi bi-arrow-right-circle"></i></span>
                                    <input type="text"
                                           class="form-control border-warning fw-bold"
                                           id="reste_display"
                                           readonly
                                           placeholder="—">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm" style="border-radius:8px">
                        <i class="bi bi-check-circle-fill me-2"></i> Générer l'OV LPL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculerMontant() {
    const pourcentage = parseFloat(document.getElementById('pourcentage').value) || 0;
    const solde       = {{ $reste }};
    const total       = {{ $prixLogement }};

    const theorique  = (total * pourcentage) / 100;
    const aPayerNet  = Math.min(theorique, solde);
    const resteApres = Math.max(0, solde - aPayerNet);

    const fmt = v => v.toLocaleString('fr-DZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DA';

    document.getElementById('montant_display').value = fmt(aPayerNet);
    document.getElementById('montant_a_payer').value  = aPayerNet.toFixed(2);
    document.getElementById('reste_display').value    = fmt(resteApres);
}
</script>
</x-app-layout>