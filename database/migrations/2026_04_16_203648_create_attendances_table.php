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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->nullable()->constrained('employers')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->timestamp('check_in_morning_time')->nullable();
            $table->timestamp('check_out_morning_time')->nullable();
            $table->timestamp('check_in_afternoon_time')->nullable();
            $table->timestamp('check_out_afternoon_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'on_leave'])->default('present');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};