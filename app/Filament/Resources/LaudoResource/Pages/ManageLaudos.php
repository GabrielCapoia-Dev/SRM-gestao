<?php

namespace App\Filament\Resources\LaudoResource\Pages;

use App\Filament\Resources\LaudoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLaudos extends ManageRecords
{
    protected static string $resource = LaudoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
