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
        Schema::create('aluno_retencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_aluno')->constrained('alunos')->cascadeOnDelete();

            $table->enum('vezes_retido', [
                '1 vez' => '1 vez',
                '2 vezes' => '2 vezes',
                '3 vezes' => '3 vezes',
                '4 ou mais' => '4 ou mais',
            ]);

            $table->foreignId('id_serie')->nullable()->constrained('series')->nullOnDelete();

            $table->json('motivo_retido')->nullable();
            $table->json('ano_retido')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluno_retencoes');
    }
};
