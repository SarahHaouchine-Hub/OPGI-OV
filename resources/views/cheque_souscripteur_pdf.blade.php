<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
/* ═══════════════════════════════════════════════════════════════════════
   CHÈQUE SOUSCRIPTEUR — O.P.G.I. Dar El Beida
   2 chèques par page A4 Portrait  (210mm × 297mm)
   Chaque chèque : 210mm × 128mm  + ligne de découpe centrale
   ═══════════════════════════════════════════════════════════════════════ */

@page { size: 210mm 297mm; margin: 0; }

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8.5pt;
    color: #111;
    background: #fff;
    width: 210mm;
}

/* ─── Page wrapper ─────────────────────────────────────────────────────── */
.page {
    width: 210mm;
    height: 297mm;
    display: block;
    position: relative;
}

/* ─── Ligne de découpe centrale ────────────────────────────────────────── */
.cut-line {
    width: 100%;
    height: 0;
    border-top: 1pt dashed #999;
    position: relative;
    margin: 0;
}
.cut-label {
    position: absolute;
    left: 50%;
    top: -6pt;
    transform: translateX(-50%);
    background: #fff;
    padding: 0 3mm;
    font-size: 6.5pt;
    color: #999;
    white-space: nowrap;
    font-style: italic;
}

/* ═══════════════════════════════════════════════════════════════════════
   UN CHÈQUE = .cheque-block
   Dimensions : 210mm × 128mm
   ═══════════════════════════════════════════════════════════════════════ */
.cheque-block {
    width: 210mm;
    height: 128mm;
    display: block;
    position: relative;
    overflow: hidden;
}

/* ─── Bandeau supérieur (header) ───────────────────────────────────────── */
.chq-header {
    width: 100%;
    height: 14mm;
    background: #1e3c72;
    display: table;
    border-collapse: collapse;
}

.chq-header-logo {
    display: table-cell;
    width: 18mm;
    vertical-align: middle;
    text-align: center;
    padding: 0 3mm;
    border-right: 0.5pt solid rgba(255,255,255,0.25);
}

