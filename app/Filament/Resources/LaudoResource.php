<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaudoResource\Pages;
use App\Filament\Resources\LaudoResource\RelationManagers;
use App\Models\Laudo;
use App\Services\LaudoService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class LaudoResource extends Resource
{
    protected static ?string $model = Laudo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function laudoService(): LaudoService
    {
        return app(LaudoService::class);
    }

    public static function form(Form $form): Form
    {
        return static::laudoService()->configurarFormulario($form);
    }

    public static function table(Table $table): Table
    {
        return static::laudoService()->configurarTabela($table, Auth::user());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLaudos::route('/'),
        ];
    }
}
