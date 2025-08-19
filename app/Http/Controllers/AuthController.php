<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // sécuriser le mot de passse . 
        ]);
    
        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Trouver l'utilisateur par emaill
        $user = User::where('email', $request->email)->first();

        // verifier le mot de passse est ce qu'il est identique a celui dans la base de donnée 
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
    
        // supprimer si ya des token existant afin de génerer un nouveau 
        $user->tokens()->delete();
    
        // Créer un token a travers sanctum 
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete(); // supprimer le token relié avec cet utilisateuur
    
            return response()->json([
                'message' => 'utilisateur deconnecté avec succès'
            ])->header('Access-Control-Allow-Origin', 'http://localhost:5173')
              ->header('Access-Control-Allow-Credentials', 'true');
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'deconnexion impossible',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}