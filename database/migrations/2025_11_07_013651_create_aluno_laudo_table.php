<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aluno_laudo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('aluno_id')
                ->constrained('alunos')
                ->cascadeOnDelete();

            $table->foreignId('laudo_id')
                ->constrained('laudos')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        // Se já existe id_laudo em alunos e você quiser migrar dados antigos:
        // Schema::table('aluno_laudo', function (Blueprint $table) { ... });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_laudo');
    }
};
