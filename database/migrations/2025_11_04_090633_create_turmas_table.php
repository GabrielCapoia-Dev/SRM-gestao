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
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->min(3)->max(3);
            $table->foreignId('id_serie')->constrained('series')->restrictOnDelete();
            $table->foreignId('id_escola')->constrained('escolas')->restrictOnDelete();
            $table->string('turma')->max(1);
            $table->enum('turno', ['Manhã', 'Tarde','Noite']);

            $table->timestamps();
            
            $table->unique(['id_escola', 'id_serie', 'turno', 'turma'], 'turma_unica_por_contexto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turmas');
    }
};
