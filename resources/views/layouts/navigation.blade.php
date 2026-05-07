<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/OPGI.jpg') }}" width="80" height="80" alt="opgi Logo">
        </a>

        <!-- Bouton hamburger (mobile) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu collapse -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation principale -->
            <ul class="navbar-nav me-auto gap-lg-4">
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}" 
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('souscripteur.create') ? 'active' : '' }}" 
                       href="{{ route('souscripteur.create') }}">
                        <i class="bi bi-person-plus-fill"></i> Inscription
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ Route::is('ov.index') || Route::is('ov.create') ? 'active' : '' }}" 
                       href="{{ route('ov.index') }}">
                        <i class="bi bi-cash-coin"></i> Ordre de Versement
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ Route::is('desistement') ? 'active' : '' }}" 
                       href="{{ route('desistement') }}">
                        <i class="bi bi-person-x-fill"></i> Désistement
                    </a>
                </li>
            </ul>

            <!-- Menu utilisateur -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" 
                       href="#" 
                       id="userDropdown" 
                       role="button" 
                       data-bs-toggle="dropdown" 
                       aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center" 
                               href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i>
                                Mon Profil
                            </a>
                        </li>
                        
                        @if(Auth::user()->role === 'admin')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" 
                               href="{{ route('users.index') }}">
                                <i class="bi bi-person-gear me-2"></i> 
                                Gestion Utilisateurs
                            </a>
                        </li>
                        @endif
                        
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="dropdown-item text-danger d-flex align-items-center">
                                    <i class="bi bi-box-arrow-left me-2"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Navigation principale */
.navbar-nav .nav-link {
    color: #000;
    font-weight: 600;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
    border-radius: 8px;
}

.navbar-nav .nav-link:hover {
    background-color: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.navbar-nav .nav-link.active {
    background: linear-gradient(45deg, #1e3c72, #2a5298);
    color: white !important;
}

.navbar-nav .nav-link i {
    margin-right: 0.25rem;
}

/* Mobile adjustments */
@media (max-width: 991.98px) {
    .navbar-collapse {
        margin-top: 1rem;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 8px;
    }
    
    .navbar-nav {
        gap: 0.5rem;
    }
    
    .navbar-nav .nav-item {
        width: 100%;
    }
    
    .navbar-nav .nav-link {
        padding: 0.75rem 1rem;
        border-radius: 6px;
    }
    
    /* Séparateur visuel entre navigation et menu utilisateur */
    .navbar-nav.ms-auto {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
    }
}

/* Desktop spacing */
@media (min-width: 992px) {
    .navbar-nav {
        margin-left: 2rem;
    }
}

/* Dropdown menu */
.dropdown-menu {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: none;
    margin-top: 0.5rem;
}

.dropdown-item {
    padding: 0.75rem 1.25rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.dropdown-item.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545 !important;
}
</style>