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
            $table->string('reference');
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('type_contrat')->nullable();
            $table->decimal('salaire_base', 10, 3)->default(0);
            $table->decimal('heures_sup', 10, 3)->default(0);
            $table->decimal('montant_heures_sup', 10, 3)->default(0);
            $table->decimal('primes', 10, 3)->default(0);
            $table->decimal('indemnites', 10, 3)->default(0);
            $table->decimal('salaire_brut', 10, 3)->default(0);
            $table->decimal('cnss', 10, 3)->default(0);
            $table->decimal('irpp', 10, 3)->default(0);
            $table->decimal('css', 10, 3)->default(0);
            $table->decimal('amount', 10, 3); // salaire net
            $table->dateTime('launch_date');
            $table->dateTime('done_time');
            $table->enum('status', ['SUCCESS', 'FAILED'])->default('SUCCESS');
            $table->enum('month', [
                'JANVIER', 'FEVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN',
                'JUILLET', 'AOUT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DECEMBRE'
            ]);
            $table->string('year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};