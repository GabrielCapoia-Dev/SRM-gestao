<?php

namespace App\Filament\Clusters\AlunoCluster\Resources\AlunoResource\Pages;

use App\Filament\Clusters\AlunoCluster\Resources\AlunoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlunos extends ListRecords
{
    protected static string $resource = AlunoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Cadastrar Aluno')
                ->color('primary')
                ->icon('heroicon-o-academic-cap'),
        ];
    }
}
