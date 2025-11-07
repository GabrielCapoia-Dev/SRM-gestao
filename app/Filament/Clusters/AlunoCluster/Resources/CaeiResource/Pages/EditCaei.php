<?php

namespace App\Filament\Clusters\AlunoCluster\Resources\CaeiResource\Pages;

use App\Filament\Clusters\AlunoCluster\Resources\CaeiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaei extends EditRecord
{
    protected static string $resource = CaeiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
