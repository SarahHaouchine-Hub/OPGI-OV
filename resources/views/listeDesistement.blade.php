<x-app-layout>

<style>
    .custom-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .card-header-gradient {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
        color: white;
        padding: 1rem;
    }
    .table-modern thead {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }
    .table-modern tbody tr { vertical-align: middle; }
    .table-modern tbody tr:hover {
        background-color: #f1f4f9;
        box-shadow: inset 4px 0 0 #2a5298;
    }
    .price-tag {
        font-weight: 700;
        font-family: 'Monaco', monospace;
        color: #2d3436;
    }
    .btn-outline-secondary {
        border-radius: 8px;
        border-color: #6c757d;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }
</style>

<div class="py-4">
    <div class="container-fluid px-4">

        {{-- ── Alertes ── --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ── Tableau des désistements / remplacements ── --}}
        <div class="card custom-card mb-2">
            <div class="card-header card-header-gradient d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-archive-fill me-2"></i> Liste des désistements &amp; remplacements
                </h4>
                <span class="badge bg-secondary fs-6">{{ $desistements->total() }} résultat(s)</span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr class="text-center">
                                <th>N°</th>
                                <th>Type</th>
                                <th>Code Logement</th>
                                <th>Ancien Souscripteur</th>
                                <th>Nouveau Souscripteur</th>
                                <th>Bât / Étage / Porte</th>
                                <th>Site</th>
                                <th>Date</th>
                                <th>Prix du Logement</th>
                                <th>Montant Payé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($desistements as $desistement)
                                <tr class="text-center">
                                    <td class="text-muted">
                                        {{ $desistements->firstItem() + $loop->index }}
                                    </td>

                                    {{-- Type --}}
                                    <td>
                                        @if ($desistement->type === 'remplacement')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-arrow-repeat me-1"></i> Remplacement
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i> Désistement
                                            </span>
                                        @endif
                                    </td>

                                    <td class="fw-bold font-monospace">
                                        {{ $desistement->code_loge_lpl }}
                                    </td>

                                    {{-- Ancien souscripteur --}}
                                    <td>
                                        {{ strtoupper($desistement->souscripteur->nom    ?? '') }}
                                        {{ strtoupper($desistement->souscripteur->prenom ?? '') }}
                                    </td>

                                    {{-- Nouveau souscripteur (remplacement uniquement) --}}
                                    <td>
                                        @if ($desistement->type === 'remplacement' && $desistement->nouveauSouscripteur)
                                            <span class="text-success fw-bold">
                                                {{ strtoupper($desistement->nouveauSouscripteur->nom) }}
                                                {{ strtoupper($desistement->nouveauSouscripteur->prenom) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        Bât.&nbsp;{{ $desistement->logement->num_batiment ?? '—' }} —
                                        Ét.&nbsp;{{ $desistement->logement->num_etage    ?? '—' }} —
                                        Porte&nbsp;{{ $desistement->logement->num_porte  ?? '—' }}
                                    </td>

                                    {{-- Site depuis la relation BDD --}}
                                    <td>{{ $desistement->logement->site->libelle ?? '—' }}</td>

                                    <td class="text-danger fw-bold">
                                        {{ \Carbon\Carbon::parse($desistement->date_desistement)->format('d/m/Y') }}
                                    </td>

                                    <td class="price-tag">
                                        {{ number_format($desistement->logement->prix ?? 0, 2, ',', ' ') }} DA
                                    </td>

                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ number_format($desistement->souscripteur->ovs->sum('montant_paye'), 2, ',', ' ') }} DA
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                            <h5>Aucun enregistrement trouvé</h5>
                                            <p class="small mb-0">Il n'y a pas encore de désistements ou remplacements enregistrés.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($desistements->hasPages())
            <div class="d-flex justify-content-start mt-2">
                {{ $desistements->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const alertEl = document.getElementById('alert');
    if (alertEl) setTimeout(() => new bootstrap.Alert(alertEl).close(), 3000);
});
</script>

</x-app-layout>