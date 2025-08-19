<?php
// Migration pour la table principale des factures

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id(); // Identifiant unique auto-incrémenté
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Référence au client avec suppression en cascade
            $table->date('date'); // Date d'émission de la facture
            $table->decimal('total_ht', 10, 2)->default(0); // Montant total hors taxes
            $table->decimal('total_tva', 10, 2)->default(0); // Montant total de la TVA
            $table->decimal('total_ttc', 10, 2)->default(0); // Montant total TTC
            $table->timestamps(); // creation de la collone created_at et updated_at

            $table->index('date'); // Index pour optimiser les recherches par date
            $table->index(['client_id', 'date']); // Index composite pour les recherches client/période
        });
    }

    public function down(): void
    {
        // Suppression de la table en cas de rollback
        Schema::dropIfExists('factures');
    }
};