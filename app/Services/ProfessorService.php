<?php

namespace App\Services;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;

class ProfessorService
{
    public function __construct(
        protected UserService $userService,
        protected AlunoService $alunoService
    ) {}

    public function configurarFormulario(Form $form, ?User $user): Form
    {
        return $form
            ->schema($this->schemaFormulario());
    }

    public function schemaFormulario(): array
    {
        return [
            Select::make('id_escola')
                ->label('Escola')
                ->relationship('escola', 'nome')
                ->required()
                ->preload()
                ->searchable()
                ->default(fn() => Auth::user()?->id_escola)
                ->dehydrated(true)
                ->disabled(function () {
                    $user = Auth::user();

                    if (! $user) {
                        return false;
                    }
                    if (filled($user->id_escola)) {
                        return true;
                    }
                    return false;
                }),

            TextInput::make('matricula')
                ->label('Matricula')
                ->minLength(3)
                ->rules(['regex:/^\d+$/'])
                ->validationMessages([
                    'regex' => 'Apenas numeros',
                    'min' => 'O CGM deve ter no mínimo 3 dígitos.',
                ])
                ->unique(ignoreRecord: true)
                ->maxLength(20),
            TextInput::make('nome')
                ->label('Nome:')
                ->required()
                ->minLength(3)
                ->maxLength(100)
                ->rule('regex:/^[\p{L}\p{N}]+(?: [\p{L}\p{N}]+)*$/u')
                ->validationMessages([
                    'regex' => 'Use apenas letras, sem caracteres especiais.',
                ]),
            TextInput::make('email')
                ->label('E-mail')
                ->unique(ignoreRecord: true)
                ->email(),
        ];
    }


    public function configurarTabela(Table $table, ?User $user): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->columns($this->colunasTabela())
            ->actions($this->acoesTabela($user))
            ->bulkActions($this->acoesEmMassa($user))
            ->filters($this->filtrosTabela())
            ->defaultSort('updated_at', 'desc')
            ->striped();
    }

    public function colunasTabela(): array
    {
        return [
            TextColumn::make('escola.nome')
                ->label('Escola')
                ->sortable()
                ->searchable(),

            TextColumn::make('matricula')
                ->label('Matricula')
                ->sortable()
                ->searchable(),
            TextColumn::make('nome')
                ->label('Nome do professor')
                ->sortable()
                ->searchable(),
            TextColumn::make('email')
                ->label('E-mail')
                ->sortable()
                ->searchable(),
            TextColumn::make('created_at')
                ->label('Criado')
                ->since()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label('Atualizado')
                ->since()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public function acoesTabela(?User $user): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()
                ->before(function (User $record, DeleteAction $action) use ($user) {
                    if (! $this->userService->podeDeletar($user, $record)) {
                        $action->failure();
                        $action->halt();
                    }
                })
                ->visible(
                    fn() =>
                    $this->userService->ehAdmin(Auth::user())
                ),
        ];
    }

    public function acoesEmMassa(?User $user): array
    {
        return [
            DeleteBulkAction::make()
                ->before(function ($records, $action) use ($user) {
                    if (! $this->userService->podeDeletarEmLote($user, $records)) {
                        $action->halt();
                    }
                })
                ->visible(fn() => $this->userService->ehAdmin(Auth::user())),
        ];
    }

    public function filtrosTabela(): array
    {
        return [
            //
        ];
    }

    public function forcarVinculoComEscola(array $data, ?User $auth): array
    {
        if ($auth && filled($auth->id_escola)) {
            $data['id_escola'] = $auth->id_escola;
        }

        return $data;
    }
}
