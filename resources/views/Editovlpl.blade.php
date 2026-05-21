<x-app-layout>
<style>
    .edit-card { border: none; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); overflow: hidden; }
    .edit-header { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; padding: 1.5rem 2rem; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
    .info-item { background: #f8f9fa; border-radius: 10px; padding: 0.9rem 1.1rem; border-left: 3px solid #2a5298; }
    .info-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: #636e72; font-weight: 600; }
    .info-val { font-size: 1rem; font-weight: 700; color: #2d3436; margin-top: 2px; font-family: 'Monaco', monospace; }
    .form-section { background: white; border-radius: 12px; border: 1px solid #e9ecef; padding: 1.5rem; }
    .calc-preview { background: linear-gradient(135deg, #e8f4fd, #f0f8ff); border: 1px solid #bee3f8; border-radius: 10px; padding: 1rem 1.2rem; }
    .calc-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 0.88rem; }
    .calc-row.total { border-top: 2px solid #2a5298; margin-top: 6px; padding-top: 8px; font-weight: 700; font-size: 0.95rem; color: #1e3c72; }
    .badge-prog { font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; font-weight: 700; }
    .alert-calcul { border-left: 4px solid #f39c12; background: #fffbf0; border-radius: 8px; padding: 0.8rem 1rem; }
    .pct-btn { display: none; }
    .pct-label {
        display: inline-flex; align-items: center; justify-content: center;
        width: 58px; height: 44px; border-radius: 8px; cursor: pointer;
        font-weight: 700; font-size: 0.9rem; border: 2px solid #dee2e6;
        background: #f8f9fa; color: #495057; transition: all 0.15s;
    }
    .pct-btn:checked + .pct-label {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
        color: white; border-color: #1e3c72;
        box-shadow: 0 4px 12px rgba(42,82,152,0.35);
    }
    .pct-label:hover { border-color: #2a5298; color: #2a5298; background: #eef3fc; }
</style>

<div class="container py-5" style="max-width:860px;">
    <div class="card edit-card">

        <div class="edit-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bi bi-pencil-square me-2"></i> Modifier l'Ordre de Versement — LPL</h5>
                <small class="opacity-75">Tranche {{ $ov->numero_tranche }} — OV #{{ $ov->id }}</small>
            </div>
            <span class="badge bg-secondary badge-prog">LPL</span>
        </div>

        <div class="card-body p-4">

            <div class="info-grid mb-4">
                <div class="info-item">
                    <div class="info-label">Souscripteur</div>
                    <div class="info-val" style="font-family:inherit; font-size:0.92rem;">
                        {{ strtoupper($souscripteur->nom) }} {{ $souscripteur->prenom }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Code logement</div>
                    <div class="info-val">{{ $code_loge }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Prix logement</div>
                    <div class="info-val text-success">{{ number_format($prixLogement, 2, ',', ' ') }} DA</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Reste disponible</div>
                    <div class="info-val text-primary">{{ number_format($reste, 2, ',', ' ') }} DA</div>
                </div>
                <div class="info-item">
                    <div class="info-label">N° Tranche</div>
                    <div class="info-val">T{{ $ov->numero_tranche }}</div>
                </div>
            </div>

            <div class="alert-calcul mb-4">
                <i class="bi bi-info-circle-fill text-warning me-2"></i>
                <strong>Règle LPL :</strong> Le montant est calculé selon le pourcentage choisi appliqué au prix du logement,
                plafonné au reste disponible. Valeurs autorisées : <strong>5 / 10 / 15 / 20 / 25 / 30 / 35 / 40 / 45 / 50 %</strong>.
            </div>

            <form method="POST" action="{{ route('ov.update', Hashids::encode($ov->id)) }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="pourcentage"  id="pourcentage_hidden"  value="{{ old('pourcentage', round($ov->pourcentage / 5) * 5) }}">
                <input type="hidden" name="montant_paye" id="montant_calc_hidden" value="{{ old('montant_paye', $ov->montant_paye) }}">

                <div class="form-section mb-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-sliders me-2"></i>Choisir le pourcentage</h6>

                    @php $currentPct = (int)(round($ov->pourcentage / 5) * 5); @endphp

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach([5,10,15,20,25,30,35,40,45,50] as $pct)
                            <input type="radio" class="pct-btn" name="_pct_radio" id="pct_{{ $pct }}"
                                   value="{{ $pct }}" {{ $currentPct === $pct ? 'checked' : '' }}
                                   onchange="updateCalcFromRadio({{ $pct }})">
                            <label class="pct-label" for="pct_{{ $pct }}">{{ $pct }}%</label>
                        @endforeach
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Montant calculé</label>
                            <input type="text" id="montant_calc_display"
                                   class="form-control fw-bold text-success fs-5 bg-light"
                                   readonly
                                   value="{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA">
                            <div class="form-text">Calculé automatiquement selon le pourcentage sélectionné</div>
                        </div>
                    </div>
                </div>

                <div class="calc-preview mb-4">
                    <div class="fw-bold mb-2 text-primary"><i class="bi bi-calculator me-1"></i> Aperçu du calcul</div>
                    <div class="calc-row">
                        <span>Prix logement</span>
                        <span>{{ number_format($prixLogement, 2, ',', ' ') }} DA</span>
                    </div>
                    <div class="calc-row">
                        <span>× Pourcentage</span>
                        <span id="prev_pct">{{ $currentPct }} %</span>
                    </div>
                    <div class="calc-row">
                        <span>= Montant théorique</span>
                        <span id="prev_theorique">—</span>
                    </div>
                    <div class="calc-row">
                        <span>Reste disponible</span>
                        <span>{{ number_format($reste, 2, ',', ' ') }} DA</span>
                    </div>
                    <div class="calc-row total">
                        <span><i class="bi bi-check-circle me-1"></i> Montant à payer</span>
                        <span id="prev_final" class="text-success">—</span>
                    </div>
                    <div class="calc-row">
                        <span class="text-muted">Reste après OV</span>
                        <span id="prev_reste" class="text-muted">—</span>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end">
                    <a href="{{ route('ov.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer la modification
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
const prixLogement = {{ $prixLogement }};
const reste        = {{ $reste }};

function formatDA(n) {
    return n.toLocaleString('fr-DZ', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' DA';
}

function updateCalcFromRadio(pct) {
    const theor  = Math.round(prixLogement * pct / 100 * 100) / 100;
    const final_ = Math.min(theor, reste);
    const rest2  = Math.max(0, reste - final_);

    document.getElementById('pourcentage_hidden').value   = pct;
    document.getElementById('montant_calc_hidden').value  = final_.toFixed(2);
    document.getElementById('montant_calc_display').value = formatDA(final_);
    document.getElementById('prev_pct').textContent       = pct + ' %';
    document.getElementById('prev_theorique').textContent = formatDA(theor);
    document.getElementById('prev_final').textContent     = formatDA(final_);
    document.getElementById('prev_reste').textContent     = formatDA(rest2);
}

// Init
updateCalcFromRadio({{ $currentPct > 0 ? $currentPct : 5 }});
</script>
</x-app-layout>