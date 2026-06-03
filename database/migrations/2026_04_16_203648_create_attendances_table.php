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
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->dateTime('morning_check_in')->nullable();
            $table->dateTime('morning_check_out')->nullable();
            $table->dateTime('afternoon_check_in')->nullable();
            $table->dateTime('afternoon_check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'on_leave'])->default('absent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
