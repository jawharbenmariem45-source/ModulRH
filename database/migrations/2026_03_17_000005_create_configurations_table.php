<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->enum('regime_horaire', ['40h', '48h'])->default('40h');
            $table->enum('type', [
                'PAYMENT_DATEE',
                'APP_NAME',
                'DEVELOPPER_NAME',
                'ANOTHER',
                'REGIME_HORAIRE'
            ])->default('ANOTHER')->comment('table de configuration');
            $table->string('value');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};