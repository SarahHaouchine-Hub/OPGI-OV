<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
/* ═══════════════════════════════════════════════════════════
   ORDRE DE VERSEMENT — OPGI Dar El Beida
   Format : A4 Portrait  (210mm × 297mm)
   Reproduit fidèlement le document officiel de référence
   ═══════════════════════════════════════════════════════════ */

@page {
    size: 210mm 297mm;
    margin: 0;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9pt;
    color: #000;
    background: #fff;
    width: 210mm;
    height: 297mm;
}

.arabic {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    direction: rtl;
    unicode-bidi: bidi-override;
}

/* ─── PAGE WRAPPER ─────────────────────────────────────────── */
.page {
    width: 210mm;
    min-height: 297mm;
    padding: 8mm 12mm 8mm 12mm;
    position: relative;
}

/* ══════════════════════════════════════════════════════════════
   EN-TÊTE
   ══════════════════════════════════════════════════════════════ */
.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 3mm;
}

.header-table td {
    vertical-align: top;
    padding: 0;
}

.col-fr {
    width: 38%;
    text-align: left;
}

.col-logo {
    width: 24%;
    text-align: center;
    vertical-align: middle;
}

.col-ar {
    width: 38%;
    text-align: right;
    direction: rtl;
}

.logo-img {
    width: 28mm;
    height: 28mm;
    object-fit: contain;
}

/* Textes en-tête FR */
.hfr-bold {
    font-size: 7.5pt;
    font-weight: bold;
    line-height: 1.45;
    display: block;
}

.hfr-normal {
    font-size: 7.5pt;
    font-weight: normal;
    line-height: 1.45;
    display: block;
}

/* Textes en-tête AR */
.har-bold {
    font-size: 7.5pt;
    font-weight: bold;
    line-height: 1.45;
    display: block;
    text-align: right;
    direction: rtl;
}

.har-normal {
    font-size: 7.5pt;
    font-weight: normal;
    line-height: 1.45;
    display: block;
    text-align: right;
    direction: rtl;
}

/* ─── Ligne séparatrice principale ────────────────────────── */
.sep-main {
    border: none;
    border-top: 1.5pt solid #000;
    margin: 2mm 0 2mm 0;
}

/* ─── Date ────────────────────────────────────────────────── */
.date-line {
    text-align: right;
    font-size: 8.5pt;
    margin-bottom: 3mm;
}

/* ══════════════════════════════════════════════════════════════
   TITRE CENTRAL
   ══════════════════════════════════════════════════════════════ */
.title-block {
    text-align: center;
    margin-bottom: 4mm;
}

.title-main {
    font-size: 16pt;
    font-weight: bold;
    letter-spacing: 1pt;
    display: block;
}

.title-tranche {
    font-size: 10pt;
    font-weight: normal;
    display: block;
    margin-top: 1mm;
}

.title-nom {
    font-size: 13pt;
    font-weight: bold;
    display: block;
    margin-top: 2mm;
}

/* ══════════════════════════════════════════════════════════════
   TEXTE D'INVITATION
   ══════════════════════════════════════════════════════════════ */
.intro-text {
    font-size: 9pt;
    line-height: 1.55;
    margin-bottom: 5mm;
    text-align: justify;
}

/* ══════════════════════════════════════════════════════════════
   SECTIONS (Identification & Versement)
   ══════════════════════════════════════════════════════════════ */
.section-title {
    font-size: 10.5pt;
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 3mm;
    margin-top: 4mm;
    display: block;
}

/* ─── Lignes de champs ─────────────────────────────────────── */
.field-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5mm;
}

.field-table td {
    padding: 1.2mm 0;
    vertical-align: middle;
    font-size: 9pt;
}

.field-label {
    font-weight: bold;
    white-space: nowrap;
    padding-right: 3mm;
    width: 1%;
}

.field-colon {
    width: 2mm;
    padding-right: 2mm;
}

.field-value {
    border-bottom: 0.5pt dotted #555;
    padding-bottom: 0.5mm;
}

/* ─── Montants finaux ──────────────────────────────────────── */
.montants-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 3mm;
}

