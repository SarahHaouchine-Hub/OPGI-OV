<style>
    .card-header-gradient {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
    }
    .custom-card {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .form-label-custom {
        font-size: 0.85rem;
        font-weight: 600;
        color: #555;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        display: block;
    }
    .input-group-text {
        background-color: #f8f9fa;
        color: #1e3c72;
        border-right: none;
    }
    .input-group .form-control {
        border-left: none;
    }
</style>
<x-app-layout>
<div class="container py-4">
    <div class="card custom-card" style="border:none; border-radius:15px">
        <div class="card-header card-header-gradient text-white" style="padding:1.5rem;">
            <h4 class="mb-0" style="font-size:18px">
                <i class="bi bi-credit-card-fill me-2"></i>
                Nouveau Paiement - Enregistrement du reçu
            </h4>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('paiement.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="ov_id" value="{{$ov->id}}">

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header text-white" style="background-color: rgb(60 88 130) !important;">
                        <h6 class="mb-0"><i class="bi bi-receipt me-2"></i> Informations du Versement</h6>
                    </div>
                    <div class="card-body border rounded-bottom">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label-custom">N° Reçu</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                    <input type="text" name="num_recu" class="form-control" required placeholder="Numéro du reçu">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">Date de Paiement</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" name="date_paiement" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
    <div class="col-md-8">
        <label class="form-label-custom">Nom de l'agence</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-bank"></i></span>
            <input type="text"
                   class="form-control bg-light text-muted fst-italic"
                   value="{{ $nomAgence ?: '—' }}"
                   disabled>
        </div>
        <small class="text-muted mt-1 d-block">
            <i class="bi bi-lock-fill me-1"></i>Rempli automatiquement depuis le site
        </small>
    </div>
    <div class="col-md-4">
        <label class="form-label-custom">N° de l'agence</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
            <input type="text"
                   class="form-control bg-light text-muted fst-italic"
                   value="{{ $numAgence ?: '—' }}"
                   disabled>
        </div>
    </div>
</div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header text-dark" style="background-color: #cfe2ff;">
                        <h6 class="mb-0 text-primary fw-bold"><i class="bi bi-paperclip me-2"></i> Justificatifs Numérisés</h6>
                    </div>
                    <div class="card-body border border-primary-subtle rounded-bottom" style="background-color: #fcfdff;">
                        <div class="mb-3">
                            <label class="form-label-custom" for="pj">Ajouter des pièces jointes (PDF)</label>
                            <input type="file" 
                                   class="form-control @error('pj.*') is-invalid @enderror" 
                                   name="pj[]" 
                                   id="pj" 
                                   multiple
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i> Vous pouvez sélectionner plusieurs fichiers
                                </small>
                            </div>
                            @error('pj.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div id="filePreview" class="mt-2"></div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm" style="border-radius:8px">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i> Enregistrer le Paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('pj').addEventListener('change', function(e) {
        const preview = document.getElementById('filePreview');
        preview.innerHTML = '';
        
        if (this.files.length > 0) {
            let html = '<div class="alert alert-info border-0 shadow-sm mb-0"><strong>Fichiers à télécharger :</strong><ul class="mb-0 mt-2 small">';
            Array.from(this.files).forEach(file => {
                const size = (file.size / 1024 / 1024).toFixed(2);
                html += `<li><i class="bi bi-file-earmark-check me-1"></i> ${file.name} (${size} MB)</li>`;
            });
            html += '</ul></div>';
            preview.innerHTML = html;
        }


                    const alert = document.getElementById('alert');
            if (alert) {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 3000);
            }

    });
</script>
</x-app-layout>