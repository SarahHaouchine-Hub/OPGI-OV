<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\LogementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SouscripteurController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesistementController;
use App\Http\Controllers\OvController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Page d'accueil → login
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', fn() => view('auth.login'));

// ─────────────────────────────────────────────────────────────────────────────
// Admin uniquement
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'is_admin'])->group(function () {
    Route::resource('users', AdminController::class)->except(['show']);
    Route::patch('/admin/users/{user}/toggle', [AdminController::class, 'toggleStatus'])
         ->name('admin.users.toggle');
});

// ─────────────────────────────────────────────────────────────────────────────
// Routes authentifiées
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'decode.hashids'])->group(function () {

    // ── Profil ────────────────────────────────────────────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Dashboard ─────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Sites & Logements ─────────────────────────────────────────────
    Route::post('/site/store',     [SiteController::class,    'store'])->name('site.store');
    Route::post('/logement/store', [LogementController::class,'store'])->name('logement.store');

    // ── Souscripteurs ─────────────────────────────────────────────────
    Route::get('/ajouter-souscripteur',    [SouscripteurController::class, 'create'])->name('souscripteur.create');
    Route::post('/save',                   [SouscripteurController::class, 'store'])->name('souscripteur.store');
    Route::get('/souscripteur/fiche/{id}', [SouscripteurController::class, 'generateFiche'])->name('souscripteur.fiche');

    Route::post('/souscripteurs/import/lpl', [SouscripteurController::class, 'importLpl'])->name('souscripteur.import.lpl');
    Route::post('/souscripteurs/import/lsp', [SouscripteurController::class, 'importLsp'])->name('souscripteur.import.lsp');
    Route::post('/souscripteurs/import/lpa', [SouscripteurController::class, 'importLpa'])->name('souscripteur.import.lpa');
    Route::post('/logements/import',         [SouscripteurController::class, 'importLogements'])->name('logements.import');

    // ── Ordres de versement (OV) ──────────────────────────────────────
    Route::get('/ov',                          [OvController::class, 'index'])->name('ov.index');
    Route::get('/ov/create/{souscripteur_id}', [OvController::class, 'create'])->name('ov.create');
    Route::post('/ov/store',                   [OvController::class, 'store'])->name('ov.store');
    Route::post('/ov/store/lpa',               [OvController::class, 'storeLpa'])->name('ov.store.lpa');
    Route::post('/ov/store/lsp',               [OvController::class, 'storeLsp'])->name('ov.store.lsp');
    Route::post('/ov/aide/store',              [OvController::class, 'storeAide'])->name('ov.aide.store');
    Route::post('/ov/credit/store',            [OvController::class, 'storeCreditBancaire'])->name('ov.credit.store');
    Route::get('/ov/pdf/{id}',                 [OvController::class, 'generatePDF'])->name('ov.pdf');
    Route::get('/ov/paiement/{ovId}',          [OvController::class, 'createPaiement'])->name('paiement.create');
    Route::post('/ov/paiement/store',          [OvController::class, 'storePaiement'])->name('paiement.store');
    Route::post('/ov/credit/ov-diff',          [OvController::class, 'storeOvCredit'])->name('ov.credit.ov_diff');

    // ── Désistements & Remplacements ──────────────────────────────────
    Route::get('/desistement',
        [DesistementController::class, 'listLogements'])->name('desistement');

    Route::post('/desistement/{idLogement}/remplacer',
        [DesistementController::class, 'remplacer'])->name('createRemplacement');

    // ── API recherche NIN (désistement) ───────────────────────────────
    Route::get('/api/souscripteur/search-nin/{nin}',
        [DesistementController::class, 'searchByNin'])->name('api.souscripteur.search-nin');

    // ── API Dashboard ─────────────────────────────────────────────────
    Route::prefix('api/dashboard')->group(function () {
        Route::get('/batiments',            [DashboardController::class, 'batiment']);
        Route::get('/etages/{bat}',         [DashboardController::class, 'etage']);
        Route::get('/portes/{bat}/{etage}', [DashboardController::class, 'porte']);
    });

    // ── API Géographique ──────────────────────────────────────────────
    Route::prefix('api')->group(function () {
        Route::get('/communes/{wilayaId}',         [DashboardController::class, 'communes']);
        Route::get('/sites/{wilayaId}',            [SiteController::class, 'sitesByWilaya']);
        Route::get('/logements-site/{siteId}',     [DashboardController::class, 'logementsBySite'])->name('api.logements.site');
    });

    // ── API Cascade souscripteur ──────────────────────────────────────
    Route::prefix('api/souscripteur')->group(function () {
        Route::get('/programmes-by-wilaya/{wilayaId}',             [SouscripteurController::class, 'programmesByWilaya']);
        Route::get('/sites/{wilayaId}/{programmeId}',              [SouscripteurController::class, 'sitesByWilayaProgramme']);
        Route::get('/batiments/{siteId}',                          [SouscripteurController::class, 'batimentsBySite']);
        Route::get('/etages/{siteId}/{batiment}',                  [SouscripteurController::class, 'etagesBySiteBat']);
        Route::get('/portes/{siteId}/{batiment}/{etage}',          [SouscripteurController::class, 'portesBySiteBatEtage']);

        Route::get('/programmes-by-wilaya/{wilayaId}',             [DashboardController::class, 'programmesByWilaya'])->name('dash.programmes');
        Route::get('/sites-by-wilaya-programme/{wilayaId}/{progId}',[DashboardController::class, 'sitesByWilayaProgramme']);
        Route::get('/batiments-dash/{siteId}',                     [DashboardController::class, 'batimentsBySite']);
        Route::get('/etages-dash/{siteId}/{batiment}',             [DashboardController::class, 'etagesBySiteBatiment']);
    });

});

require __DIR__ . '/auth.php';