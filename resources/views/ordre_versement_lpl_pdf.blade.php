<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 0px; }
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
    width: 100%; height: 100%; overflow: hidden;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5pt; color: #0b1a30; background: #f0f6fa;
}
.page {
    width: 100%; border: 1pt solid #5b9bd5; position: relative;
    overflow: hidden; background: #f0f6fa; box-sizing: border-box;
    padding-bottom: 1mm;
}
.watermark {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    font-size: 80pt; font-weight: bold; color: #2b5797; opacity: 0.06;
    white-space: nowrap; pointer-events: none; z-index: 1000;
}
.content { position: relative; margin: 2pt 5mm 0 5mm; z-index: 3; }

.hdr { display: table; width: 100%; border-collapse: collapse; border-bottom: 0.5pt solid #8faadc; padding-bottom: 1pt; margin-bottom: 1pt; height: 7mm; }
.hdr-logo, .hdr-logo-opgi { display: table-cell; width: 9%; vertical-align: middle; text-align: center; }
.hdr-logo img, .hdr-logo-opgi img { width: 12mm; height: 12mm; }
.hdr-fr { display: table-cell; width: 41%; vertical-align: middle; padding-left: 2pt; font-size: 4pt; font-weight: bold; line-height: 1.3; color: #1d2a44; white-space: nowrap; }
.hdr-fr .fr-org { font-size: 4.2pt; font-weight: bold; color: #16365c; margin-top: 0.5pt; }
.hdr-ar { display: table-cell; width: 41%; vertical-align: middle; padding-right: 2pt; font-size: 4pt; font-weight: bold; line-height: 1.3; text-align: right; direction: rtl; color: #1d2a44; }
.hdr-ar .ar-org { font-size: 4.2pt; font-weight: bold; color: #16365c; margin-top: 0.5pt; }

.banner { display: table; width: 100%; border-collapse: collapse; background: linear-gradient(180deg, #8faadc 0%, #41719c 100%); height: 6mm; margin-bottom: 1pt; border-radius: 2pt; }
.ban-left { display: table-cell; width: 20%; vertical-align: middle; text-align: center; padding: 0.5pt 2pt; }
.ban-left .t-num-line { line-height: 1; }
.ban-left .t-num { font-size: 10pt; font-weight: bold; color: #0b1a30; }
.ban-left .t-suf-inline { font-size: 4.5pt; font-weight: bold; color: #1d2a44; vertical-align: baseline; }
.ban-left .t-tranche { font-size: 4.5pt; font-weight: bold; color: #1d2a44; }
.ban-center { display: table-cell; width: 60%; vertical-align: middle; text-align: center; padding: 0.5pt 2pt; }
.ban-center .prog-lbl { font-size: 5pt; font-weight: bold; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5pt; margin-bottom: 0.5pt; }
.ban-center .ov-title { font-size: 7pt; font-weight: bold; color: #0b1a30; text-transform: uppercase; letter-spacing: 1.5pt; }
.ban-right { display: table-cell; width: 20%; vertical-align: middle; text-align: center; padding: 0.5pt 2pt; }
.ban-right .ref-lbl { font-size: 3.5pt; color: #1d2a44; text-transform: uppercase; }
.ban-right .ref-val { font-size: 5.5pt; font-weight: bold; color: #0b1a30; font-family: 'DejaVu Sans Mono', monospace; }

.id-row { display: table; width: 100%; border-collapse: collapse; padding-bottom: 0.5pt; margin-bottom: 0.5pt; }
.id-left { display: table-cell; vertical-align: bottom; }
.id-right { display: table-cell; vertical-align: bottom; text-align: right; white-space: nowrap; }
.id-lbl { font-size: 3.5pt; color: #2f5597; text-transform: uppercase; letter-spacing: 0.3pt; }
.id-val { font-size: 6.5pt; font-weight: bold; color: #0b1a30; text-transform: uppercase; border-bottom: 0.5pt solid #5b9bd5; padding-bottom: 0.5pt; display: inline-block; min-width: 65mm; }
.nin-lbl { font-size: 3.5pt; color: #2f5597; text-transform: uppercase; }
.nin-val { font-size: 5pt; font-weight: bold; font-family: 'DejaVu Sans Mono', monospace; color: #0b1a30; letter-spacing: 0.5pt; }

.log-row { font-size: 4.5pt; color: #1d2a44; padding-bottom: 1pt; margin-bottom: 1pt; }

.pay-row { display: table; width: 100%; border-collapse: collapse; }
.pay-left { display: table-cell; width: 60%; vertical-align: top; padding-right: 4pt; }
.pay-right { display: table-cell; width: 40%; vertical-align: top; padding-left: 2pt; }

.payez-invite { font-size: 4.5pt; line-height: 1.3; color: #0b1a30; }
.payez-invite strong { color: #16365c; }
.prix-cession-row { font-size: 4.3pt; color: #1d2a44; margin: 1pt 0 1pt; }
.prix-cession-row strong { color: #0b1a30; }

.montant-frame { background: #e9f0f8; padding: 2pt; text-align: right; margin-bottom: 1pt; border-radius: 2pt; border: 0.5pt solid #8faadc; }
.montant-lbl { font-size: 3.5pt; color: #2f5597; text-transform: uppercase; letter-spacing: 0.5pt; display: block; }
.montant-chiffres { font-size: 8pt; font-weight: bold; color: #0b1a30; font-family: 'DejaVu Sans Mono', monospace; line-height: 1; }
.montant-devise { font-size: 4.5pt; font-weight: bold; color: #2b5797; margin-top: 0.5pt; }
.letters-row { padding: 1.5pt; margin-top: 1pt; font-size: 4pt; font-weight: bold; font-style: italic; color: #1d2a44; background: #e2eef9; border-radius: 2pt; text-align: right; }

.pay-info-lbl { font-size: 3.5pt; color: #2f5597; text-transform: uppercase; margin-bottom: 0.2pt; }
.pay-info-val { font-size: 4.5pt; font-weight: bold; color: #0b1a30; margin-bottom: 1pt; }

.footer-bar { position: relative; margin-top: 0.5mm; background: linear-gradient(180deg, #8faadc 0%, #41719c 100%); border-radius: 2pt; }
.footer-inner { display: table; width: 100%; border-collapse: collapse; height: 11mm; }
.ft-delai { display: table-cell; width: 85%; vertical-align: middle; padding: 1pt 5pt; font-size: 4.5pt; color: #0b1a30; text-align: center; }
.ft-qr { display: table-cell; width: 15%; vertical-align: middle; text-align: center; padding: 1pt; }
.ft-qr img { width: 12mm; height: 12mm; }

.notes-bar { position: relative; margin-top: 0.5mm; padding-left: 5mm; padding-right: 5mm; text-align: center; }
.note { display: block; font-size: 3pt; line-height: 1.1; color: #16365c; margin-bottom: 0.5pt; }
.note::before { content: "✦ "; }
.contact-info { font-size: 2.8pt; color: #1d2a44; line-height: 1.2; border-top: 0.5pt solid #8faadc; padding-top: 0.5pt; }
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
                        <circle cx="30" cy="30" r="27" stroke="#2b5797" stroke-width="2" fill="none"/>
                        <ellipse cx="30" cy="30" rx="10" ry="27" stroke="#2b5797" stroke-width="1.5" fill="none"/>
                        <line x1="3" y1="30" x2="57" y2="30" stroke="#2b5797" stroke-width="1.5"/>
                        <polygon points="30,6 33,14 27,14" fill="#5b9bd5"/>
                    </svg>
                @endif
            </div>
            <div class="hdr-fr">
                <div>République Algérienne Démocratique et Populaire</div>
                <div>Ministère de l'Habitat, de l'urbanisme, de la Ville et de l'Aménagement du Territoire</div>
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
                        <rect x="4" y="10" width="52" height="42" rx="3" stroke="#2b5797" stroke-width="2" fill="#e9f0f8"/>
                        <rect x="12" y="17" width="36" height="7" rx="2" fill="#5b9bd5"/>
                        <text x="18" y="40" font-size="8" font-family="sans-serif" fill="#2b5797" font-weight="bold">OPGI</text>
                    </svg>
                @endif
            </div>
        </div>

        {{-- ══ BANDEAU ══ --}}
        @php
            $ordinals = [1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5'];
            $suffixes = [1=>'ère', 2=>'ème', 3=>'ème', 4=>'ème', 5=>'ème'];
            $num = $ov->numero_tranche ?? 1;
        @endphp
        <div class="banner">
            <div class="ban-left">
                <div class="t-num-line">
                    <span class="t-num">{{ $ordinals[$num] ?? $num }}</span><span class="t-suf-inline">{{ $suffixes[$num] ?? 'ème' }}</span>
                </div>
                <div class="t-tranche">Tranche</div>
            </div>
            <div class="ban-center">
                <div class="prog-lbl">Programme : PROMOTIONNEL / LPL</div>
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
                <div class="id-val">Mr/Mme {{ strtoupper($ov->souscripteur->nom) }}&nbsp;{{ strtoupper($ov->souscripteur->prenom) }}</div>
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
            $numRib      = $ribLpl ?? $site->rib ?? '—';
            $titulaire   = $site->titulaire ?? 'O.P.G.I. Dar El Beida';
        @endphp
        <div class="log-row">
            &nbsp;—&nbsp;Projet :&nbsp;<strong>{{ $site->libelle ?? $prog->libelle ?? '—' }}</strong><br>
            —&nbsp;Bât.&nbsp;<strong>{{ $logement->num_batiment ?? '—' }}</strong>
            &nbsp;/&nbsp;N° Logement&nbsp;<strong>{{ $logement->num_porte ?? '—' }}</strong>
            &nbsp;—&nbsp;Ét.&nbsp;<strong>{{ $logement->num_etage ?? '—' }}</strong>
            &nbsp;—&nbsp;Lot EDD&nbsp;<strong>{{ $logement->num_lot ?? '—' }}</strong>
            &nbsp;—&nbsp;<strong>{{ $logement->typologie ?? '—' }}</strong>
            &nbsp;({{ $logement->surface ?? '—' }}&nbsp;m²)
        </div>

        {{-- ══ PAYEZ / MONTANT ══ --}}
        <div class="pay-row">
            <div class="pay-left">
                <div class="payez-invite">
                    Vous êtes invité(e) à effectuer le versement de la
                    <strong>{{ $trancheLabelFr }} tranche</strong>
                    du prix de cession du programme
                    <strong>(PROMOTIONNEL)</strong>
                    dans un délai de <strong>30 jours</strong>.
                </div>
                <div class="prix-cession-row">
                    Prix de cession :&nbsp;<strong>{!! $prixCession !!}&nbsp;DA</strong>
                </div>

                <div class="pay-info-lbl" style="margin-top: 2pt;">Banque / Agence destinataire</div>
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
                    <span class="montant-lbl">Montant à verser {{ $trancheLabelFr }} tranche</span>
                    <div class="montant-chiffres">{!! $mChiffres !!}</div>
                    <div class="montant-devise">Dinars Algériens</div>
                </div>
                <div class="letters-row">{{ mb_strtoupper($montantEnLettres, 'UTF-8') }}</div>
            </div>
        </div>
    </div>

    {{-- ══ PIED ══ --}}
    @php
        $dateCouper = now()->addDays(30)->format('d/m/Y');
        $qrB64 = $ov->qrcode ? 'data:image/svg+xml;base64,' . $ov->qrcode : '';
    @endphp
    <div class="footer-bar">
        <div class="footer-inner">
            <div class="ft-delai">
                <strong>Délai : (30) trente jours</strong> &nbsp;&nbsp;|&nbsp;&nbsp; 
                Le : {{ $datePdf }} &nbsp;&nbsp;|&nbsp;&nbsp; 
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
        <span class="note">L'intéressé doit se déplacer au niveau du notaire dans un délai de trente 30 jrs après le paiement de la 1ère tranche pour signature de l'acte s.v.p.</span>
        <div class="contact-info">
            O.P.G.I. Cité Rabia Tahar Bâtiment M/5 - Bab Ezzouar &nbsp;&nbsp;•&nbsp;&nbsp; Tél. 023-83-16-59 &nbsp;&nbsp;•&nbsp;&nbsp; Fax: 023-83-17-00 <br>
            Site Web: https://opgi-darelbeida.dz/ &nbsp;&nbsp;•&nbsp;&nbsp; Facebook : Opgi Dar El Beida &nbsp;&nbsp;•&nbsp;&nbsp; Email: contact@opgi-darelbeida.dz
        </div>
    </div>
</div>
</body>
</html>