<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // si la company n'a pas shift
            $table->enum('type', ['entree', 'sortie']);
            $table->timestamp('pointage_at');
            $table->foreignId('shift_user_id')->nullable()->constrained('shift_user')->nullOnDelete(); // si la company has shift
 
            // Reconnaissance faciale
            $table->boolean('face_matched')->default(false);
 
            // Blockchain
            $table->string('tx_hash', 66)->nullable();
            $table->string('block_number')->nullable();
            $table->enum('blockchain_statut', ['pending', 'confirmed', 'failed'])->default('pending');
 
            // Appareil
            $table->string('device_ref')->nullable();
 
            $table->timestamps();
 
            // Index
            $table->index(['user_id', 'pointage_at']);
            $table->index('blockchain_statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
