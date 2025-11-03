<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EscolaResource\Pages;
use App\Models\Escola;
use App\Services\EscolaService as Service;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EscolaResource extends Resource
{
    protected static ?string $model = Escola::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return app(Service::class)->configurarFormulario($form);
    }


    public static function table(Table $table): Table
    {
        return app(Service::class)->configurarTabela($table, Auth::user());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEscolas::route('/'),
        ];
    }
}
