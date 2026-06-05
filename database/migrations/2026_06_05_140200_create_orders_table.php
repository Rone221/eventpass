<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();          // Réf interne (EVP-XXXX)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->integer('total_amount');                // Total en XOF
            $table->string('status')->default('pending')->index();

            // Coordonnées de l'acheteur (réutilisées pour Paydunya & email)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            // Données fournisseur de paiement
            $table->string('payment_provider')->nullable();     // paydunya | fake
            $table->string('payment_token')->nullable()->index(); // invoice token Paydunya
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
