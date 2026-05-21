<x-app-layout>
<style>
    .edit-card { border: none; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.08); overflow: hidden; }
    .edit-header { background: linear-gradient(45deg, #7f4f00, #c67c00); color: white; padding: 1.5rem 2rem; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 1rem; }
    .info-item { background: #f8f9fa; border-radius: 10px; padding: 0.9rem 1.1rem; border-left: 3px solid #c67c00; }
    .info-item.aide  { border-left-color: #6c757d; }
    .info-item.calc  { border-left-color: #2a5298; background: #eef3fc; }
    .info-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; color: #636e72; font-weight: 600; }
    .info-val { font-size: 1rem; font-weight: 700; color: #2d3436; margin-top: 2px; font-family: 'Monaco', monospace; }
    .tranche-steps { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 1rem; }
    .tranche-step { display: flex; flex-direction: column; align-items: center; justify-content: center;
                    width: 64px; height: 64px; border-radius: 12px; font-weight: 700; font-size: 0.78rem;
                    border: 2px solid transparent; transition: all 0.2s; }
    .tranche-step.active   { background: #c67c00; color: white; border-color: #7f4f00; box-shadow: 0 4px 12px rgba(198,124,0,0.35); }
    .tranche-step.done     { background: #d4edda; color: #155724; border-color: #c3e6cb; }
    .tranche-step.pending  { background: #f8f9fa; color: #adb5bd; border-color: #dee2e6; }
    .calc-preview { background: linear-gradient(135deg, #fffbf0, #fff8e1); border: 1px solid #ffe082; border-radius: 10px; padding: 1rem 1.2rem; }
    .calc-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 0.88rem; }
    .calc-row.total { border-top: 2px solid #c67c00; margin-top: 6px; padding-top: 8px; font-weight: 700; font-size: 0.95rem; color: #7f4f00; }
    .alert-calcul { border-left: 4px solid #f39c12; background: #fffbf0; border-radius: 8px; padding: 0.8rem 1rem; }
    .alert-credit { border-left: 4px solid #6f42c1; background: #f8f4ff; border-radius: 8px; padding: 0.8rem 1rem; }
    .readonly-field { background: #f8f9fa; border: 1.5px dashed #ced4da; border-radius: 8px; padding: 0.6rem 1rem;
                      font-family: 'Monaco', monospace; font-weight: 700; font-size: 1.05rem; color: #2d3436; }
    .aide-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; font-weight: 600;
                  padding: 3px 10px; border-radius: 20px; }
    .aide-bnh   { background: #d1ecf1; color: #0c5460; }
    .aide-fnpos { background: #d4edda; color: #155724; }
    .aide-none  { background: #f8d7da; color: #721c24; }
    .form-section { background: white; border-radius: 12px; border: 1px solid #e9ecef; padding: 1.5rem; }
</style>

<div class="container py-5" style="max-width:900px;">
    <div class="card edit-card">

        {{-- EN-TÊTE --}}
        <div class="edit-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bi bi-pencil-square me-2"></i> Modifier l'Ordre de Versement — LPA</h5>
                <small class="opacity-75">Tranche {{ $tranche }} — OV #{{ $ov->id }}</small>
            </div>
            <span class="badge bg-warning text-dark" style="font-size:0.75rem;padding:4px 12px;border-radius:20px;font-weight:700;">LPA</span>
        </div>

        <div class="card-body p-4">

            @if($ov->type_ov !== null)
                {{-- OV crédit bancaire : non modifiable --}}
                <div class="alert-credit mb-4">
                    <i class="bi bi-lock-fill me-2" style="color:#6f42c1;"></i>
                    <strong>OV de type crédit bancaire</strong> — Cet ordre de versement est généré automatiquement
                    par le système lors de l'enregistrement du crédit bancaire.
                    Il ne peut pas être modifié manuellement.
                </div>
                <a href="{{ route('ov.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Retour à la liste
                </a>

            @else

                {{-- INFOS SOUSCRIPTEUR --}}
                <div class="info-grid mb-4">
                    <div class="info-item">
                        <div class="info-label">Souscripteur</div>
                        <div class="info-val" style="font-family:inherit;font-size:0.9rem;">
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
                        <div class="info-label">Aide BNH</div>
                        <div class="info-val text-danger">
                            @if($aideBnh)
                                − {{ number_format($montantBnh, 2, ',', ' ') }} DA
                            @else
                                <span class="text-muted fs-6">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item calc">
                        <div class="info-label">Prix² (base de calcul)</div>
                        <div class="info-val text-primary">{{ number_format($prix2, 2, ',', ' ') }} DA</div>
                    </div>
                    <div class="info-item calc">
                        <div class="info-label">Base disponible (T{{ $tranche }})</div>
                        <div class="info-val text-primary">{{ number_format($baseCalcul, 2, ',', ' ') }} DA</div>
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
                            FNPOS : {{ number_format($fnposMontant, 2, ',', ' ') }} DA
                            @if($tranche === 4) (déduite T4) @endif
                        </span>
                    @endif
                    @if($creditBancaire)
                        <span class="aide-badge" style="background:#ede7f6;color:#4a148c;">
                            <i class="bi bi-bank"></i>
                            Crédit enregistré
                        </span>
                    @endif
                </div>

                {{-- PROGRESSION TRANCHES --}}
                <div class="mb-4">
                    <div class="info-label mb-2">Progression des tranches LPA</div>
                    <div class="tranche-steps">
                        @foreach([1=>25, 2=>15, 3=>35, 4=>25, 5=>5] as $t => $pct)
                            @php
                                $isCurrent = $t === $tranche;
                                $isDone    = $souscripteur->ovs->where('type_ov', null)->where('numero_tranche', $t)->where('id', '!=', $ov->id)->isNotEmpty();
                            @endphp
                            <div class="tranche-step {{ $isCurrent ? 'active' : ($isDone ? 'done' : 'pending') }}">
                                <div>T{{ $t }}</div>
                                <div style="font-size:0.7rem;margin-top:2px;">{{ $pct }}%</div>
                                @if($isDone)
                                    <i class="bi bi-check-circle-fill" style="font-size:0.65rem;margin-top:2px;"></i>
                                @elseif($isCurrent)
                                    <i class="bi bi-pencil-fill" style="font-size:0.65rem;margin-top:2px;"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- INFO logique LPA --}}
                <div class="alert-calcul mb-4">
                    <i class="bi bi-info-circle-fill text-warning me-2"></i>
                    <strong>Règle LPA :</strong>
                    Le montant de la tranche {{ $tranche }} est <strong>calculé automatiquement</strong>
                    selon les règles LPA ({{ $tranche < 5 ? ($tranches[$tranche] ?? 0).'% de Prix²' : 'solde du reste' }}).
                    @if($tranche === 4 && $aideFnpos)
                        L'aide FNPOS ({{ number_format($fnposMontant, 2, ',', ' ') }} DA) est déduite de cette tranche.
                    @endif
                    Seul le <strong>VSP</strong> peut être modifié manuellement.
                </div>

                {{-- FORMULAIRE --}}
                <form method="POST" action="{{ route('ov.update', Hashids::encode($ov->id)) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-section mb-4">
                        <h6 class="fw-bold mb-3 text-warning"><i class="bi bi-calculator me-2"></i>Montants recalculés — Tranche {{ $tranche }}</h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted">Pourcentage</label>
                                <div class="readonly-field text-center">
                                    {{ $tranche < 5 ? ($tranches[$tranche] ?? 0) : round(($baseCalcul / max(1, $prix2)) * 100, 2) }} %
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted">Montant à payer</label>
                                <div class="readonly-field text-success text-center">
                                    {{ number_format($montantTranche, 2, ',', ' ') }} DA
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted">Montant restant</label>
                                <div class="readonly-field text-primary text-center">
                                    {{ number_format($montantRestant, 2, ',', ' ') }} DA
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- APERÇU DU CALCUL --}}
                    <div class="calc-preview mb-4">
                        <div class="fw-bold mb-2 text-warning"><i class="bi bi-calculator me-1"></i> Détail du calcul LPA</div>
                        <div class="calc-row">
                            <span>Prix logement</span>
                            <span>{{ number_format($prixLogement, 2, ',', ' ') }} DA</span>
                        </div>
                        <div class="calc-row text-danger">
                            <span>− Aide BNH</span>
                            <span>{{ number_format($montantBnh, 2, ',', ' ') }} DA</span>
                        </div>
                        <div class="calc-row">
                            <span>= Prix² (base)</span>
                            <span>{{ number_format($prix2, 2, ',', ' ') }} DA</span>
                        </div>
                        <div class="calc-row text-muted">
                            <span>− Total T1→T{{ $tranche - 1 }} payées</span>
                            <span>{{ number_format($prix2 - $baseCalcul, 2, ',', ' ') }} DA</span>
                        </div>
                        <div class="calc-row">
                            <span>= Base disponible</span>
                            <span>{{ number_format($baseCalcul, 2, ',', ' ') }} DA</span>
                        </div>
                        @if($tranche === 4 && $aideFnpos)
                        <div class="calc-row text-success">
                            <span>× {{ $tranches[$tranche] ?? 0 }}% − FNPOS</span>
                            <span>{{ number_format($montantTranche, 2, ',', ' ') }} DA</span>
                        </div>
                        @endif
                        <div class="calc-row total">
                            <span><i class="bi bi-check-circle me-1"></i> Montant T{{ $tranche }}</span>
                            <span class="text-success">{{ number_format($montantTranche, 2, ',', ' ') }} DA</span>
                        </div>
                        <div class="calc-row">
                            <span class="text-muted">Reste après T{{ $tranche }}</span>
                            <span class="text-muted">{{ number_format($montantRestant, 2, ',', ' ') }} DA</span>
                        </div>
                    </div>

                    {{-- VSP --}}
                    @php
                        $vspDejaFait = $souscripteur->ovs->where('id', '!=', $ov->id)->contains(fn($o) => (bool) $o->vsp);
                    @endphp
                    @if(!$vspDejaFait)
                    <div class="form-section mb-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-house-check me-2"></i>Visite de Conformité (VSP)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="vsp" id="vsp"
                                   value="1" {{ $ov->vsp ? 'checked' : '' }} style="width:3rem;height:1.5rem;">
                            <label class="form-check-label ms-2 fw-semibold" for="vsp">
                                VSP effectué pour cet OV
                            </label>
                        </div>
                        <small class="text-muted">Le VSP ne peut être activé qu'une seule fois sur l'ensemble du dossier.</small>
                    </div>
                    @else
                    <div class="form-section mb-4">
                        <div class="d-flex align-items-center gap-2 text-success">
                            <i class="bi bi-house-check-fill fs-5"></i>
                            <span class="fw-semibold">VSP déjà enregistré sur un autre OV de ce dossier</span>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex gap-3 justify-content-end">
                        <a href="{{ route('ov.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="bi bi-check-lg me-1"></i> Confirmer la mise à jour
                        </button>
                    </div>
                </form>

            @endif

        </div>
    </div>
</div>
</x-app-layout>