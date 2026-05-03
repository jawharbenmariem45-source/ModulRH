<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('conges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
    $table->string('type')->nullable();
    $table->date('date_debut');
    $table->date('date_fin');
    $table->integer('nombre_jours')->nullable();
    $table->string('motif')->nullable();
    $table->string('statut')->default('En attente');
    $table->text('commentaire')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};
