<?php

namespace App\Filament\Clusters\AlunoCluster\Resources;

use App\Filament\Clusters\AlunoCluster;
use App\Filament\Clusters\AlunoCluster\Resources\CaeiResource\Pages;
use App\Models\Aluno;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;

class CaeiResource extends Resource
{
    protected static ?string $model = Aluno::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'CAEI';
    protected static ?string $pluralModelLabel = 'CAEI';
    protected static ?string $modelLabel = 'CAEI';
    protected static ?string $slug = 'caei';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $cluster = AlunoCluster::class;

    public static function form(Form $form): Form
    {
        // Se o CAEI não edita diretamente por aqui, deixa sem schema.
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('turma.escola.nome')
                    ->label('Escola')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('turma.serie.nome')
                    ->label('Série')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('turma.turma')
                    ->label('Turma')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cgm')
                    ->label('CGM')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nome')
                    ->label('Aluno')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('encaminhado_para_caei')
                    ->label('Encaminhado para CAEI')
                    ->badge()
                    ->color(fn (?string $state) => $state === 'Sim' ? 'success' : 'secondary'),

                TextColumn::make('encaminhado_para_especialista')
                    ->label('Encaminhado para Especialista')
                    ->badge()
                    ->color(fn (?string $state) => $state === 'Sim' ? 'success' : 'secondary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status_fonoaudiologo')
                    ->label('Fonoaudiólogo')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Sim' => 'success',
                        'Lista de Espera' => 'warning',
                        'Nao' => 'danger',
                        default => 'secondary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status_psicologo')
                    ->label('Psicólogo')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Sim' => 'success',
                        'Lista de Espera' => 'warning',
                        'Nao' => 'danger',
                        default => 'secondary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status_psicopedagogo')
                    ->label('Psicopedagogo')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Sim' => 'success',
                        'Lista de Espera' => 'warning',
                        'Nao' => 'danger',
                        default => 'secondary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('avanco_caei')
                    ->label('Avanço CAEI')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Sim' => 'success',
                        'Nao' => 'danger',
                        'Nao está em atendimento' => 'secondary',
                        default => 'secondary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Exemplo: mostrar só realmente encaminhados
                Tables\Filters\SelectFilter::make('encaminhado_para_caei')
                    ->label('Encaminhado CAEI')
                    ->options([
                        'Sim' => 'Sim',
                        'Nao' => 'Não',
                    ]),
            ])
            ->actions([
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Mostra só alunos que têm relação com CAEI (encaminhados ou com status preenchido)
        return parent::getEloquentQuery()
            ->where(function ($q) {
                $q->where('encaminhado_para_caei', 'Sim')
                  ->orWhereNotNull('encaminhado_para_especialista')
                  ->orWhereNotNull('status_fonoaudiologo')
                  ->orWhereNotNull('status_psicologo')
                  ->orWhereNotNull('status_psicopedagogo')
                  ->orWhereNotNull('avanco_caei');
            })
            ->with(['turma.escola', 'turma.serie']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCaeis::route('/'),
            // Se não quiser criar/editar por aqui, pode remover:
            // 'create' => Pages\CreateCaei::route('/create'),
            // 'edit' => Pages\EditCaei::route('/{record}/edit'),
        ];
    }
}
