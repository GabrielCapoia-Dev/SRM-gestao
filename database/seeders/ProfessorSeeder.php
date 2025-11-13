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

            foreach ($turnos as $turno) {
                // Para ESTE turno na escola, escolhe 1 índice que será o SRM
                $idxSrm = random_int(0, count($especializacoes) - 1);

                foreach ($especializacoes as $idx => $esp) {

                    // === Regra: 1 professor SRM por turno ===
                    $professorSrm      = ($idx === $idxSrm);
                    $profissionalApoio = ! $professorSrm && (bool) random_int(0, 1);

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
                        'especializacao_educacao_especial' => (bool) random_int(0, 1),
                    ]);
                }
            }
        }
    }
}
