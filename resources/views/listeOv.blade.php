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

    <div class="card custom-card">
        <div class="card-header card-header-gradient d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i> Gestion des Ordres de versement</h5>
            <span class="badge bg-light text-dark">{{ count($souscripteurs) }} Total</span>
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
    $programme = strtoupper(trim(
        $s->logement->programme->libelle  // ← libelle pas code
        ?? 'LPL'
    ));
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

                            {{-- OVs --}}
                            <td class="p-3">
                                @forelse($s->ovs as $ov)
                                <div class="ov-item mb-3 p-3 border rounded-3 shadow-sm">

                                    {{-- En-tête OV : pourcentage + tranche (LPA) + statut --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary">{{ $ov->pourcentage }}%</span>

                                            {{-- Tranche LPA --}}
                                            @if($programme === 'LPA' && $ov->numero_tranche)
                                                <span class="badge bg-warning text-dark">
                                                    T{{ $ov->numero_tranche }}/5
                                                </span>
                                            @endif

                                            {{-- VSP --}}
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
                                        @if($ov->paiement)
                                            <a href="{{ asset('storage/' . $ov->paiement->recu_pdf) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-file-pdf me-1"></i> Reçu
                                            </a>
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

                                {{-- Progression LPA --}}
                                @if($programme === 'LPA')
                                <div class="mt-2">
                                    @php $nbOvs = $s->ovs->count(); @endphp
                                    <div class="progress" style="height:6px; border-radius:3px;">
                                        <div class="progress-bar bg-success"
                                             style="width: {{ ($nbOvs / 5) * 100 }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $nbOvs }}/5 tranches générées</small>
                                </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-center">
                                @php
                                    $peutGenerer = true;
                                    if ($programme === 'LPA' && $s->ovs->count() >= 5) $peutGenerer = false;
                                    if ($programme === 'LSP' && $s->ovs->count() >= 1) $peutGenerer = false;
                                @endphp

                                @if($peutGenerer)
                                    <a href="{{ route('ov.create', Hashids::encode($s->id)) }}"
                                       class="btn btn-primary btn-action btn-sm shadow-sm">
                                        <i class="fas fa-plus-circle"></i>
                                        @if($programme === 'LPA')
                                            Tranche {{ $s->ovs->count() + 1 }}
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
                            <td colspan="6" class="py-5 text-muted">
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