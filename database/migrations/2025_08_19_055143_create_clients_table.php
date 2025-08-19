<?php
// database/migrations/2024_08_19_000001_create_clients_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique(); // email unique pour eviter les doublons
            $table->string('siret', 14)->unique(); // SIRET unique pour identifier le client
            $table->date('date_creation'); // date de création du client
            $table->timestamps(); // creation de la collone created_at et updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
?>