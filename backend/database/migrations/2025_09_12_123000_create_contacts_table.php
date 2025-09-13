<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 120);
            $table->string('cpf', 11)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->json('address')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->unique(['user_id', 'cpf']); // aceita múltiplos NULLs (sqlite/postgres)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};

