<x-app-layout>

<style>
    .card-header-gradient {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
    }
    .custom-card {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .form-label-custom {
        font-size: 0.9rem;
        font-weight: 600;
        color: #555;
        margin-bottom: 0.5rem;
    }
    .table thead {
        background-color: #f1f4f9;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    /* Boutons simplifiés */
    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .btn-action:hover {
        transform: translateY(-1px);
    }
    /* Modal simplifié */
    .modal-content {
        border-radius: 12px;
    }
    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .modal-body {
        padding: 1.5rem;
    }
    .form-control, .form-select {
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        border: 1px solid #ddd;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.15);
    }
</style>

    <div class="container py-4">
        {{-- Alertes --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" id="alert" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card custom-card">
            {{-- Header --}}
            <div class="card-header card-header-gradient text-white d-flex justify-content-between align-items-center" style="padding: 1.2rem;">
                <h4 class="mb-0" style="font-size:18px"><i class="bi bi-people-fill me-2"></i> Gestion des Utilisateurs</h4>
                <button data-bs-toggle="modal" data-bs-target="#addUserModal" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nouveau
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="text-secondary">
                                <th class="ps-4">Utilisateur</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td class="ps-4 align-middle fw-bold text-dark">{{ $user->name }}</td>
                                    <td class="align-middle text-muted">{{ $user->email }}</td>
                                    <td class="align-middle">
                                        <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }} rounded-pill">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="pe-4 align-middle text-end">
                                        <button type="button" class="btn btn-sm btn-primary btn-action me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-role="{{ $user->role }}">
                                            <i class="bi bi-pencil"></i> Modifier
                                        </button>

                                        <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-success' }} btn-action">
                                                <i class="bi {{ $user->is_active ? 'bi-lock'  : 'bi-unlock' }}"></i>
                                                {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-5 text-center text-muted">
                                        <i class="bi bi-person-x display-4 d-block mb-2 opacity-25"></i>
                                        <p>Aucun utilisateur enregistré.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL AJOUTER --}}
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header card-header-gradient text-white">
                    <h5 class="modal-title fw-semibold">Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nom complet</label>
                            <input type="text" name="name" class="form-control"  required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Rôle</label>
                            <select name="role" class="form-select" required>
                                <option value="agent">Agent</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary card-header-gradient">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header card-header-gradient text-white">
                    <h5 class="modal-title fw-semibold">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nom complet</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Rôle</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <option value="admin">Administrateur</option>
                                <option value="agent">Agent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Nouveau mot de passe <small class="text-muted">(optionnel)</small></label>
                            <input type="password" name="password" class="form-control" placeholder="Laissez vide pour ne pas modifier">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary card-header-gradient">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editUserModal = document.getElementById('editUserModal');
            editUserModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                document.getElementById('edit_name').value = button.getAttribute('data-name');
                document.getElementById('edit_email').value = button.getAttribute('data-email');
                document.getElementById('edit_role').value = button.getAttribute('data-role');
                const id = button.getAttribute('data-id');
                document.getElementById('editForm').action = '/users/' + id;
            });

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