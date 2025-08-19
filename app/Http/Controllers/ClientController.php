<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // function qui retourne la liste des clients
    public function get_clients()
    {
        $clients = Client::all();
        return response()->json([
            'clients' => $clients
        ]);
    }

    /*function qui permet de créer un nouveau client
        elle prend en parametre un objet Request
            elle retourne un json avec le client créé
                ou un message d'erreur en cas d'échec */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'email' => 'required|email|unique:clients',
                'siret' => 'required|string|size:14|unique:clients',
                'date_creation' => 'required|date'
            ]);

            $client = Client::create($validated);

            return response()->json([
                'client' => $client
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue.'
            ], 500);
        }
    }

    // function qui retourne les details d'un client et ses factures
    // elle prend en parametre l'id du client
    // elle retourne un json avec le client et ses factures
    // ou un message d'erreur en cas d'échec
    // elle utilise la relation factures() définie dans le modèle Client pour recuperer les factures lié au client
    public function show($clientid)
    {
        $client = Client::findOrFail($clientid);
        $factures = $client->factures()->with('factureslines')->get();

        return response()->json([
            'client' => $client,
            'factures' => $factures
        ]);
    }
}
