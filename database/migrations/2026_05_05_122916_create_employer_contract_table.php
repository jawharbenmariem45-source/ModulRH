<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employer_contract')) {
            Schema::create('employer_contract', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employer_id')
                      ->constrained('employers')
                      ->onDelete('cascade');
                $table->foreignId('contract_id')
                      ->constrained('contracts')
                      ->onDelete('cascade');
                $table->string('start_date')->nullable(); // ✅ string pour formats sales
                $table->string('end_date')->nullable();   // ✅ string pour formats sales
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_contract');
    }
};