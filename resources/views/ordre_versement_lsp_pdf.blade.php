<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
{{--
    ═══════════════════════════════════════════════════
    ORDRE DE VERSEMENT — LSP | OPGI Dar El Beida
    Style  : Minimaliste & Épuré (Thème Vert LSP)
    Format : setPaper([0, 0, 419.53, 230], 'portrait')
    ═══════════════════════════════════════════════════
--}}
<style>
@page {
    margin: 0px;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html, body {
    width: 100%;
    height: 100%;
    overflow: hidden;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5pt;
    color: #0d1f0f;
    background: #f1f8f2;
}

/* ── CADRE PRINCIPAL ─────────────────────────────── */
.page {
    width: 100%;
    border: 1pt solid #4caf50;
    position: relative;
    overflow: hidden;
    background: #f1f8f2;
    box-sizing: border-box;
    padding-bottom: 2mm;
}

/* Filigrane */
.watermark {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    font-size: 90pt; font-weight: bold;
    color: #1b5e20; opacity: 0.04;
    white-space: nowrap; pointer-events: none;
    z-index: 1000;
}

/* ── CONTENU ─────────────────────────────────────── */
.content {
    position: relative;
    margin: 4pt 5mm 0 5mm;
    z-index: 3;
}

/* ══ EN-TÊTE ════════════════════════════════════════ */
.hdr {
    display: table; width: 100%; border-collapse: collapse;
    border-bottom: 0.5pt solid #81c784;
    padding-bottom: 2pt; margin-bottom: 2pt;
    height: 9mm;
}
.hdr-logo, .hdr-logo-opgi {
    display: table-cell; width: 9%;
    vertical-align: middle; text-align: center;
}
.hdr-logo img, .hdr-logo-opgi img { width: 8mm; height: 8mm; }

.hdr-fr {
    display: table-cell; width: 41%;
    vertical-align: middle; padding-left: 3pt;
    font-size: 4pt; font-weight: bold;
    line-height: 1.5; color: #1a3a1c;
}
.hdr-fr .fr-org { font-size: 4.2pt; font-weight: bold; color: #145216; margin-top: 0.5pt; }

.hdr-ar {
    display: table-cell; width: 41%;
    vertical-align: middle; padding-right: 3pt;
    font-size: 4pt; font-weight: bold;
    line-height: 1.5; text-align: right; direction: rtl; color: #1a3a1c;
}
.hdr-ar .ar-org { font-size: 4.2pt; font-weight: bold; color: #145216; margin-top: 0.5pt; }

/* ══ BANDEAU ════════════════════════════════════════ */
.banner {
    display: table; width: 100%; border-collapse: collapse;
    background: linear-gradient(180deg, #81c784 0%, #388e3c 100%);
    height: 7mm; margin-bottom: 2pt;
    border-radius: 2pt;
}
.ban-left {
    display: table-cell; width: 20%;
    vertical-align: middle; text-align: center;
    padding: 0.5pt 3pt;
}
.ban-left .t-num { font-size: 11pt; font-weight: bold; color: #0d1f0f; line-height: 1; }
.ban-left .t-suf { font-size: 4.5pt; font-weight: bold; color: #1a3a1c; }

.ban-center {
    display: table-cell; width: 60%;
    vertical-align: middle; text-align: center;
    padding: 0.5pt 4pt;
}
.ban-center .prog-lbl {
    font-size: 5.5pt;
    font-weight: bold;
    color: #0d1f0f;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    margin-bottom: 1pt;
}
.ban-center .ov-title {
    font-size: 7.5pt; font-weight: bold;
    color: #81c784; text-transform: uppercase; letter-spacing: 1.5pt;
}

.ban-right {
    display: table-cell; width: 20%;
    vertical-align: middle; text-align: center; padding: 0.5pt 3pt;
}
.ban-right .ref-lbl { font-size: 3.5pt; color: #1a3a1c; text-transform: uppercase; }
.ban-right .ref-val { font-size: 5.5pt; font-weight: bold; color: #0d1f0f; font-family: 'DejaVu Sans Mono', monospace; }

/* ══ IDENTITÉ ═══════════════════════════════════════ */
.id-row {
    display: table; width: 100%; border-collapse: collapse;
    padding-bottom: 1pt; margin-bottom: 1pt;
}
.id-left  { display: table-cell; vertical-align: bottom; }
.id-right { display: table-cell; vertical-align: bottom; text-align: right; white-space: nowrap; }
.id-lbl { font-size: 3.8pt; color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3pt; }
.id-val {
    font-size: 7pt; font-weight: bold; color: #0d1f0f;
    text-transform: uppercase;
    border-bottom: 0.5pt solid #4caf50;
    padding-bottom: 0.5pt; display: inline-block; min-width: 65mm;
}
.nin-lbl { font-size: 3.5pt; color: #2e7d32; text-transform: uppercase; }
.nin-val  { font-size: 5pt; font-weight: bold; font-family: 'DejaVu Sans Mono', monospace; color: #0d1f0f; letter-spacing: 0.5pt; }

/* ══ LOGEMENT ═══════════════════════════════════════ */
.log-row {
    font-size: 4.5pt; color: #1a3a1c;
    padding-bottom: 2pt; margin-bottom: 2pt;
}

/* ══ PAYEZ / MONTANT ════════════════════════════════ */
.pay-row {
    display: table; width: 100%; border-collapse: collapse;
}
.pay-left {
    display: table-cell; width: 60%;
    vertical-align: top; padding-right: 6pt;
}
.pay-right {
    display: table-cell; width: 40%;
    vertical-align: top; padding-left: 2pt;
}

.payez-invite { font-size: 4.5pt; line-height: 1.5; color: #0d1f0f; }
.payez-invite strong { color: #145216; }

.prix-cession-row { font-size: 4.3pt; color: #1a3a1c; margin: 2pt 0 1pt; }
.prix-cession-row strong { color: #0d1f0f; }

.pay-info-lbl { font-size: 3.5pt; color: #2e7d32; text-transform: uppercase; margin-bottom: 0.2pt; }
.pay-info-val { font-size: 4.5pt; font-weight: bold; color: #0d1f0f; margin-bottom: 1.5pt; }

/* Aides */
.aide-row {
    font-size: 4pt; color: #1a3a1c;
    margin-top: 1.5pt;
    padding: 1.5pt 2pt;
    background: #e8f5e9;
    border-radius: 2pt;
    border-left: 1.5pt solid #4caf50;
}
.aide-row strong { color: #145216; }

/* Cadre montant */
.montant-frame {
    background: #e8f5e9;
    padding: 2pt 3pt;
    text-align: right;
    margin-bottom: 1pt;
    border-radius: 2pt;
    border: 0.5pt solid #81c784;
}
.montant-lbl { font-size: 3.5pt; color: #2e7d32; text-transform: uppercase; letter-spacing: 0.5pt; display: block; }
.montant-chiffres { font-size: 8.5pt; font-weight: bold; color: #0d1f0f; font-family: 'DejaVu Sans Mono', monospace; line-height: 1; }
.montant-devise { font-size: 4.5pt; font-weight: bold; color: #1b5e20; margin-top: 0.5pt; }

/* Lettres */
.letters-row {
    padding: 2pt;
    margin-top: 1pt;
    font-size: 4pt; font-weight: bold; font-style: italic;
    color: #1a3a1c; background: #dcedc8;
    border-radius: 2pt;
    text-align: right;
}

.pct-row {
    font-size: 4pt;
    color: #1a3a1c;
    text-align: right;
    margin-top: 3pt;
}
.pct-row strong { color: #145216; }

/* ══ PIED ════════════════════════════════════════════ */
.footer-bar {
    position: relative;
    margin-top: 2mm;
    background: linear-gradient(180deg, #81c784 0%, #388e3c 100%);
    border-radius: 2pt;
}
.footer-inner {
    display: table; width: 100%; border-collapse: collapse;
    height: 8mm;
}
.ft-delai {
    display: table-cell; width: 100%;
    vertical-align: middle; padding: 1.5pt 5pt;
    font-size: 4.8pt; color: #81c784; text-align: center;
}
.ft-qr {
    display: table-cell; width: 15%;
    vertical-align: middle; text-align: center; padding: 1pt;
}
.ft-qr img { width: 7.5mm; height: 7.5mm; }

/* ══ NOTES ════════════════════════════════════════ */
.notes-bar {
    position: relative;
    margin-top: 1.5mm;
    padding-left: 5mm;
    padding-right: 5mm;
    text-align: center;
}
.note {
    display: block; font-size: 3pt; line-height: 1.3; color: #145216; margin-bottom: 2pt;
}
.note::before { content: "✦ "; }
.contact-info {
    font-size: 2.8pt;
    color: #1a3a1c;
    line-height: 1.4;
    border-top: 0.5pt solid #81c784;
    padding-top: 1.5pt;
}
</style>
</head>
<body>
<div class="page">

    <div class="watermark">OPGI</div>

    <div class="content">

        {{-- ══ EN-TÊTE ══ --}}
        <div class="hdr">
            <div class="hdr-logo">
                @if(!empty($logoRepB64))
                    <img src="{{ $logoRepB64 }}" alt="Rep">
                @else
                    <svg width="22" height="22" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="30" cy="30" r="27" stroke="#2e7d32" stroke-width="2" fill="none"/>
                        <ellipse cx="30" cy="30" rx="10" ry="27" stroke="#2e7d32" stroke-width="1.5" fill="none"/>
                        <line x1="3" y1="30" x2="57" y2="30" stroke="#2e7d32" stroke-width="1.5"/>
                        <polygon points="30,6 33,14 27,14" fill="#4caf50"/>
                    </svg>
                @endif
            </div>
            <div class="hdr-fr">
                <div>République Algérienne Démocratique et Populaire</div>
                <div>Ministère de l'Habitat, de l'Urbanisme et de la Ville</div>
                <div class="fr-org">Office de Promotion et de Gestion Immobilière de Dar El Beida</div>
            </div>
            <div class="hdr-ar">
                <div>{{ $republique }}</div>
                <div>{{ $ministere_ar }}</div>
                <div class="ar-org">{{ $dar_beida_ar }} {{ $opgi_nom_ar }}</div>
            </div>
            <div class="hdr-logo-opgi">
                @if(!empty($logoOpgiB64))
                    <img src="{{ $logoOpgiB64 }}" alt="OPGI">
                @else
                    <svg width="22" height="22" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="10" width="52" height="42" rx="3" stroke="#2e7d32" stroke-width="2" fill="#e8f5e9"/>
                        <rect x="12" y="17" width="36" height="7" rx="2" fill="#4caf50"/>
                        <text x="18" y="40" font-size="8" font-family="sans-serif" fill="#2e7d32" font-weight="bold">OPGI</text>
                    </svg>
                @endif
            </div>
        </div>

        {{-- ══ BANDEAU ══ --}}
        @php
            $ordinals = [1=>'1',2=>'2',3=>'3',4=>'4',5=>'5'];
            $suffixes = [1=>'ère Tranche',2=>'ème Tranche',3=>'ème Tranche',4=>'ème Tranche',5=>'ème Tranche'];
            $num = $ov->numero_tranche ?? 1;
        @endphp
        <div class="banner">
            <div class="ban-left">
                <div class="t-num">{{ $ordinals[$num] ?? $num }}</div>
                <div class="t-suf">{{ $suffixes[$num] ?? 'ème Tranche' }}</div>
            </div>
            <div class="ban-center">
                <div class="prog-lbl">Programme : {{ $typeProgramme }}</div>
                <div class="ov-title">Ordre de Versement</div>
            </div>
            <div class="ban-right">
                <div class="ref-lbl">Code logement</div>
                <div class="ref-val">{{ $ov->souscripteur->code_loge_lpl ?? '—' }}</div>
            </div>
        </div>

        {{-- ══ IDENTITÉ ══ --}}
        <div class="id-row">
            <div class="id-left">
                <div class="id-lbl">Payez à l'ordre de</div>
                <div class="id-val">{{ strtoupper($ov->souscripteur->nom) }}&nbsp;{{ strtoupper($ov->souscripteur->prenom) }}</div>
            </div>
            <div class="id-right">
                @if($ov->souscripteur->nin)
                    <div class="nin-lbl">N.I.N :</div>
                    <div class="nin-val">{{ $ov->souscripteur->nin }}</div>
                @endif
            </div>
        </div>

        {{-- ══ LOGEMENT ══ --}}
        @php
            $logement    = $ov->souscripteur->logement;
            $site        = $logement->site ?? null;
            $prog        = $logement->programme ?? null;
            $nbsp        = "\xc2\xa0";
            $mChiffres   = number_format((float)$ov->montant_paye, 2, ',', $nbsp);
            $prixCession = number_format((float)($logement->prix ?? 0), 2, ',', $nbsp);
            $nomBanque   = $banqueNom ?? $site->banque_nom ?? $site->nom_agence ?? '—';
            $numRib      = $ribLsp ?? $site->rib ?? '—';
            $titulaire   = $site->titulaire ?? 'O.P.G.I. Dar El Beida';
            $nbsp        = "\xc2\xa0";

            // Aides LSP
            $montantBnh   = (float)($aideBnh->montant ?? 0);
            $montantFnpos = $aideFnpos ? 500000.00 : 0.0;
            $prixNet      = number_format(max(0, ($logement->prix ?? 0) - $totalAides), 2, ',', $nbsp);
        @endphp
        <div class="log-row">
            Bât.&nbsp;<strong>{{ $logement->num_batiment ?? '—' }}</strong>
            &nbsp;—&nbsp;Ét.&nbsp;<strong>{{ $logement->num_etage ?? '—' }}</strong>
            &nbsp;/&nbsp;N° Logement&nbsp;<strong>{{ $logement->num_porte ?? '—' }}</strong>
            &nbsp;—&nbsp;Lot EDD&nbsp;<strong>{{ $logement->num_lot ?? '—' }}</strong>
            &nbsp;—&nbsp;<strong>{{ $logement->type_logement ?? $logement->typologie ?? '—' }}</strong>
            &nbsp;({{ $logement->superficie ?? $logement->surface ?? '—' }}&nbsp;m²)
            &nbsp;—&nbsp;Site :&nbsp;<strong>{{ $siteLibelle ?? $site->libelle ?? $prog->libelle ?? '—' }}</strong>
        </div>

        {{-- ══ PAYEZ / MONTANT ══ --}}
        <div class="pay-row">
            <div class="pay-left">

                <div class="payez-invite">
                    Vous êtes invité(e) à effectuer le versement de la
                    <strong>{{ $trancheLabelFr }} tranche</strong>
                    du prix de cession du logement
                    <strong>({{ $typeProgramme }})</strong>
                    dans un délai de <strong>30 jours</strong>.
                </div>
                <div class="prix-cession-row">
                    Prix de cession :&nbsp;<strong>{!! $prixCession !!}&nbsp;DA</strong>
                </div>

                {{-- Aides déduites --}}
                @if($aideBnh)
                    <div class="aide-row">
                        Aide BNH :&nbsp;<strong>−&nbsp;{{ number_format($montantBnh, 2, ',', "\xc2\xa0") }}&nbsp;DA</strong>
                    </div>
                @endif
                @if($aideFnpos)
                    <div class="aide-row">
                        Aide FNPOS :&nbsp;<strong>−&nbsp;500&nbsp;000,00&nbsp;DA</strong>
                    </div>
                @endif
                @if($aideBnh || $aideFnpos)
                    <div class="aide-row" style="margin-top: 1pt; background: #c8e6c9;">
                        Prix net à payer :&nbsp;<strong>{!! $prixNet !!}&nbsp;DA</strong>
                    </div>
                @endif

                <div class="pay-info-lbl" style="margin-top: 4pt;">Banque / Agence destinataire</div>
                <div class="pay-info-val">
                    {{ $nomBanque }}
                    @if(!empty($site->num_agence) && $site->num_agence !== '—')
                        &nbsp;— N°&nbsp;{{ $site->num_agence }}
                    @endif
                    @if(!empty($site->adresse_agence))
                        <br><span style="font-weight:normal; font-size:4pt;">{{ $site->adresse_agence }}</span>
                    @endif
                </div>

                <div class="pay-info-lbl">Numéro de compte (RIB)</div>
                <div class="pay-info-val">{{ $numRib }}</div>

                <div class="pay-info-lbl">Titulaire du compte</div>
                <div class="pay-info-val">{{ $titulaire }}</div>

            </div>
            <div class="pay-right">

                <div class="montant-frame">
                    <span class="montant-lbl">Montant</span>
                    <div class="montant-chiffres">{!! $mChiffres !!}</div>
                    <div class="montant-devise">Dinar Algérien</div>
                </div>

                <div class="letters-row">{{ mb_strtoupper($montantEnLettres, 'UTF-8') }}</div>

                <div class="pct-row">
                    Représentant :&nbsp;<strong>{{ $ov->pourcentage }}&nbsp;%</strong>&nbsp;à l'ordre de&nbsp;<strong>O.P.G.I. Dar El Beida</strong>.
                </div>

            </div>
        </div>

    </div>{{-- /.content --}}

    {{-- ══ PIED ══ --}}
    @php
        $dateCouper = now()->addDays(30)->format('d/m/Y');
        $qrB64 = $ov->qrcode ? 'data:image/svg+xml;base64,' . $ov->qrcode : '';
    @endphp
    <div class="footer-bar">
        <div class="footer-inner">
            <div class="ft-delai">
                <strong>Délai : (30) trente jours</strong>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Le : {{ $datePdf }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Expire : {{ $dateCouper }}</strong>
            </div>
            <div class="ft-qr">
                @if($qrB64)<img src="{{ $qrB64 }}" alt="QR">@endif
            </div>
        </div>
    </div>

    {{-- ══ NOTES & CONTACTS ══ --}}
    <div class="notes-bar">
        <span class="note">Le versement ne peut être effectué que par et pour l'intéressé. Dépassé le délai mentionné ci-dessus, l'ordre de versement est systématiquement annulé.</span>
        <div class="contact-info">
            O.P.G.I. Cité Rabia Tahar Bâtiment M/5 - Bab Ezzouar &nbsp;&nbsp;•&nbsp;&nbsp; Tél. 023-83-16-59 &nbsp;&nbsp;•&nbsp;&nbsp; Fax: 023-83-17-00 <br>
            Site Web: https://opgi-darelbeida.dz/ &nbsp;&nbsp;•&nbsp;&nbsp; Facebook : Opgi Dar El Beida &nbsp;&nbsp;•&nbsp;&nbsp; Email: contact@opgi-darelbeida.dz
        </div>
    </div>

</div>{{-- /.page --}}
</body>
</html>