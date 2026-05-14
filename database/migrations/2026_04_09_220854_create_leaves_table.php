<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {  
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('start_date')->nullable();     
            $table->string('end_date')->nullable();       
            $table->string('days_count')->nullable();     
            $table->string('reason')->nullable();         
            $table->string('document')->nullable();
            $table->string('status')->nullable();         
            $table->text('comment')->nullable();          
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};