.chq-header-logo img {
    width: 10mm;
    height: 10mm;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

.chq-header-title {
    display: table-cell;
    vertical-align: middle;
    padding: 0 4mm;
    color: #fff;
}

.chq-header-title .org {
    font-size: 6.5pt;
    font-weight: normal;
    opacity: 0.85;
    display: block;
}
.chq-header-title .doc-title {
    font-size: 11pt;
    font-weight: bold;
    letter-spacing: 0.5pt;
    display: block;
    margin-top: 0.5mm;
}
.chq-header-title .doc-sub {
    font-size: 7pt;
    opacity: 0.85;
    display: block;
    margin-top: 0.3mm;
}

.chq-header-meta {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
    padding: 0 4mm;
    color: #fff;
    white-space: nowrap;
}
.chq-header-meta .meta-row {
    font-size: 6.5pt;
    opacity: 0.85;
    display: block;
    line-height: 1.6;
}
.chq-header-meta .meta-code {
    font-size: 8pt;
    font-weight: bold;
    display: block;
    font-family: 'DejaVu Sans Mono', monospace;
    margin-top: 0.5mm;
}

/* ─── Sous-bandeau programme + tranche ────────────────────────────────── */
.chq-sub-bar {
    width: 100%;
    height: 7mm;
    display: table;
    background: #f1f4f9;
    border-bottom: 0.5pt solid #c5d3ed;
}

.chq-sub-bar td {
    display: table-cell;
    vertical-align: middle;
    padding: 0 4mm;
    font-size: 7.5pt;
}

.chq-sub-bar .left  { text-align: left; }
.chq-sub-bar .right { text-align: right; color: #555; }

.prog-pill {
    display: inline-block;
    font-size: 7pt;
    font-weight: bold;
    letter-spacing: 0.3pt;
    padding: 1pt 6pt;
    border-radius: 3pt;
    margin-right: 2mm;
}
.pill-lpa  { background: #fff3cd; color: #7a5200; border: 0.5pt solid #e6a817; }
.pill-lpl  { background: #e9ecef; color: #343a40; border: 0.5pt solid #adb5bd; }
.pill-lsp  { background: #cfe2ff; color: #0a3775; border: 0.5pt solid #2a5298; }

.tranche-pill {
    display: inline-block;
    font-size: 7pt;
    font-weight: bold;
    padding: 1pt 6pt;
    border-radius: 3pt;
    background: #1e3c72;
    color: #fff;
    margin-right: 2mm;
}

.montant-badge {
    display: inline-block;
    font-size: 8.5pt;
    font-weight: bold;
    color: #1e3c72;
    font-family: 'DejaVu Sans Mono', monospace;
}

/* ─── Corps du chèque : deux colonnes ──────────────────────────────────── */
.chq-body {
    width: 100%;
    border-collapse: collapse;
    /* hauteur = 128mm - 14mm header - 7mm subbar - 10mm footer = 97mm */
}

.chq-body td {
    vertical-align: top;
    padding: 3.5mm 5mm;
}

/* Colonne gauche — souscripteur */
.col-left {
    width: 50%;
    border-right: 0.8pt solid #c5d3ed;
    background: #fbfcff;
}

/* Colonne droite — logement */
.col-right {
    width: 50%;
    background: #fbfff9;
}

/* Titre de section dans le corps */
.section-lbl {
    font-size: 6.5pt;
    font-weight: bold;
    color: #1e3c72;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    border-bottom: 0.5pt solid #c5d3ed;
    padding-bottom: 1.2mm;
    margin-bottom: 2.5mm;
    display: block;
}

.section-lbl-r {
    font-size: 6.5pt;
    font-weight: bold;
    color: #1a6b3a;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    border-bottom: 0.5pt solid #9dd4b0;
    padding-bottom: 1.2mm;
    margin-bottom: 2.5mm;
    display: block;
}

/* Lignes de champs */
.f-table { width: 100%; border-collapse: collapse; }
.f-table tr { border: none; }
.f-table td { padding: 0.8mm 0; vertical-align: middle; font-size: 7.5pt; border: none; }

.f-lbl {
    color: #666;
    font-size: 6.8pt;
    width: 34%;
    padding-right: 2mm;
    white-space: nowrap;
}

.f-val {
    font-weight: bold;
    color: #111;
    border-bottom: 0.3pt dotted #ccc;
    padding-bottom: 0.3mm;
}

.f-val-mono {
    font-weight: bold;
    color: #111;
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 7pt;
    border-bottom: 0.3pt dotted #ccc;
    padding-bottom: 0.3mm;
}

/* Séparateur interne */
.inner-sep {
    border: none;
    border-top: 0.4pt dashed #ccc;
    margin: 2.5mm 0;
}

/* Sous-titre interne */
.sub-lbl {
    font-size: 6.5pt;
    font-weight: bold;
    color: #555;
    display: block;
    margin-bottom: 1.5mm;
}

/* Aide pill */
.aide-pill {
    display: inline-block;
    font-size: 6.5pt;
    font-weight: bold;
    padding: 0.8pt 5pt;
    border-radius: 3pt;
    margin-right: 1.5mm;
    margin-bottom: 1mm;
}
.aide-bnh   { background: #fff3cd; color: #7a5200; border: 0.5pt solid #e6a817; }
.aide-fnpos { background: #e2f0e8; color: #1a6b3a; border: 0.5pt solid #5cb87a; }

/* ─── Bande inférieure (MICR-like) ─────────────────────────────────────── */
.chq-footer {
    width: 100%;
    height: 10mm;
    background: #f8f9fa;
    border-top: 0.5pt solid #dde3f0;
    display: table;
    border-collapse: collapse;
}

.chq-footer td {
    display: table-cell;
    vertical-align: middle;
    padding: 0 5mm;
    font-size: 6.5pt;
}

.chq-footer .f-left {
    color: #555;
    text-align: left;
}

.chq-footer .f-center {
    text-align: center;
    color: #888;
    font-style: italic;
}

.chq-footer .f-right {
    text-align: right;
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 7pt;
    color: #1e3c72;
    font-weight: bold;
    letter-spacing: 1pt;
}

/* ─── Filet vertical décoratif au milieu ───────────────────────────────── */
.chq-block-inner {
    border-left: 4pt solid #1e3c72;
    padding-left: 0;
}

</style>
</head>
<body>
<div class="page">

@php
    $s        = $ov->souscripteur;
    $log      = $s->logement;
    $sit      = $log->site ?? null;
    $prog     = strtoupper(trim($log->programme->libelle ?? 'LPL'));
    $progLow  = strtolower($prog);

    /* Tranche */
    $trancheMap = [
        1 => '1ère', 2 => '2ème', 3 => '3ème', 4 => '4ème', 5 => '5ème',
    ];
    $trancheLabel = $trancheMap[$ov->numero_tranche ?? 1] ?? ($ov->numero_tranche . 'ème');

    /* Type OV */
    $typeLabel = match($ov->type_ov) {
        'credit_reel' => 'Crédit Réel',
        'credit_diff' => 'Différence Crédit',
        default       => 'Tranche normale',
    };

    /* Situation familiale */
    $sitFamMap   = ['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'];
    $sitFamLabel = $sitFamMap[$s->situation_familiale ?? ''] ?? ($s->situation_familiale ?? '—');

    /* Aides */
    $aideBnh   = $s->aides->firstWhere('type', 'bnh') ?? $s->aides->firstWhere('type', 'cnl') ?? null;
    $aideFnpos = $s->aides->firstWhere('type', 'fnpos') ?? null;

    /* Agence */
    $nomAgence  = $sit->nom_agence       ?? '—';
    $adrAgence  = $sit->adresse_agence   ?? ($sit->libelle ?? '—');
    $numCompte  = $sit->num_compte_agence ?? ($sit->num_agence ?? '—');
    $ribVal     = $sit->rib              ?? '—';

    /* Logo en base64 */
    $logoSrc = '';
    if (file_exists(public_path('images/OPGI.jpg'))) {
        $logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('images/OPGI.jpg')));
    } elseif (file_exists(public_path('images/AADL_logo.svg'))) {
        $logoSrc = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(public_path('images/AADL_logo.svg')));
    }
@endphp

{{-- ══════════════════════════════════════════════════════
     CHÈQUE 1  (recto)
     ══════════════════════════════════════════════════════ --}}
<div class="cheque-block">

    {{-- Header bleu --}}
    <div class="chq-header">
        <div class="chq-header-logo">
            @if($logoSrc)
                <img src="{{ $logoSrc }}">
            @endif
        </div>
        <div class="chq-header-title">
            <span class="org">O.P.G.I. Dar El Beida — Ordre de Versement</span>
            <span class="doc-title">BON DE VERSEMENT</span>
            <span class="doc-sub">{{ $typeLabel }} — {{ $trancheLabel }} tranche</span>
        </div>
        <div class="chq-header-meta">
            <span class="meta-row">Date d'émission</span>
            <span class="meta-row">{{ now()->format('d/m/Y') }}</span>
            <span class="meta-code">N° {{ $ov->id }}</span>
        </div>
    </div>

    {{-- Sous-bandeau --}}
    <table class="chq-sub-bar">
        <tr>
            <td class="left">
                <span class="prog-pill pill-{{ $progLow }}">{{ $prog }}</span>
                <span class="tranche-pill">{{ $trancheLabel }} tranche</span>
                @if($ov->type_ov === 'credit_reel')
                    <span class="prog-pill" style="background:#d4edda;color:#155724;border:0.5pt solid #5cb87a;">Crédit Réel</span>
                @elseif($ov->type_ov === 'credit_diff')
                    <span class="prog-pill" style="background:#ffe5d0;color:#7a3600;border:0.5pt solid #e6781a;">Différence</span>
                @endif
            </td>
            <td class="right">
                Montant à verser :&nbsp;
                <span class="montant-badge">{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</span>
            </td>
        </tr>
    </table>

    {{-- Corps --}}
    <table class="chq-body">
        <tr>

            {{-- ─── GAUCHE : Souscripteur ─── --}}
            <td class="col-left">
                <span class="section-lbl">&#9656; Souscripteur</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">Nom &amp; Prénom</td>
                        <td class="f-val">{{ strtoupper($s->nom) }} {{ $s->prenom }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">N.I.N</td>
                        <td class="f-val-mono">{{ $s->nin ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Date naissance</td>
                        <td class="f-val">{{ $s->date_naissance ? \Carbon\Carbon::parse($s->date_naissance)->format('d/m/Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Lieu naissance</td>
                        <td class="f-val">{{ $s->lieu_naissance ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Situation</td>
                        <td class="f-val">{{ $sitFamLabel }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Père</td>
                        <td class="f-val">{{ $s->nom_pere ?? '—' }} {{ $s->prenom_pere ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Mère</td>
                        <td class="f-val">{{ $s->nom_mere ?? '—' }} {{ $s->prenom_mere ?? '' }}</td>
                    </tr>
                </table>

                @if($s->situation_familiale === 'marie' && $s->conjoint_nom)
                <hr class="inner-sep">
                <span class="sub-lbl">Conjoint(e)</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">Nom &amp; Prénom</td>
                        <td class="f-val">{{ strtoupper($s->conjoint_nom) }} {{ $s->conjoint_prenom }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">N.I.N conjoint</td>
                        <td class="f-val-mono">{{ $s->conjoint_nin ?? '—' }}</td>
                    </tr>
                </table>
                @endif

                @if($aideBnh || $aideFnpos)
                <hr class="inner-sep">
                <span class="sub-lbl">Aides accordées</span>
                @if($aideBnh)
                    <span class="aide-pill aide-bnh">BNH/CNL : {{ number_format($aideBnh->montant, 2, ',', ' ') }} DA</span>
                @endif
                @if($aideFnpos)
                    <span class="aide-pill aide-fnpos">FNPOS : 500 000,00 DA</span>
                @endif
                @endif
            </td>

            {{-- ─── DROITE : Logement + Site ─── --}}
            <td class="col-right">
                <span class="section-lbl-r">&#9656; Logement &amp; Projet</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">Projet / Site</td>
                        <td class="f-val">{{ $sit->libelle ?? $log->programme->libelle ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Code logement</td>
                        <td class="f-val-mono">{{ $s->code_loge_lpl ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Bâtiment</td>
                        <td class="f-val">{{ $log->num_batiment ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Étage / Porte</td>
                        <td class="f-val">{{ $log->num_etage ?? '—' }} / {{ $log->num_porte ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Lot</td>
                        <td class="f-val">{{ $log->num_lot ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Typologie</td>
                        <td class="f-val">{{ $log->typologie ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Surface</td>
                        <td class="f-val">{{ $log->surface ?? '—' }} M²</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Prix TTC logement</td>
                        <td class="f-val">{{ number_format($log->prix ?? 0, 2, ',', ' ') }} DA</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Montant à verser</td>
                        <td class="f-val" style="color:#1e3c72;font-size:8.5pt;">{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Reste après versement</td>
                        <td class="f-val">{{ number_format($ov->montant_restant, 2, ',', ' ') }} DA</td>
                    </tr>
                </table>

                <hr class="inner-sep">
                <span class="sub-lbl">Virement à effectuer</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">Agence</td>
                        <td class="f-val">{{ $nomAgence }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Adresse</td>
                        <td class="f-val">{{ $adrAgence }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">RIB / Compte</td>
                        <td class="f-val-mono">{{ $ribVal }}</td>
                    </tr>
                </table>
            </td>

        </tr>
    </table>

    {{-- Footer MICR-like --}}
    <table class="chq-footer">
        <tr>
            <td class="f-left" style="width:40%;">
                O.P.G.I. Dar El Beida — Bab Ezzouar<br>
                Tél : 023-83-16-59
            </td>
            <td class="f-center" style="width:30%;">
                Délai de validité : 15 jours
            </td>
            <td class="f-right" style="width:30%;">
                @if($ov->qrcode)
                    <img src="data:image/svg+xml;base64,{{ $ov->qrcode }}"
                         style="width:8mm;height:8mm;vertical-align:middle;margin-right:2mm;">
                @endif
                OV-{{ str_pad($ov->id, 6, '0', STR_PAD_LEFT) }}
            </td>
        </tr>
    </table>

</div>
{{-- /cheque 1 --}}

{{-- ══════════════════════════════════════════════════════
     LIGNE DE DÉCOUPE
     ══════════════════════════════════════════════════════ --}}
<div class="cut-line">
    <span class="cut-label">✂ &nbsp; Découpez ici &nbsp; ✂</span>
</div>

{{-- ══════════════════════════════════════════════════════
     CHÈQUE 2  (copie — coupon à remettre au service)
     ══════════════════════════════════════════════════════ --}}
<div class="cheque-block">

    {{-- Header — couleur différente pour distinguer la copie --}}
    <div class="chq-header" style="background:#2e7d32;">
        <div class="chq-header-logo">
            @if($logoSrc)
                <img src="{{ $logoSrc }}">
            @endif
        </div>
        <div class="chq-header-title">
            <span class="org">O.P.G.I. Dar El Beida — Exemplaire Service Commercial</span>
            <span class="doc-title">BON DE VERSEMENT (COPIE)</span>
            <span class="doc-sub">{{ $typeLabel }} — {{ $trancheLabel }} tranche</span>
        </div>
        <div class="chq-header-meta">
            <span class="meta-row">Date d'émission</span>
            <span class="meta-row">{{ now()->format('d/m/Y') }}</span>
            <span class="meta-code">N° {{ $ov->id }}</span>
        </div>
    </div>

    {{-- Sous-bandeau --}}
    <table class="chq-sub-bar">
        <tr>
            <td class="left">
                <span class="prog-pill pill-{{ $progLow }}">{{ $prog }}</span>
                <span class="tranche-pill">{{ $trancheLabel }} tranche</span>
                @if($ov->type_ov === 'credit_reel')
                    <span class="prog-pill" style="background:#d4edda;color:#155724;border:0.5pt solid #5cb87a;">Crédit Réel</span>
                @elseif($ov->type_ov === 'credit_diff')
                    <span class="prog-pill" style="background:#ffe5d0;color:#7a3600;border:0.5pt solid #e6781a;">Différence</span>
                @endif
            </td>
            <td class="right">
                Montant à verser :&nbsp;
                <span class="montant-badge">{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</span>
            </td>
        </tr>
    </table>

    {{-- Corps identique mais condensé --}}
    <table class="chq-body">
        <tr>
            <td class="col-left">
                <span class="section-lbl">&#9656; Souscripteur</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">Nom &amp; Prénom</td>
                        <td class="f-val">{{ strtoupper($s->nom) }} {{ $s->prenom }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">N.I.N</td>
                        <td class="f-val-mono">{{ $s->nin ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Date naissance</td>
                        <td class="f-val">{{ $s->date_naissance ? \Carbon\Carbon::parse($s->date_naissance)->format('d/m/Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Lieu naissance</td>
                        <td class="f-val">{{ $s->lieu_naissance ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Situation</td>
                        <td class="f-val">{{ $sitFamLabel }}</td>
                    </tr>
                </table>

                @if($aideBnh || $aideFnpos)
                <hr class="inner-sep">
                <span class="sub-lbl">Aides</span>
                @if($aideBnh)
                    <span class="aide-pill aide-bnh">BNH/CNL : {{ number_format($aideBnh->montant, 2, ',', ' ') }} DA</span>
                @endif
                @if($aideFnpos)
                    <span class="aide-pill aide-fnpos">FNPOS : 500 000,00 DA</span>
                @endif
                @endif

                {{-- Zone signature --}}
                <hr class="inner-sep">
                <span class="sub-lbl">Cachet &amp; Signature de réception</span>
                <div style="height:18mm;border:0.5pt dashed #bbb;border-radius:3pt;margin-top:1mm;"></div>
            </td>

            <td class="col-right">
                <span class="section-lbl-r">&#9656; Logement &amp; Projet</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">Projet / Site</td>
                        <td class="f-val">{{ $sit->libelle ?? $log->programme->libelle ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Code logement</td>
                        <td class="f-val-mono">{{ $s->code_loge_lpl ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Bâtiment / Porte</td>
                        <td class="f-val">{{ $log->num_batiment ?? '—' }} — {{ $log->num_porte ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Typologie</td>
                        <td class="f-val">{{ $log->typologie ?? '—' }} / {{ $log->surface ?? '—' }} M²</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Prix TTC</td>
                        <td class="f-val">{{ number_format($log->prix ?? 0, 2, ',', ' ') }} DA</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Montant à verser</td>
                        <td class="f-val" style="color:#1e3c72;font-size:8.5pt;">{{ number_format($ov->montant_paye, 2, ',', ' ') }} DA</td>
                    </tr>
                </table>

                <hr class="inner-sep">
                <span class="sub-lbl">Virement — {{ $nomAgence }}</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">RIB / Compte</td>
                        <td class="f-val-mono">{{ $ribVal }}</td>
                    </tr>
                </table>

                {{-- Zone N° reçu banque --}}
                <hr class="inner-sep">
                <span class="sub-lbl">À remplir après versement</span>
                <table class="f-table">
                    <tr>
                        <td class="f-lbl">N° reçu banque</td>
                        <td class="f-val" style="min-width:50mm;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="f-lbl">Date versement</td>
                        <td class="f-val">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Footer copie --}}
    <table class="chq-footer">
        <tr>
            <td class="f-left" style="width:40%;">
                O.P.G.I. Dar El Beida — Bab Ezzouar<br>
                Tél : 023-83-16-59
            </td>
            <td class="f-center" style="width:30%;">
                Remettre ce coupon au service commercial
            </td>
            <td class="f-right" style="width:30%;">
                @if($ov->qrcode)
                    <img src="data:image/svg+xml;base64,{{ $ov->qrcode }}"
                         style="width:8mm;height:8mm;vertical-align:middle;margin-right:2mm;">
                @endif
                OV-{{ str_pad($ov->id, 6, '0', STR_PAD_LEFT) }}
            </td>
        </tr>
    </table>

</div>
{{-- /cheque 2 --}}

</div>
</body>
</html>