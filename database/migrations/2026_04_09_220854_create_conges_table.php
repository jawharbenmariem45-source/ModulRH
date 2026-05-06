<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('date_debut')->nullable(); // ✅ string pour formats sales
            $table->string('date_fin')->nullable();   // ✅ string pour formats sales
            $table->string('nombre_jours')->nullable();// ✅ string pour valeurs sales
            $table->string('motif')->nullable();
            $table->string('statut')->nullable();     // ✅ string pour statuts sales
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};