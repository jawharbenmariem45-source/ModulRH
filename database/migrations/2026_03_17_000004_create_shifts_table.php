<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->enum('type', ['morning', 'afternoon', 'night', 'two_shifts']);
            $table->time('starts_at');
            $table->time('ends_at');
            $table->time('pause_start')->nullable();
            $table->time('pause_end')->nullable();
            $table->json('work_days')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schifts');
    }
};
