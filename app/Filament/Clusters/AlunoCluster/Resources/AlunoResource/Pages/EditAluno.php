<?php

namespace App\Filament\Clusters\AlunoCluster\Resources\AlunoResource\Pages;

use App\Filament\Clusters\AlunoCluster\Resources\AlunoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAluno extends EditRecord
{
    protected static string $resource = AlunoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
