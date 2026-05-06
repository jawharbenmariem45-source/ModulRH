<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('type_contrat')->nullable();
            $table->string('salaire_base')->nullable();
            $table->string('heures_sup')->nullable();
            $table->string('montant_heures_sup')->nullable();
            $table->string('primes')->nullable();
            $table->string('indemnites')->nullable();
            $table->string('salaire_brut')->nullable();
            $table->string('cnss')->nullable();
            $table->string('irpp')->nullable();
            $table->string('css')->nullable();
            $table->string('amount')->nullable();
            $table->string('launch_date')->nullable();
            $table->string('done_time')->nullable();
            $table->string('status')->nullable();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};