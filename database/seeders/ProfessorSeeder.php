<?php

namespace Database\Seeders;

use App\Models\Escola;
use App\Models\Professor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $turnos = ['Manhã', 'Tarde', 'Noite'];

        $especializacoes = [
            'Magisterio',
            'Licenciatura',
            'Bacharelado',
            'Pos Graduacao',
            'Doutorado',
            'Mestrado',
        ];

        $escolas = Escola::query()->get(['id', 'nome']);

        $matricSeq = 1; // sequencial global para matrícula/e-mail

        foreach ($escolas as $escola) {
            $schoolSlug = Str::slug($escola->nome ?: "escola-{$escola->id}", '-');

            $jaTemSrm   = false;
            $jaTemApoio = false;

            $idxGlobal = 0; // índice global para alternar SRM/Apoio de forma determinística

            foreach ($turnos as $turno) {
                foreach ($especializacoes as $esp) {
                    // === Regra de mutual exclusão (sempre um dos dois) ===
                    $professorSrm      = ($idxGlobal % 2 === 0); // pares = SRM
                    $profissionalApoio = ! $professorSrm;        // ímpares = Apoio
                    $idxGlobal++;

                    // marca presença por escola
                    if ($professorSrm)      $jaTemSrm   = true;
                    if ($profissionalApoio) $jaTemApoio = true;

                    // Nome & e-mail “estáveis”
                    $nomeBase = sprintf(
                        '%s %s %s',
                        ['Ana', 'Bruno', 'Carla', 'Diego', 'Eduarda', 'Felipe', 'Gustavo', 'Helena', 'Igor', 'Júlia'][$matricSeq % 10],
                        ['Silva', 'Souza', 'Oliveira', 'Santos', 'Pereira', 'Lima', 'Ferreira', 'Costa', 'Almeida', 'Gomes'][$matricSeq % 10],
                        ['Junior', 'Filho', 'Neto', 'da Costa', 'de Souza', 'do Carmo', 'da Silva', 'Monteiro', 'Faria', 'Furtado'][$matricSeq % 10]
                    );
                    $nome = trim(preg_replace('/\s+/', ' ', $nomeBase));

                    $nomeSlug = Str::slug($nome, '.');
                    $email    = strtolower("{$nomeSlug}.{$matricSeq}@{$schoolSlug}.edu.local");

                    // Matrícula única (numérica)
                    $matricula = str_pad((string) $matricSeq, 8, '0', STR_PAD_LEFT);
                    $matricSeq++;

                    Professor::query()->create([
                        'id_escola'          => $escola->id,
                        'matricula'          => $matricula,
                        'nome'               => $nome,
                        'email'              => $email,
                        'especializacao'     => $esp,
                        'turno'              => $turno,
                        'professor_srm'      => $professorSrm,
                        'profissional_apoio' => $profissionalApoio,
                    ]);
                }
            }

            // ==== Failsafe: garante pelo menos 1 SRM e 1 Apoio por escola ====
            if (! $jaTemSrm || ! $jaTemApoio) {
                $professores = Professor::where('id_escola', $escola->id)->orderBy('id')->get();

                if ($professores->isNotEmpty()) {
                    if (! $jaTemSrm) {
                        $p = $professores->first();
                        $p->update([
                            'professor_srm'      => true,
                            'profissional_apoio' => false,
                        ]);
                        $jaTemSrm = true;
                    }

                    if (! $jaTemApoio && $professores->count() > 1) {
                        $p = $professores->get(1);
                        $p->update([
                            'professor_srm'      => false,
                            'profissional_apoio' => true,
                        ]);
                        $jaTemApoio = true;
                    }
                }
            }
        }
    }
}
