<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Turma;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AlunoService
{
    public function __construct(
        protected UserService $userService,
        protected EscolaService $escolaService
    ) {}

    public function configurarFormulario(Form $form, ?User $user): Form
    {
        return $form
            ->schema($this->schemaFormulario());
    }

    public function schemaFormulario(): array
    {
        return [
            Grid::make(6)
                ->schema([
                    Fieldset::make('Dados do Aluno')
                        ->schema([
                            TextInput::make('cgm')
                                ->label('CGM:')
                                ->required()
                                ->minLength(6)
                                ->rules(['regex:/^\d+$/'])
                                ->validationMessages([
                                    'regex' => 'Apenas numeros',
                                    'min' => 'O CGM deve ter no mínimo 6 dígitos.',
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

                            Select::make('sexo')
                                ->label('Sexo:')
                                ->placeholder('Selecione')
                                ->required()
                                ->options([
                                    'Masculino' => 'Masculino',
                                    'Feminino' => 'Feminino',
                                ]),

                            DatePicker::make('data_nascimento')
                                ->label('Data de Nascimento:')
                                ->required()
                                ->maxDate(Carbon::today()->subYears(1))
                                ->rule(fn() => 'before_or_equal:' . Carbon::today()->subYears(1)->toDateString())
                                ->validationMessages([
                                    'before_or_equal' => 'A criança precisa ter pelo menos 1 anos.',
                                ]),

                            Fieldset::make('Informações da Escola')
                                ->schema([
                                    Select::make('id_escola')
                                        ->label('Escola')
                                        ->options(fn() => $this->escolaService->opcoesDeEscolasParaUsuario(Auth::user()))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->default(fn($record) => $this->escolaInicialParaForm($record, Auth::user()))
                                        ->afterStateHydrated(function ($state, callable $set, $record) {
                                            $set('id_escola', $this->escolaInicialParaForm($record, Auth::user()));
                                        })
                                        ->dehydrated(false)
                                        ->disabled(fn() => $this->deveTravarCampoEscola(Auth::user()))
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, callable $set) => $set('id_turma', null)),

                                    Select::make('id_turma')
                                        ->label('Turma')
                                        ->options(function (Get $get, $record) {
                                            $idEscola = $get('id_escola') ?? $record?->turma?->id_escola;
                                            return $this->opcoesDeTurmasParaEscola($idEscola);
                                        })
                                        ->searchable()
                                        ->required()
                                        ->disabled(function (Get $get, $record) {
                                            $idEscola = $get('id_escola') ?? $record?->turma?->id_escola;
                                            return $this->desabilitarSelectTurma($idEscola);
                                        })
                                        ->reactive()
                                        ->placeholder('Selecione a escola primeiro'),
                                ]),


                            Section::make('Profissional de Apoio')
                                ->schema([
                                    Select::make('id_professor')
                                        ->label('Profissional de Apoio')
                                        ->relationship('professor', 'nome')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                ]),
                        ])
                        ->columnSpan(1),

                    Fieldset::make('Laudo')
                        ->schema([
                            TextInput::make('laudo')
                                ->label('Laudo:')
                                ->required()
                                ->minLength(3)
                                ->maxLength(100)
                                ->rule('regex:/^[\p{L}\p{N}]+(?: [\p{L}\p{N}]+)*$/u')
                                ->validationMessages([
                                    'regex' => 'Use apenas letras, sem caracteres especiais.',
                                ])
                        ])
                        ->columnSpan(1),
                ])
                ->columns(2),

        ];
    }

    public function desabilitarSelectTurma(?int $idEscola): bool
    {
        return blank($idEscola);
    }

    /** Opções de turmas filtradas pela escola escolhida. */
    public function opcoesDeTurmasParaEscola(?int $idEscola): array
    {
        if (! $idEscola) {
            return [];
        }

        return Turma::with('serie')
            ->where('id_escola', $idEscola)
            ->get()
            ->filter(fn($t) => $t->serie) // garante série carregada
            ->mapWithKeys(fn($turma) => [
                $turma->id => "{$turma->serie->nome} - {$turma->turma}",
            ])
            ->toArray();
    }

    public function escolaInicialParaForm(?Aluno $record, ?User $user): ?int
    {
        return $record?->turma?->id_escola ?? ($user?->id_escola ?? null);
    }

    public function deveTravarCampoEscola(?User $user): bool
    {
        return ! app(UserService::class)->ehAdmin($user) && filled($user?->id_escola);
    }
}
