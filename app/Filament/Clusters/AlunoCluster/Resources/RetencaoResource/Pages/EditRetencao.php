<?php

namespace App\Filament\Clusters\AlunoCluster\Resources\RetencaoResource\Pages;

use App\Filament\Clusters\AlunoCluster\Resources\RetencaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRetencao extends EditRecord
{
    protected static string $resource = RetencaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
