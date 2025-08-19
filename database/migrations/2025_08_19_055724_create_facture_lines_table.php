<?php
// Migration pour la gestion des lignes de factures

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facture_lines', function (Blueprint $table) {
            $table->id(); // Identifiant unique auto-incrémenté
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade'); // Clé étrangere avec suppression en cascade
            $table->string('description'); // Description détaillée du produit ou service
            $table->decimal('quantite', 10, 2); // Quantité avec support des décimales
            $table->decimal('prix_unitaire_ht', 10, 2); // Prix unitaire hors taxes
            $table->decimal('taux_tva', 5, 2); // Taux de TVA applicabl
            $table->decimal('montant_ht', 10, 2); // Montant total hors taxes
            $table->decimal('montant_tva', 10, 2); // Montant de la TVA calculée
            $table->decimal('montant_ttc', 10, 2); // Montant total TTC (TVA inclus)
            $table->timestamps(); // creation de la collone created_at et updated_at
        });
        
 
    }

    public function down(): void
    {
        // Suppression de la tabl en cas de rolback
        Schema::dropIfExists('facture_lines');
    }
};
