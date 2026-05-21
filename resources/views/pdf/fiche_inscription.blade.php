<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Marges réduites pour garantir le format une page */
        @page { margin: 1.2cm 1.5cm; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.4;
            color: #2c3e50;
            font-size: 12px;
        }

        /* ── En-tête ─────────────────────────────────── */
        .header { width: 100%; margin-bottom: 15px; }
        .header table { width: 100%; border-collapse: collapse; }
        .official-text {
            direction: rtl;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            line-height: 1.6;
            color: #1a237e;
        }

        /* ── Titre principal ─────────────────────────── */
        .main-title {
            text-align: center;
            margin: 15px auto;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a237e;
            border-bottom: 2px solid #1a237e;
            padding-bottom: 5px;
            width: 40%;
        }

        /* ── Sections ────────────────────────────────── */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a237e;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
            background-color: #f8fafc;
            padding: 4px 8px;
            border-left: 3px solid #1a237e;
        }

        /* ── Structure Tableau Propre (Fixe DomPDF) ─── */
        .info-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px; 
            table-layout: fixed;
        }
        .info-table td { 
            padding: 5px 4px; 
            vertical-align: middle;
        }
        
        /* Alignement strict des colonnes */
        .label {
            font-weight: bold;
            color: #475569;
            width: 18%;
        }
        .value {
            color: #0f172a;
            width: 32%;
            word-wrap: break-word;
        }
        .full-width-value {
            color: #0f172a;
            word-wrap: break-word;
        }

        /* ── Message de félicitations épuré ────────── */
        .congrats-message {
            text-align: center;
            margin: 15px 0;
            padding: 8px;
            font-style: italic;
            font-size: 13px;
            color: #15803d;
            background-color: #f0fdf4;
            border: 1px dashed #bbf7d0;
            border-radius: 4px;
        }

        /* ── Pied de page compact ────────────────────── */
        .footer {
            position: absolute;
            bottom: -10px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        .code-lgt {
            font-weight: bold;
            font-size: 14px;
            color: #b91c1c;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    {{-- ── EN-TÊTE INSTITUTIONNEL ───────────────────────────────────── --}}
    <div class="header">
        <table>
            <tr>
                <td style="width:20%; text-align:left; vertical-align: middle;">
                    <img src="data:image/png;base64,{{ $algeria }}" style="width:75px; height:auto;">
                </td>
                <td style="width:60%;" class="official-text">
                    {{ $republique }}<br>
                    {{ $ministere }}<br>
                      {{ $comm }}
                    {{ $agence }}
                  
                </td>
                <td style="width:20%; text-align:right; vertical-align: middle;">
                    <img src="data:image/png;base64,{{ $logoOPGI }}" style="width:75px; height:auto;">
                </td>
            </tr>
        </table>
    </div>

    <div class="main-title">Fiche d'Inscription</div>

    {{-- ── INFORMATIONS DU SOUSCRIPTEUR ───────────────────────────────── --}}
    <div class="section-title">Informations du Souscripteur</div>
    <table class="info-table">
        <tr>
            <td class="label">Nom :</td>
            <td class="value">{{ strtoupper($souscripteur->nom) }}</td>
            <td class="label">Prénom :</td>
            <td class="value">{{ ucfirst($souscripteur->prenom) }}</td>
        </tr>
        <tr>
            <td class="label">Né(e) le :</td>
            <td class="value">{{ \Carbon\Carbon::parse($souscripteur->date_naissance)->format('d/m/Y') }}</td>
            <td class="label">Lieu de naiss. :</td>
            <td class="value">{{ $souscripteur->lieu_naissance ?? '/' }}</td>
        </tr>
        <tr>
            <td class="label">NIN :</td>
            <td class="value">{{ $souscripteur->nin }}</td>
            <td class="label">Situation fam. :</td>
            <td class="value">{{ ucfirst($souscripteur->situation_familiale) }}</td>
        </tr>
        @if($souscripteur->nom_pere || $souscripteur->prenom_pere || $souscripteur->nom_mere || $souscripteur->prenom_mere)
        <tr>
            <td class="label">Nom du père :</td>
            <td class="value">
                @if($souscripteur->nom_pere || $souscripteur->prenom_pere)
                    {{ strtoupper($souscripteur->nom_pere) }} {{ ucfirst($souscripteur->prenom_pere) }}
                @else
                    /
                @endif
            </td>
            <td class="label">Nom de la mère :</td>
            <td class="value">
                @if($souscripteur->nom_mere || $souscripteur->prenom_mere)
                    {{ strtoupper($souscripteur->nom_mere) }} {{ ucfirst($souscripteur->prenom_mere) }}
                @else
                    /
                @endif
            </td>
        </tr>
        @endif
        <tr>
            <td class="label">Code Lgt :</td>
            <td colspan="3" class="full-width-value code-lgt">{{ $souscripteur->code_loge_lpl }}</td>
        </tr>
    </table>

    {{-- ── INFORMATIONS DU CONJOINT (si marié) ──────────────────────────── --}}
    @if($souscripteur->situation_familiale === 'marie' && $souscripteur->conjoint_nom)
    <div class="section-title">Informations du Conjoint</div>
    <table class="info-table">
        <tr>
            <td class="label">Nom :</td>
            <td class="value">{{ strtoupper($souscripteur->conjoint_nom) }}</td>
            <td class="label">Prénom :</td>
            <td class="value">{{ ucfirst($souscripteur->conjoint_prenom) }}</td>
        </tr>
        <tr>
            <td class="label">NIN :</td>
            <td class="value">{{ $souscripteur->conjoint_nin }}</td>
            <td class="label">Né(e) le :</td>
            <td class="value">
                {{ $souscripteur->conjoint_date_naissance ? \Carbon\Carbon::parse($souscripteur->conjoint_date_naissance)->format('d/m/Y') : '/' }}
            </td>
        </tr>
        @if($souscripteur->conjoint_lieu_naissance)
        <tr>
            <td class="label">Lieu de naiss. :</td>
            <td colspan="3" class="full-width-value">{{ $souscripteur->conjoint_lieu_naissance }}</td>
        </tr>
        @endif
        @if($souscripteur->conjoint_nom_pere || $souscripteur->conjoint_prenom_pere || $souscripteur->conjoint_nom_mere || $souscripteur->conjoint_prenom_mere)
        <tr>
            <td class="label">Nom du père :</td>
            <td class="value">
                {{ strtoupper($souscripteur->conjoint_nom_pere) }} {{ ucfirst($souscripteur->conjoint_prenom_pere) }}
            </td>
            <td class="label">Nom de la mère :</td>
            <td class="value">
                {{ strtoupper($souscripteur->conjoint_nom_mere) }} {{ ucfirst($souscripteur->conjoint_prenom_mere) }}
            </td>
        </tr>
        @endif
    </table>
    @endif

    <div class="congrats-message">
        « Félicitations pour votre inscription réussie au Programme {{ $logement->site->programme->libelle ?? 'N/A' }} »
    </div>

    {{-- ── AFFECTATION DU LOGEMENT ─────────────────────────────────────── --}}
    <div class="section-title">Affectation du Logement</div>
    <table class="info-table">
        <tr>
            <td class="label">Programme :</td>
            <td colspan="3" class="full-width-value" style="font-weight:bold;">
                {{ $logement->site->programme->libelle ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td class="label">Site :</td>
            <td colspan="3" class="full-width-value">
                {{ $logement->site->libelle ?? 'N/A' }}
                @if($logement->site && $logement->site->wilaya)
                    — {{ $logement->site->wilaya->nom }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Bâtiment :</td>
            <td class="value">{{ $logement->num_batiment }}</td>
            <td class="label">Étage :</td>
            <td class="value">{{ $logement->num_etage }}</td>
        </tr>
        <tr>
            <td class="label">Porte N° :</td>
            <td class="value">{{ $logement->num_porte }}</td>
            <td class="label">N° Lot :</td>
            <td class="value">{{ $logement->num_lot ?? '/' }}</td>
        </tr>
        <tr>
            <td class="label">Surface :</td>
            <td class="value">{{ $logement->surface ? $logement->surface.' m²' : '/' }}</td>
            <td class="label">Typologie :</td>
            <td class="value">{{ $logement->typologie ?? '/' }}</td>
        </tr>
        @if($logement->prix)
        <tr>
            <td class="label">Prix :</td>
            <td colspan="3" class="full-width-value" style="font-weight:bold; color: #1a237e; font-size: 13px;">
                {{ number_format($logement->prix, 0, ',', ' ') }} DA
            </td>
        </tr>
        @endif
    </table>

    {{-- ── QR CODE + PIED DE PAGE ──────────────────────────────────────── --}}
    <div class="footer">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 75%; text-align: left; vertical-align: bottom; padding-bottom: 5px;">
                    Document officiel généré le {{ now()->format('d/m/Y à H:i') }} — Services OPGI
                </td>
                <td style="width: 25%; text-align: right; vertical-align: middle;">
                    @if($souscripteur->qrcode)
                        <img src="data:image/svg+xml;base64,{{ $souscripteur->qrcode }}" style="width:75px; height:75px;"><br>
                        <span style="font-size:8px; color:#94a3b8; display:block; text-align:right; margin-top:2px;">Signature numérique</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>
</html>