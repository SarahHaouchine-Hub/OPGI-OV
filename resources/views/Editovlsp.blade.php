<x-app-layout>
<style>
    .edit-card { border: none; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); overflow: hidden; }
    .edit-header { background: linear-gradient(45deg, #0a3d62, #1289A7); color: white; padding: 1.5rem 2rem; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
    .info-item { background: #f8f9fa; border-radius: 10px; padding: 0.9rem 1.1rem; border-left: 3px solid #1289A7; }
    .info-item.aide { border-left-color: #6c757d; }
    .info-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: #636e72; font-weight: 600; }
    .info-val { font-size: 1rem; font-weight: 700; color: #2d3436; margin-top: 2px; font-family: 'Monaco', monospace; }
    .form-section { background: white; border-radius: 12px; border: 1px solid #e9ecef; padding: 1.5rem; }
    .calc-preview { background: linear-gradient(135deg, #e0f7fa, #f0fdff); border: 1px solid #b2ebf2; border-radius: 10px; padding: 1rem 1.2rem; }
    .calc-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 0.88rem; }
    .calc-row.total { border-top: 2px solid #1289A7; margin-top: 6px; padding-top: 8px; font-weight: 700; font-size: 0.95rem; color: #0a3d62; }
    .alert-calcul { border-left: 4px solid #17a2b8; background: #f0fdff; border-radius: 8px; padding: 0.8rem 1rem; }
    .aide-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; font-weight: 600;
                  padding: 3px 10px; border-radius: 20px; }
    .aide-bnh   { background: #d1ecf1; color: #0c5460; }
    .aide-fnpos { background: #d4edda; color: #155724; }
    .aide-none  { background: #f8d7da; color: #721c24; }
</style>

<div class="container py-5" style="max-width:860px;">
    <div class="card edit-card">

        {{-- EN-TÊTE --}}
        <div class="edit-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bi bi-pencil-square me-2"></i> Modifier l'Ordre de Versement — LSP</h5>
                <small class="opacity-75">Tranche {{ $ov->numero_tranche }} — OV #{{ $ov->id }}</small>
            </div>
            <span class="badge bg-info text-dark" style="font-size:0.75rem;padding:4px 12px;border-radius:20px;font-weight:700;">LSP</span>
        </div>

        <div class="card-body p-4">

            {{-- INFOS SOUSCRIPTEUR --}}
            <div class="info-grid mb-4">
                <div class="info-item">
                    <div class="info-label">Souscripteur</div>
                    <div class="info-val" style="font-family:inherit;font-size:0.92rem;">
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
                <div class="info-item aide">
                    <div class="info-label">Total aides déduites</div>
                    <div class="info-val text-danger">− {{ number_format($totalAides, 2, ',', ' ') }} DA</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Reste à payer</div>
                    <div class="info-val text-primary">{{ number_format($resteAPayer, 2, ',', ' ') }} DA</div>
                </div>
                <div class="info-item">
                    <div class="info-label">N° Tranche</div>
                    <div class="info-val">T{{ $ov->numero_tranche }}</div>
                </div>
            </div>

            {{-- BADGES AIDES --}}
            <div class="d-flex gap-2 mb-4 flex-wrap">
                @if($aideBnh)
                    <span class="aide-badge aide-bnh">
                        <i class="bi bi-building-check"></i>
                        BNH : {{ number_format($aideBnh->montant, 2, ',', ' ') }} DA
                    </span>
                @else
                    <span class="aide-badge aide-none"><i class="bi bi-x-circle"></i> Pas d'aide BNH</span>
                @endif

                @if($aideFnpos)
                    <span class="aide-badge aide-fnpos">
                        <i class="bi bi-shield-check"></i>
                        FNPOS : 500 000,00 DA
                    </span>
                @endif
            </div>

            {{-- ALERTE info logique --}}
            <div class="alert-calcul mb-4">
                <i class="bi bi-info-circle-fill text-info me-2"></i>
                <strong>Règle LSP :</strong> Saisir le montant librement entre <strong>1 DA</strong>
                et le reste disponible (<strong>{{ number_format($resteAPayer, 2, ',', ' ') }} DA</strong>).
                Le pourcentage est calculé automatiquement.
            </div>

            {{-- FORMULAIRE --}}
            <form method="POST" action="{{ route('ov.update', Hashids::encode($ov->id)) }}">
                @csrf
                @method('PUT')

                <div class="form-section mb-4">
                    <h6 class="fw-bold mb-3" style="color:#1289A7;"><i class="bi bi-cash-coin me-2"></i>Montant de la tranche</h6>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Montant à payer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="montant_a_payer" id="montant_a_payer"
                                       class="form-control fw-bold fs-5"
                                       step="0.01" min="1" max="{{ $resteAPayer }}"
                                       value="{{ old('montant_a_payer', $ov->montant_paye) }}"
                                       oninput="updateCalcLsp()"
                                       required>
                                <span class="input-group-text">DA</span>
                            </div>
                            <div class="form-text">Maximum : {{ number_format($resteAPayer, 2, ',', ' ') }} DA</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-muted">Pourcentage calculé</label>
                            <div class="input-group">
                                <input type="text" id="pct_auto" class="form-control text-center fw-bold bg-light" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- APERÇU --}}
                <div class="calc-preview mb-4">
                    <div class="fw-bold mb-2" style="color:#1289A7;"><i class="bi bi-calculator me-1"></i> Aperçu du calcul</div>
                    <div class="calc-row">
                        <span>Prix logement</span>
                        <span>{{ number_format($prixLogement, 2, ',', ' ') }} DA</span>
                    </div>
                    <div class="calc-row text-danger">
                        <span>− Total aides</span>
                        <span>{{ number_format($totalAides, 2, ',', ' ') }} DA</span>
                    </div>
                    <div class="calc-row">
                        <span>= Reste à payer</span>
                        <span>{{ number_format($resteAPayer, 2, ',', ' ') }} DA</span>
                    </div>
                    <div class="calc-row total">
                        <span><i class="bi bi-check-circle me-1"></i> Montant cette tranche</span>
                        <span id="prev_montant" class="text-success">—</span>
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
                    <button type="submit" class="btn btn-info text-white px-4">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer la modification
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
const prixLogement = {{ $prixLogement }};
const totalAides   = {{ $totalAides }};
const resteAPayer  = {{ $resteAPayer }};

function formatDA(n) {
    return n.toLocaleString('fr-DZ', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' DA';
}

function updateCalcLsp() {
    const montant  = parseFloat(document.getElementById('montant_a_payer').value) || 0;
    const base     = prixLogement - totalAides;
    const pct      = base > 0 ? Math.round(montant / base * 10000) / 100 : 0;
    const reste    = Math.max(0, resteAPayer - montant);

    document.getElementById('pct_auto').value           = pct.toFixed(2);
    document.getElementById('prev_montant').textContent  = formatDA(montant);
    document.getElementById('prev_reste').textContent    = formatDA(reste);
}

// Init
updateCalcLsp();
</script>
</x-app-layout>