<?php

namespace App\Services;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Form;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;

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
            Grid::make(12)
                ->schema([


                    TextInput::make('matricula')
                        ->label('Matricula')
                        ->columnSpan(2)
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
                        ->columnSpan(5)
                        ->minLength(3)
                        ->maxLength(100)
                        ->rule('regex:/^[\p{L}\p{N}]+(?: [\p{L}\p{N}]+)*$/u')
                        ->validationMessages([
                            'regex' => 'Use apenas letras, sem caracteres especiais.',
                        ]),
                    TextInput::make('email')
                        ->columnSpan(5)
                        ->label('E-mail')
                        ->unique(ignoreRecord: true)
                        ->email(),

                ]),
            Grid::make(6)
                ->schema([
                    Select::make('id_escola')
                        ->label('Escola')
                        ->relationship('escola', 'nome')
                        ->required()
                        ->columnSpan(2)
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
                    Select::make('especializacao')
                        ->columnSpan(2)
                        ->label('Especializacao')
                        ->required()
                        ->options([
                            'Magisterio' => 'Magisterio',
                            'Licenciatura' => 'Licenciatura',
                            'Bacharelado' => 'Bacharelado',
                            'Pos Graduacao' => 'Pos Graduacao',
                            'Doutorado' => 'Doutorado',
                            'Mestrado' => 'Mestrado',
                        ]),

                    Select::make('turno')
                        ->columnSpan(2)
                        ->required()
                        ->label('Turno')
                        ->options([
                            'Manhã' => 'Manhã',
                            'Tarde' => 'Tarde',
                            'Noite' => 'Noite',
                        ]),
                ]),
            Grid::make(6)
                ->schema([
                    Checkbox::make('professor_srm')
                        ->columnSpan(3)
                        ->label('É um professor da SRM?')
                        ->helperText('Esse é um professor de Sala de Recursos Multifuncionais?')
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, ?bool $state) {
                            if ($state) $set('profissional_apoio', false);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            if ($get('professor_srm') && $get('profissional_apoio')) {
                                $set('profissional_apoio', false);
                            }
                        })
                        ->required(fn(Get $get) => ! (bool) $get('profissional_apoio'))
                        ->rules(['prohibited_if:profissional_apoio,1']),

                    Checkbox::make('profissional_apoio')
                        ->columnSpan(3)
                        ->label('É um profissional de Apoio?')
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, ?bool $state) {
                            if ($state) $set('professor_srm', false);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            if ($get('profissional_apoio') && $get('professor_srm')) {
                                $set('professor_srm', false);
                            }
                        })
                        ->required(fn(Get $get) => ! (bool) $get('professor_srm'))
                        ->rules(['prohibited_if:professor_srm,1']),
                ]),


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
