<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departements')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->string('last_name');                           
            $table->string('first_name');                           
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('phone', 20)->nullable();                
            $table->string('salary')->nullable();                  
            $table->boolean('family_head')->default(false);         
            $table->integer('children_count')->default(0);           
            $table->integer('disabled_children_count')->default(0);  
            $table->integer('student_children_count')->default(0);   
            $table->string('contract_type')->nullable();            
            $table->string('rib')->nullable();
            $table->string('rib_image')->nullable();
            $table->string('cnss')->nullable();
            $table->string('start_date')->nullable();                
            $table->string('end_date')->nullable();                 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};