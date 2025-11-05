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
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_turma')->constrained('turmas')->restrictOnDelete();
            $table->foreignId('id_professor')->nullable()->constrained('professores')->onDelete('cascade');
            $table->foreignId('id_laudo')->nullable()->constrained('laudos')->onDelete('cascade');
            $table->string('nome');
            $table->string('cgm')->unique();
            $table->enum('sexo', ['Masculino', 'Feminino']);
            $table->date('data_nascimento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
