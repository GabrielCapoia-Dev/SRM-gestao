<?php

namespace Database\Seeders;

use App\Models\Turma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TurmaCodigoSeeder extends Seeder
{
    public function run(): void
    {
        // Atualiza todas (ou troque por ->whereNull('codigo') se preferir só as sem código)
        Turma::query()->chunkById(500, function ($turmas) {
            foreach ($turmas as $turma) {
                $letra = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $turma->turma));
                if (blank($letra)) {
                    // sem letra de turma, pula
                    continue;
                }

                $codigo = 'TR' . $letra; // TRA, TRB, TRC...
                // Se quiser garantir sempre 3 caracteres:
                $codigo = Str::upper(Str::substr($codigo, 0, 3));

                // Só atualiza se mudou
                if ($turma->codigo !== $codigo) {
                    $turma->codigo = $codigo;
                    $turma->save();
                }
            }
        });
    }
}