.montants-table td {
    padding: 1.5mm 0;
    font-size: 9pt;
    vertical-align: middle;
}

.montants-table .lbl {
    font-weight: bold;
    white-space: nowrap;
    padding-right: 3mm;
    width: 1%;
}

.montants-table .val {
    font-weight: normal;
}

/* ═══════════════════════════════════════════════════════════
   SECTION NB + QR CODE (layout côte à côte)
   ═══════════════════════════════════════════════════════════ */
.nb-qr-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5mm;
}

.nb-qr-table td {
    vertical-align: top;
    padding: 0;
}

.nb-col {
    width: 70%;
    padding-right: 5mm;
}

.qr-col {
    width: 30%;
    text-align: center;
}

.nb-title {
    font-size: 10pt;
    font-weight: bold;
    margin-bottom: 2mm;
    display: block;
}

.nb-line {
    font-size: 8.5pt;
    line-height: 1.6;
    display: block;
}

.qr-img {
    width: 35mm;
    height: 35mm;
}

/* ═══════════════════════════════════════════════════════════
   PIED DE PAGE
   ═══════════════════════════════════════════════════════════ */
.footer-sep {
    border: none;
    border-top: 0.8pt solid #000;
    margin-top: 6mm;
    margin-bottom: 2mm;
}

.printed-by {
    font-size: 7.5pt;
    font-style: italic;
    margin-bottom: 2mm;
}

.footer-addr {
    font-size: 7.5pt;
    text-align: center;
    line-height: 1.6;
}

