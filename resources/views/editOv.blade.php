<x-app-layout>
<div class="container py-5">
    <div class="card" style="border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.05);">

        <div class="card-header text-white" style="background: linear-gradient(45deg, #1e3c72, #2a5298); border-radius:15px 15px 0 0;">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Modifier l'Ordre de Versement</h5>
        </div>

        <div class="card-body p-4">

            {{-- Infos souscripteur --}}
            <div class="alert alert-info mb-4">
                <strong>Souscripteur :</strong>
                {{ strtoupper($ov->souscripteur->nom) }} {{ $ov->souscripteur->prenom }}
                &nbsp;|&nbsp;
                <strong>Code :</strong> {{ $ov->souscripteur->code_loge_lpl }}
                &nbsp;|&nbsp;
                <strong>Tranche :</strong> T{{ $ov->numero_tranche }}
                &nbsp;|&nbsp;
                <strong>Programme :</strong>
                {{ strtoupper($ov->souscripteur->logement->programme->libelle ?? '—') }}
            </div>

            <form method="POST" action="{{ route('ov.update', Hashids::encode($ov->id)) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Montant à payer (DA)</label>
                        <input type="number" step="0.01" name="montant_paye"
                               class="form-control @error('montant_paye') is-invalid @enderror"
                               value="{{ old('montant_paye', $ov->montant_paye) }}" required>
                        @error('montant_paye')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Montant restant (DA)</label>
                        <input type="number" step="0.01" name="montant_restant"
                               class="form-control @error('montant_restant') is-invalid @enderror"
                               value="{{ old('montant_restant', $ov->montant_restant) }}" required>
                        @error('montant_restant')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pourcentage (%)</label>
                        <input type="number" step="0.01" name="pourcentage"
                               class="form-control @error('pourcentage') is-invalid @enderror"
                               value="{{ old('pourcentage', $ov->pourcentage) }}" required>
                        @error('pourcentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="bi bi-save me-1"></i> Enregistrer les modifications
                    </button>
                    <a href="{{ route('ov.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Annuler
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
</x-app-layout>