<style>
    .card-header-gradient {
        background: linear-gradient(45deg, #1e3c72, #2a5298);
    }

    .btn.btn-primary:hover{
        background-color: #1e3c72
}
</style>
<head>
            <title>Profile</title>

</head>
<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header card-header-gradient text-white bg-light py-3">
                        <h5 class="mb-0">Informations du profil</h5>
                        <p class="text-white small mb-0">Mettez à jour les informations de votre profil et votre adresse e-mail.</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse e-mail</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-3 p-3 border rounded bg-light">
                                        <p class="text-sm mb-0">
                                            Votre adresse e-mail n'est pas vérifiée.
                                            <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">Cliquez ici pour renvoyer l'e-mail de vérification.</button>
                                        </p>
                                        @if (session('status') === 'verification-link-sent')
                                            <div class="text-success small mt-2 fw-bold">Un nouveau lien de vérification a été envoyé à votre adresse e-mail.</div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                                @if (session('status') === 'profile-updated')
                                    <span class="text-success small success-msg animate__animated animate__fadeIn">✓ Enregistré.</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header card-header-gradient text-white bg-light py-3">
                        <h5 class="mb-0">Modifier le mot de passe</h5>
                        <p class="text-white small mb-0">Assurez-vous que votre compte utilise un mot de passe long et complexe pour rester en sécurité.</p>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" id="current_password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                                @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" id="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                                @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                                @error('password_confirmation', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                                @if (session('status') === 'password-updated')
                                    <span class="text-success small success-msg animate__animated animate__fadeIn">✓ Mot de passe mis à jour.</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const messages = document.querySelectorAll('.success-msg');
                messages.forEach(msg => {
                    msg.style.transition = "opacity 0.5s ease";
                    msg.style.opacity = "0";
                    setTimeout(() => msg.remove(), 500);
                });
            }, 3000);
        });
    </script>
</x-app-layout>