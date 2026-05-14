<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('contract_type')->nullable();      
            $table->string('base_salary')->nullable();        
            $table->string('overtime_hours')->nullable();     
            $table->string('overtime_amount')->nullable();   
            $table->string('bonuses')->nullable();           
            $table->string('allowances')->nullable();        
            $table->string('gross_salary')->nullable();      
            $table->string('cnss')->nullable();
            $table->string('irpp')->nullable();
            $table->string('css')->nullable();
            $table->string('amount')->nullable();            
            $table->string('launch_date')->nullable();
            $table->string('done_time')->nullable();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};