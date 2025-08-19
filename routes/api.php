<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FactureController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Routes d'authentification
Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

// Routes protégées avec Sanctum jutement pour les utilisateurs authentifiés et qui possede un token
Route::middleware("auth:sanctum")->group(function() {
    // Routes clients
    Route::post("/logout", [AuthController::class, "logout"]); // Déconnexion de l'utilisateur
    Route::prefix('clients')->group(function () {
        Route::get('getclient/{clientid}', [ClientController::class, 'show']); // Obtenir les détails d'un client
        Route::get('/', [ClientController::class, 'get_clients']); // Liste des clients
        Route::post('/storeClient', [ClientController::class, 'store']); // Créer un nouveau client
    });
    
    // Routes factures
    Route::prefix('factures')->group(function () {
        Route::post('/createfacture', [FactureController::class, 'store']); // Créer une nouvelle facture
        Route::get('/listeFactures', [FactureController::class, 'index']); // Lister toutes les factures
        Route::get('/search', [FactureController::class, 'search']); // Rechercher des factures par client ou date
        Route::get('/{factureid}/export', [FactureController::class, 'export']); // Exporter une facture en JSON
        Route::get('/{factureid}', [FactureController::class, 'show']); // Détails d'une facture
    });
});