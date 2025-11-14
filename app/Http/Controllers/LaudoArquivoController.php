<?php

namespace App\Http\Controllers;

use App\Models\AlunoLaudo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class LaudoArquivoController extends Controller
{


    protected function laudosDisk(): FilesystemAdapter
    {
        return Storage::disk('laudos');
    }


    public function show(AlunoLaudo $alunoLaudo)
    {
        // policy: view
        $this->authorize('view', $alunoLaudo);
        $disk = $this->laudosDisk();
        $path = $alunoLaudo->anexo_laudo_path;

        abort_unless($disk->exists($path), 404);

        return $this->laudosDisk()->response($path);
    }

    public function download(AlunoLaudo $alunoLaudo)
    {
        // policy: download
        $this->authorize('download', $alunoLaudo);
        $disk = $this->laudosDisk();
        $path = $alunoLaudo->anexo_laudo_path;

        abort_unless($disk->exists($path), 404);

        $filename = $this->makeFilename($alunoLaudo);

        return $this->laudosDisk()->download($path, $filename);
    }

    protected function makeFilename(AlunoLaudo $alunoLaudo): string
    {
        $aluno = $alunoLaudo->aluno;
        $laudo = $alunoLaudo->laudo;

        $cgm  = $aluno->cgm;
        $nome = str($laudo->nome)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-');

        return "{$cgm}-{$nome}.pdf";
    }
}
