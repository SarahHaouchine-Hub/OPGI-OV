<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait RoleAccess
{
    // =========================================================================
    //  MAPPING — rôles restreints → programmes autorisés (clés en MAJUSCULE)
    // =========================================================================
    private const ROLE_PROGRAMME_MAP = [
        'charge_etude_lsp_lpa' => ['LSP', 'LPA'],
        'charge_etude_prom'    => ['LPL', 'PROMOTIONNEL'],
    ];

    /**
     * Retourne le tableau des libellés de programmes autorisés pour l'utilisateur
     * connecté, ou NULL si l'utilisateur n'a pas de restriction (accès total).
     *
     * @return string[]|null
     */
    protected function getAllowedProgrammes(): ?array
    {
        $role = Auth::user()?->role;
        return self::ROLE_PROGRAMME_MAP[$role] ?? null;
    }

    /**
     * Retourne true si l'utilisateur connecté peut accéder au programme donné.
     * $programmeLibelle est comparé en MAJUSCULE (ex : 'LPA', 'LSP', 'LPL').
     */
    protected function canAccessProgramme(string $programmeLibelle): bool
    {
        $allowed = $this->getAllowedProgrammes();
        if ($allowed === null) {
            return true; // accès total
        }
        $upper = strtoupper(trim($programmeLibelle));
        foreach ($allowed as $key) {
            if (str_contains($upper, $key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Applique un filtre Eloquent sur une relation `programme`
     * selon les droits de l'utilisateur connecté.
     *
     * Usage : $query->when(..., fn($q) => ...)
     * Retourne la query chainée.
     */
    protected function applyProgrammeFilter($query)
    {
        $allowed = $this->getAllowedProgrammes();
        if ($allowed === null) {
            return $query; // pas de filtre
        }

     // AFTER (fixed):
return $query->whereHas('logement.programme', function ($q) use ($allowed) {
    $q->where(function ($inner) use ($allowed) {
        foreach ($allowed as $key) {
            $inner->orWhereRaw('UPPER(libelle) LIKE ?', ['%' . $key . '%']);
        }
    });
});
    }

    /**
     * Applique un filtre DB::table() sur la colonne `libelle` de la table `programmes`
     * via une jointure.  Utilise un alias $progAlias pour la table programmes.
     */
    protected function applyProgrammeFilterRaw($query, string $progAlias = 'programmes'): void
    {
        $allowed = $this->getAllowedProgrammes();
        if ($allowed === null) {
            return;
        }

        $query->where(function ($q) use ($allowed, $progAlias) {
            foreach ($allowed as $key) {
                $q->orWhereRaw("UPPER({$progAlias}.libelle) LIKE ?", ['%' . $key . '%']);
            }
        });
    }

    /**
     * Message d'erreur standard pour un accès refusé.
     */
    protected function accessDeniedMessage(): string
    {
        return 'Accès refusé. Votre profil ne vous autorise pas à gérer ce type de programme.';
    }
}