<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\FactureLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{

    /**
     * Afficher la liste de toutes les factures
     */
    public function index()
    {
        // Je récupère toutes les factures avec leurs clients et lignes
        $factures = Facture::with(['client', 'factureslines'])->get();
        
        // Je renvoie le tout en JSON, normalement ça devrait marcher
        return response()->json(['factures' => $factures]);
    }

    /**
     * Créer une nouvelle facture avec ses lignes
     */
    public function store(Request $request)
    {
        // D'abord je valide les données reçues
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'date' => 'required|date',
                'lignes' => 'required|array|min:1',
                'lignes.*.description' => 'required|string|max:255',
                'lignes.*.quantite' => 'required|numeric|min:0.01', 
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'lignes.*.taux_tva' => 'required|in:0,5.5,10,20' 
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Si ya une erreur de validation, je renvoie les erreurs
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()
            ], 422);
        }

        // Je commence une transaction pour être sûr que tout se passe bien
        DB::beginTransaction();

        try {
            // Je crée la facture sans les totaux pour l'instant
            $facture = Facture::create([
                'client_id' => $validated['client_id'],
                'date' => $validated['date'],
                'total_ht' => 0,  // Je mettrai à jour après
                'total_tva' => 0, // Pareil pour la TVA
                'total_ttc' => 0  // Et le TTC aussi
            ]);

            // Maintenant je traite chaque ligne de la facture
            foreach ($validated['lignes'] as $ligneData) {
                $ligne = new FactureLine([
                    'description' => $ligneData['description'],
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire_ht' => $ligneData['prix_unitaire'],
                    'taux_tva' => $ligneData['taux_tva']
                ]);
                
                // J'ajoute la ligne à la facture, les calculs se font automatiquement
                $facture->lignes()->save($ligne);
            }

            // Je calcule les totaux maintenant que toutes les lignes sont ajoutées
            $facture->calculateTotals();
            $facture->save(); // Je sauvegarde les totaux

            // Tout est bon, je valide la transaction
            DB::commit();

            // Je recharge avec les infos client et lignes pour la réponse
            $facture->load(['client', 'lignes']);

            // Je renvoie une réponse de succès avec tous les détails
            return response()->json([
                'success' => true,
                'message' => '✅ Facture #' . $facture->id . ' créée avec succès',
                'data' => [
                    'facture_id' => $facture->id,
                    'client_id' => $facture->client_id,
                    'date_facture' => $facture->date,
                    'total_ht' => $facture->total_ht,
                    'total_tva' => $facture->total_tva,
                    'total_ttc' => $facture->total_ttc,
                    'nombre_lignes' => $facture->lignes->count(),
                    'lignes' => $facture->lignes,
                    'client' => $facture->client
                ]
            ], 201);

        } catch (\Throwable $e) {
            // Si ya une erreur, j'annule tout
            DB::rollBack();
            
            // Je log l'erreur pour debugger plus tard
            Log::error('Erreur création facture: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            // Je renvoie une erreur avec un message
            return response()->json([
                'success' => false,
                'error' => 'Impossible de créer la facture: ' . $e->getMessage(),
                'ref' => 'ERR_FACT_' . time()
            ], 500);
        }
    }


    /**
     * Afficher une facture spécifique
     */
    public function show($factureid)
    {   
        // Je cherche la facture avec ses lignes
        $facture = Facture::with(['factureslines'])->findOrFail($factureid);
        
        // Je renvoie la facture en JSON
        return response()->json([
            'facture' => $facture->load(['factureslines'])
        ]);
    }

    /**
     * Exporter une facture en format JSON
     */
    public function export($factureid)
    {
        // Je charge la facture avec le client et les lignes
        $facture = Facture::with(['client', 'factureslines'])->findOrFail($factureid);
        
        // Je prépare les données pour l'export
        $exportData = [
            'facture_id' => $facture->id,
            'date' => $facture->date,
            'client' => [
                'nom' => $facture->client->nom,
                'email' => $facture->client->email,
                'siret' => $facture->client->siret
            ],
            'lignes' => $facture->factureslines->map(function ($ligne) {
                return [
                    'description' => $ligne->description,
                    'quantite' => $ligne->quantite,
                    'prix_unitaire_ht' => $ligne->prix_unitaire_ht,
                    'taux_tva' => $ligne->taux_tva,
                    'montant_ht' => $ligne->montant_ht,
                    'montant_tva' => $ligne->montant_tva,
                    'montant_ttc' => $ligne->montant_ttc
                ];
            }),
            'total' => [
                'total_ht' => $facture->total_ht,
                'total_tva' => $facture->total_tva,
                'total_ttc' => $facture->total_ttc
            ]
        ];

        // Je renvoie les données d'export
        return response()->json($exportData);
    }

    /**
     * Rechercher des factures par client ou date
     */
    public function search(Request $request)
{
    // Je valide direct avec la request, c'est plus simple
    $validated = $request->validate([
        'client_id' => 'nullable|exists:clients,id',
        'date_debut' => 'nullable|date',
        'date_fin' => 'nullable|date|after_or_equal:date_debut'
    ]);

    // Je prépare ma requête de base avec les relations
    $query = Facture::with(['client', 'factureslines']);

    // Filtre par client si y'en a un
    if (!empty($validated['client_id'])) {
        $query->where('client_id', $validated['client_id']);
    }

    // Filtre par date de début si y'en a une
    if (!empty($validated['date_debut'])) {
        $query->whereDate('date', '>=', $validated['date_debut']);
    }

    // Filtre par date de fin si y'en a une
    if (!empty($validated['date_fin'])) {
        $query->whereDate('date', '<=', $validated['date_fin']);
    }

    // J'exécute et je renvoie les résultats
    return response()->json(['factures' => $query->get()]);
}
}