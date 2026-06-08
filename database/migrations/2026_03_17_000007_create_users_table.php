<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('last_name', 191)->nullable();
            $table->string('first_name', 191)->nullable();
            $table->string('email', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 191)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('gender', ['Homme', 'Femme'])->nullable();

            // Relations
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('departement_id')->nullable()->constrained('departements')->onDelete('set null');
            $table->foreignId('poste_id')->nullable()->constrained('postes')->onDelete('set null');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');

            // Infos RH
            $table->decimal('salary', 10, 3)->nullable();
            $table->string('contract_type', 191)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Infos fiscales
            $table->boolean('family_head')->default(false);
            $table->integer('children_count')->default(0);
            $table->integer('disabled_children_count')->default(0);
            $table->integer('student_children_count')->default(0);

            // Infos bancaires / CNSS
            $table->string('cnss', 191)->nullable();
            $table->string('rib', 191)->nullable();
            $table->string('rib_image', 191)->nullable();

            // Discipline
            $table->integer('discipline_score')->default(100);

            // Congés
            $table->integer('solde_conges')->default(30);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};