</style>
</head>
<body>
<div class="page">

    {{-- ══════════════════════════════════════════════════════
         EN-TÊTE TRICOLONNE : FR | LOGO | AR
         ══════════════════════════════════════════════════════ --}}
    <table class="header-table">
        <tr>
            {{-- Colonne Français --}}
            <td class="col-fr">
                <span class="hfr-normal">MINISTERE DE <span style="font-weight:bold">L'HABITAT DE L'URBANISME</span></span>
                <span class="hfr-normal">ET DE LA VILLE</span>
                <span class="hfr-normal">WILAYA D'ALGER</span>
                <span class="hfr-normal">&nbsp;</span>
                <span class="hfr-bold">OFFICE DE PROMOTION ET DE GESTION</span>
                <span class="hfr-bold">IMMOBILIÈRE DE DAR EL BEIDA</span>
            </td>

            {{-- Logo central --}}
            <td class="col-logo">
                @if(file_exists(public_path('images/OPGI.jpg')))
                    <img class="logo-img"
                         src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('images/OPGI.jpg'))) }}">
                @elseif(file_exists(public_path('images/AADL_logo.svg')))
                    <img class="logo-img"
                         src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('images/AADL_logo.svg'))) }}">
                @endif
            </td>

            {{-- Colonne Arabe --}}
            <td class="col-ar">
                <span class="har-normal">{{ $ministere_ar }}</span>
                <span class="har-normal">{{ $wilaya_ar }}</span>
                <span class="har-normal">&nbsp;</span>
                <span class="har-bold">{{ $opgi_nom_ar }}</span>
                <span class="har-bold">{{ $dar_beida_ar }}</span>
            </td>
        </tr>
    </table>

    {{-- République (ligne centrale) --}}
    <div style="text-align:center; margin-bottom:1mm;">
        <span style="font-size:8pt; font-weight:bold;">
            REPUBLIQUE ALGERIENNE DEMOCRATIQUE ET POPULAIRE
        </span>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <span class="arabic" style="font-size:8pt; font-weight:bold;">
            {{ $republique }}
        </span>
    </div>

    <hr class="sep-main">

    {{-- Date --}}
    <div class="date-line">
        ALGER LE : {{ $datePdf }}
    </div>

    {{-- ══════════════════════════════════════════════════════
         TITRE
         ══════════════════════════════════════════════════════ --}}
    <div class="title-block">
        <span class="title-main">ORDRE DE VERSEMENT</span>
        <span class="title-tranche">({{ $trancheLabel }})</span>
        <span class="title-nom">
            Monsieur/ Madame : {{ strtoupper($ov->souscripteur->nom) }} {{ strtoupper($ov->souscripteur->prenom) }}
        </span>
    </div>

    {{-- ══════════════════════════════════════════════════════
         TEXTE D'INVITATION
         ══════════════════════════════════════════════════════ --}}
    <div class="intro-text">
        Vous êtes invités à procéder au paiement de la <strong>{{ $trancheLabelFr }}</strong> tranche du prix
        de cession du <strong>Logement Promotionnel Aidé ({{ $typeProgramme }})</strong>
        identifié ci-dessous et ce, dans un délai de quinze jours (15j) à compter de la date
        d'établissement du présent ordre de versement.
    </div>

    {{-- ══════════════════════════════════════════════════════
         IDENTIFICATION DU LOGEMENT
         ══════════════════════════════════════════════════════ --}}
    <span class="section-title">Identification du logement :</span>

    <table class="field-table">
        <tr>
            <td class="field-label">- SITE</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $ov->souscripteur->logement->site->libelle ?? $ov->souscripteur->logement->programme->libelle ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">- BATIMENT</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $ov->souscripteur->logement->num_batiment ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">- N° Logement</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $ov->souscripteur->logement->num_logement ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">- Etage</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $ov->souscripteur->logement->etage ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">- Lot EDD</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $ov->souscripteur->logement->lot_edd ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">- TYPE</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $ov->souscripteur->logement->type_logement ?? $ov->souscripteur->logement->type ?? '' }}</td>
        </tr>
        <tr>
            <td class="field-label">- SUPERFICIE</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $ov->souscripteur->logement->superficie ?? '' }} M²</td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════════
         LE VERSEMENT DOIT S'EFFECTUER À L'ORDRE DE
         ══════════════════════════════════════════════════════ --}}
    <span class="section-title">Le versement doit s'effectuer à l'ordre de :</span>

    <table class="field-table">
        <tr>
            <td class="field-label">- Titulaire du compte</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $titulaire }}</td>
        </tr>
        <tr>
            <td class="field-label">- Agence</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $nomAgence }}</td>
        </tr>
        <tr>
            <td class="field-label">- Domiciliation bancaire</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $domiciliation }}</td>
        </tr>
        <tr>
            <td class="field-label">- RIB</td>
            <td class="field-colon">:</td>
            <td class="field-value">{{ $rib }}</td>
        </tr>
    </table>

    {{-- Prix et montant --}}
    <table class="montants-table">
        <tr>
            <td class="lbl">- Prix de cession du logement en TTC</td>
            <td class="field-colon">:</td>
            <td class="val">
                <strong>{{ number_format($ov->montant_total, 2, ',', ' ') }} DA</strong>
            </td>
        </tr>
        <tr>
            <td class="lbl">- Montant à verser en TTC ({{ $trancheLabelFr }} tranche)</td>
            <td class="field-colon">:</td>
            <td class="val">
                <strong>{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</strong>
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════════
         NB + QR CODE
         ══════════════════════════════════════════════════════ --}}
    <table class="nb-qr-table">
        <tr>
            <td class="nb-col">
                <span class="nb-title">NB :</span>
                <span class="nb-line">- Le versement ne peut être effectué que par et pour l'intéressé.</span>
                <span class="nb-line">- Dépassé le délai mentionné ci-dessus, l'ordre de versement est systématiquement annulé.</span>
            </td>
            <td class="qr-col">
                <img class="qr-img"
                     src="data:image/svg+xml;base64,{{ $ov->qrcode }}">
            </td>
        </tr>
    </table>

    {{-- ══════════════════════════════════════════════════════
         PIED DE PAGE
         ══════════════════════════════════════════════════════ --}}
    <hr class="footer-sep">

    <div class="printed-by">
        Printed by: {{ $userPrinted }} le: {{ $datePdf }}
    </div>

    <div class="footer-addr">
        O.P.G.I. Cité Rabia Tahar Bâtiment M/5 - Bab Ezzouar &nbsp; Tél. 023-83-16-59 &nbsp; Fax : 023-83-17-00<br>
        Site Web: https://opgi-darelbeida.dz/ &nbsp; Facebook : Opgi Dar El Beida &nbsp; Email: contact@opgi-darelbeida.dz
    </div>

</div>
</body>
</html>