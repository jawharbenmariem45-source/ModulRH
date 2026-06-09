<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_encodings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->json('encoding');
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['actived', 'inactived', 'archived'])->default('actived');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_encodings');
    }
};