<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class HashidsDecode
{
    public function handle(Request $request, Closure $next)
    {
        // 1. DÉCODAGE DES PARAMÈTRES D'URL (ex: {id}, {etude})
        $params = $request->route()->parameters();
        foreach ($params as $key => $value) {
            $decoded = Hashids::decode($value);
            if (!empty($decoded)) {
                $request->route()->setParameter($key, $decoded[0]);
            }
        }

        // 2. DÉCODAGE GLOBAL DES DONNÉES DE FORMULAIRE (POST/PUT)
        // On parcourt TOUTES les données reçues
        $allInputs = $request->all();
        $modified = false;

        foreach ($allInputs as $key => $value) {
            // On ne tente de décoder que si c'est une chaîne (les Hashids sont des strings)
            if (is_string($value) && !empty($value)) {
                $decoded = Hashids::decode($value);
                
                if (!empty($decoded)) {
                    $allInputs[$key] = $decoded[0];
                    $modified = true;
                }
            }
        }

        // Si on a modifié des valeurs, on les réinjecte dans la requête
        if ($modified) {
            $request->merge($allInputs);
        }

        return $next($request);
    }
}