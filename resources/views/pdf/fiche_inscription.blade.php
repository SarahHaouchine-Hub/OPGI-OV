<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 2.5cm; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.8;
            color: #222;
            font-size: 13px;
        }

        /* ── En-tête ─────────────────────────────────── */
        .header { width: 100%; margin-bottom: 40px; }
        .header table { width: 100%; border: none; }
        .official-text {
            direction: rtl;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            line-height: 1.8;
        }

        /* ── Titre principal ─────────────────────────── */
        .main-title {
            text-align: center;
            margin: 30px auto;
            font-size: 22px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            color: #1a237e;
        }

        /* ── Sections ────────────────────────────────── */
        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #6c7d8f;
            margin-top: 28px;
            margin-bottom: 12px;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }

        /* ── Tableau infos ───────────────────────────── */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { width: 50%; padding: 8px 0; vertical-align: top; border: none; }

        .label {
            font-weight: bold;
            color: #000;
            display: inline-block;
            width: 35%;
            vertical-align: top;
        }
        .value {
            display: inline-block;
            width: 60%;
            vertical-align: top;
            word-wrap: break-word;
        }

        /* ── Message félicitations ───────────────────── */
        .congrats-message {
            text-align: center;
            margin: 35px 0;
            padding: 18px;
            font-style: italic;
            font-size: 15px;
            color: #2e7d32;
            border-top: 1px solid #f1f8e9;
            border-bottom: 1px solid #f1f8e9;
        }

        /* ── Pied de page ────────────────────────────── */
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: #999;
            padding-bottom: 20px;
        }

        .code-lgt {
            font-weight: bold;
            font-size: 15px;
            color: #1a237e;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    {{-- ── EN-TÊTE INSTITUTIONNEL ───────────────────────────────────── --}}
    <div class="header">
        <table>
            <tr>
                <td style="width:20%; text-align:left;">
                    <img src="data:image/png;base64,{{ $algeria }}" style="width:80%; height:auto;">
                </td>
                <td style="width:60%;" class="official-text">
                    {{ $republique }}<br>
                    {{ $ministere }}<br>
                    {{ $agence }}
                </td>
                <td style="width:20%; text-align:right;">
                    <img src="data:image/png;base64,{{ $logoOPGI }}" style="width:80%; height:auto;">
                </td>
            </tr>
        </table>
    </div>

    <div class="main-title">Fiche d'Inscription</div>

    {{-- ── INFORMATIONS DU SOUSCRIPTEUR ───────────────────────────────── --}}
    <div class="section-title">Informations du Souscripteur</div>
    <table class="info-table">
        <tr>
            <td>
                <span class="label">Nom :</span>
                <span class="value">{{ strtoupper($souscripteur->nom) }}</span>
            </td>
            <td>
                <span class="label">Prénom :</span>
                <span class="value">{{ ucfirst($souscripteur->prenom) }}</span>
            </td>
        </tr>
        <tr>
            {{-- Nom arabe --}}
            <td style="direction:rtl; text-align:right;">
                <span class="label" style="width:35%;">اللقب :</span>
                <span class="value" style="width:60%;">{{ $souscripteur->nom_arabe }}</span>
            </td>
            {{-- Prénom arabe --}}
            <td style="direction:rtl; text-align:right;">
                <span class="label" style="width:35%;">الاسم :</span>
                <span class="value" style="width:60%;">{{ $souscripteur->prenom_arabe }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Né(e) le :</span>
                <span class="value">{{ \Carbon\Carbon::parse($souscripteur->date_naissance)->format('d/m/Y') }}</span>
            </td>
            <td>
                <span class="label">Code Lgt :</span>
                <span class="value code-lgt">{{ $souscripteur->code_loge_lpl }}</span>
            </td>
        </tr>
    </table>

    <div class="congrats-message">
        « Félicitations d'avoir inscrit avec succès au logement promotionnel. »
    </div>

    {{-- ── AFFECTATION DU LOGEMENT ─────────────────────────────────────── --}}
    <div class="section-title">Affectation du Logement</div>
    <table class="info-table">
        {{-- Programme --}}
        <tr>
            <td colspan="2">
                <span class="label" style="width:17.5%;">Programme :</span>
                <span class="value" style="width:80%; font-weight:bold;">
                    {{ $logement->site->programme->libelle ?? 'N/A' }}
                </span>
            </td>
        </tr>
        {{-- Site --}}
        <tr>
            <td colspan="2">
                <span class="label" style="width:17.5%;">Site :</span>
                <span class="value" style="width:80%;">
                    {{ $logement->site->libelle ?? 'N/A' }}
                    @if($logement->site && $logement->site->wilaya)
                        — {{ $logement->site->wilaya->nom }}
                    @endif
                </span>
            </td>
        </tr>
        {{-- Bâtiment / Étage --}}
        <tr>
            <td>
                <span class="label">Bâtiment :</span>
                <span class="value">{{ $logement->num_batiment }}</span>
            </td>
            <td>
                <span class="label">Étage :</span>
                <span class="value">{{ $logement->num_etage }}</span>
            </td>
        </tr>
        {{-- Porte / Lot --}}
        <tr>
            <td>
                <span class="label">Porte N° :</span>
                <span class="value">{{ $logement->num_porte }}</span>
            </td>
            <td>
                @if($logement->num_lot)
                    <span class="label">N° Lot :</span>
                    <span class="value">{{ $logement->num_lot }}</span>
                @endif
            </td>
        </tr>
        {{-- Surface / Typologie --}}
        <tr>
            <td>
                @if($logement->surface)
                    <span class="label">Surface :</span>
                    <span class="value">{{ $logement->surface }} m²</span>
                @endif
            </td>
            <td>
                @if($logement->typologie)
                    <span class="label">Typologie :</span>
                    <span class="value">{{ $logement->typologie }}</span>
                @endif
            </td>
        </tr>
        {{-- Prix --}}
        @if($logement->prix)
        <tr>
            <td colspan="2">
                <span class="label" style="width:17.5%;">Prix :</span>
                <span class="value" style="width:80%; font-weight:bold;">
                    {{ number_format($logement->prix, 0, ',', ' ') }} DA
                </span>
            </td>
        </tr>
        @endif
    </table>

    {{-- ── QR CODE + PIED DE PAGE ──────────────────────────────────────── --}}
    <div class="footer">
        <div style="text-align:center; margin-bottom: 10px;">
            @if($souscripteur->qrcode)
                {{--
                    Le QR est stocké en base64 d'un SVG.
                    On le réinsère directement comme image SVG base64.
                --}}
                <img src="data:image/svg+xml;base64,{{ $souscripteur->qrcode }}"
                     style="width:100px; height:100px;">
                <br>
                <span style="font-size:10px; color:#aaa;">Signature numérique de vérification</span>
            @else
                <p style="color:red;">QR Code non disponible</p>
            @endif
        </div>

        Document officiel généré le {{ now()->format('d/m/Y à H:i') }} — Services OPGI
    </div>

</body>
</html>