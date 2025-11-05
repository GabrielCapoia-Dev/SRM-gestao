<?php

namespace Database\Seeders;

use App\Models\Escola;
use App\Models\Serie;
use App\Models\Turma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        // Cache de séries por nome => id
        $seriesMap = Serie::pluck('id', 'nome')->map(fn ($id) => (int) $id)->all();

        // Conjuntos
        $anosEscola   = ['1º Ano', '2º Ano', '3º Ano', '4º Ano', '5º Ano'];
        $anosCmeiCei  = ['Infantil 4', 'Infantil 5'];

        $letrasEscola  = ['A', 'B', 'C'];
        $letrasCmeiCei = ['A', 'B', 'C', 'D', 'E'];

        // Percorre todas as escolas já criadas
        Escola::query()->orderBy('id')->chunkById(200, function ($escolas) use (
            $seriesMap, $anosEscola, $anosCmeiCei, $letrasEscola, $letrasCmeiCei
        ) {
            foreach ($escolas as $escola) {
                $isCmeiOuCei = $this->ehCmeiOuCei($escola->nome, $escola->codigo);

                $seriesAlvo = $isCmeiOuCei ? $anosCmeiCei : $anosEscola;
                $letras     = $isCmeiOuCei ? $letrasCmeiCei : $letrasEscola;

                foreach ($seriesAlvo as $serieNome) {
                    $serieId = $seriesMap[$serieNome] ?? null;

                    // Se a série não existe, cria (garantia extra)
                    if (!$serieId) {
                        $serie = Serie::updateOrCreate(
                            ['nome' => $serieNome],
                            ['codigo' => $this->codigoSerie($serieNome)]
                        );
                        $serieId = $seriesMap[$serieNome] = (int) $serie->id;
                    }

                    foreach ($letras as $letra) {
                        $letra = Str::upper($letra);
                        $codigo = 'TR' . $letra; // TRA, TRB, ...

                        Turma::updateOrCreate(
                            [
                                'id_escola' => $escola->id,
                                'id_serie'  => $serieId,
                                'turno'     => 'Manhã',
                                'turma'     => $letra,
                            ],
                            [
                                'codigo'    => $codigo,
                            ]
                        );
                    }
                }
            }
        });
    }

    private function ehCmeiOuCei(string $nome, ?string $codigo): bool
    {
        // Se já tiver código começando com C, trate como CMEI/CEI
        if ($codigo && Str::startsWith(Str::upper($codigo), 'C')) {
            return true;
        }

        $n = Str::upper(Str::ascii(trim($nome)));
        return Str::startsWith($n, ['CMEI', 'CEI', 'CMEI -', 'CEI -']);
    }

    private function codigoSerie(string $nome): ?string
    {
        // SI4 / SI5
        if (preg_match('/^Infantil\s*(4|5)$/iu', $nome, $m)) {
            return 'SI' . $m[1];
        }
        // S1A..S5A
        if (preg_match('/^([1-5])º\s*Ano$/iu', $nome, $m)) {
            return 'S' . $m[1] . 'A';
        }
        return null;
    }
}
