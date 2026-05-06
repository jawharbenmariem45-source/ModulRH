<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->nullable()->constrained('employers')->onDelete('cascade');
            $table->string('date')->nullable();                      // ✅ string pour formats sales
            $table->string('check_in_morning_time')->nullable();     // ✅ string pour heures sales
            $table->string('check_out_morning_time')->nullable();    // ✅ string pour heures sales
            $table->string('check_in_afternoon_time')->nullable();   // ✅ string pour heures sales
            $table->string('check_out_afternoon_time')->nullable();  // ✅ string pour heures sales
            $table->string('status')->nullable();                    // ✅ string pour statuts sales
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};