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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('contract_type')->nullable();

            // Salaire & heures
            $table->decimal('base_salary', 10, 3)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 10, 3)->default(0);

            // Primes & indemnités
            $table->decimal('bonuses', 10, 3)->default(0);
            $table->decimal('allowances', 10, 3)->default(0);

            // Brut
            $table->decimal('gross_salary', 10, 3)->default(0);

            // Déductions (loi tunisienne)
            $table->decimal('cnss', 10, 3)->default(0);    // 9.68%
            $table->decimal('css', 10, 3)->default(0);     // 1%
            $table->decimal('irpp', 10, 3)->default(0);    // barème progressif

            // Net à payer
            $table->decimal('amount', 10, 3)->default(0);

            // Dates
            $table->date('launch_date')->nullable();
            $table->dateTime('done_time')->nullable();
            $table->unsignedTinyInteger('month')->nullable();   // 1-12
            $table->unsignedSmallInteger('year')->nullable();   // ex: 2026

            $table->enum('status', ['pending', 'done', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
