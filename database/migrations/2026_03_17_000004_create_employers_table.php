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
        Schema::create('employers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('department_id')->constrained('departements')->onDelete('cascade');
    $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
    $table->string('nom');
    $table->string('prenom');
    $table->string('email')->unique();
    $table->string('password')->nullable();
    $table->string('numero_telephone', 8)->nullable();
    $table->decimal('salaire', 10, 3)->nullable();
    $table->boolean('chef_famille')->default(false);
    $table->integer('nombre_enfants')->default(0);
    $table->integer('nombre_enfants_infirmes')->default(0);
    $table->integer('nombre_enfants_etudiants')->default(0); 
    $table->enum('type_contrat', ['CDI', 'CDD', 'CIVP', 'Karama']);
    $table->string('rib')->nullable();
    $table->string('rib_image')->nullable();
    $table->string('cnss', 10)->nullable();
    $table->date('date_debut')->nullable();
    $table->date('date_fin')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};