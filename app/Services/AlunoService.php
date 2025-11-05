<?php

namespace App\Services;


use App\Models\Escola;
use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Aluno;
use App\Models\Turma;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\CheckboxList;
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
use App\Filament\Resources\AlunoResource;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

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
            Section::make('Dados do Aluno')
                ->collapsible()
                ->schema([
                    Grid::make(12)
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
                                ->maxLength(20)
                                ->columnSpan(3),

                            TextInput::make('nome')
                                ->label('Nome:')
                                ->required()
                                ->minLength(3)
                                ->columnSpan(3)
                                ->maxLength(100)
                                ->rule('regex:/^[\p{L}\p{N}]+(?: [\p{L}\p{N}]+)*$/u')
                                ->validationMessages([
                                    'regex' => 'Use apenas letras, sem caracteres especiais.',
                                ]),

                            Select::make('sexo')
                                ->label('Sexo:')
                                ->columnSpan(3)
                                ->placeholder('Selecione')
                                ->required()
                                ->options([
                                    'Masculino' => 'Masculino',
                                    'Feminino' => 'Feminino',
                                ]),

                            DatePicker::make('data_nascimento')
                                ->columnSpan(3)
                                ->label('Data de Nascimento:')
                                ->required()
                                ->maxDate(Carbon::today()->subYears(1))
                                ->rule(fn() => 'before_or_equal:' . Carbon::today()->subYears(1)->toDateString())
                                ->validationMessages([
                                    'before_or_equal' => 'A criança precisa ter pelo menos 1 anos.',
                                ]),

                            Fieldset::make('Informações da Escola')
                                ->columns(12)
                                ->schema([
                                    Grid::make(12)
                                        ->schema([
                                            Select::make('id_escola')
                                                ->label('Escola')
                                                ->options(fn() => $this->escolaService->opcoesDeEscolasParaUsuario(Auth::user()))
                                                ->searchable()
                                                ->preload()
                                                ->columnSpan(7)
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
                                                ->columnSpan(5)
                                                ->placeholder('Selecione a turma'),
                                        ]),
                                    Grid::make()
                                        ->schema([
                                            Checkbox::make('dificuldade_aprendizagem')
                                                ->columnSpan(1)
                                                ->label('Apresenta dificuldade na aprendizagem?'),

                                            Checkbox::make('frequenta_srm')
                                                ->columnSpan(1)
                                                ->label('Frequenta Sala de Recursos Multifuncionais?'),
                                        ]),
                                ])
                                ->columnSpan(6),


                            Fieldset::make('Profissional de Apoio')
                                ->schema([

                                    Grid::make()
                                        ->columns(1)
                                        ->schema([
                                            Checkbox::make('profissional_apoio')
                                                ->columnSpan(1)
                                                ->label('Tem acompanhamento de Profissional de Apoio?'),
                                            Select::make('id_professor')
                                                ->label('Professor')
                                                ->relationship('professor', 'nome')
                                                ->searchable()
                                                ->preload()
                                                ->required(),
                                        ]),


                                ])
                                ->columnSpan(6),





                        ])
                ]),

            Section::make('Acompanhamento Pedagogico')
                ->collapsible()
                ->schema([
                    Grid::make(12)
                        ->schema([
                            Grid::make()
                                ->schema([
                                    Grid::make(12)
                                        ->schema([
                                            Grid::make(7)
                                                ->columnSpan(12)
                                                ->schema([
                                                    Checkbox::make('ja_foi_retido')
                                                        ->columnSpan(2)
                                                        ->label('Ja foi retido?'),
                                                ]),

                                            Repeater::make('members')
                                                ->columnSpan(12)
                                                ->columns(12)
                                                ->schema([
                                                    Select::make('vezes_retido')
                                                        ->columnSpan(5)
                                                        ->label("Quantas vezes foi retido?")
                                                        ->placeholder('Número vezes')
                                                        ->options([
                                                            '1 vez' => '1 vez',
                                                            '2 vezes' => '2 vezes',
                                                            '3 vezes' => '3 vezes',
                                                            '4 ou mais' => '4 ou mais',
                                                        ]),
                                                    Select::make('role')
                                                        ->options([
                                                            '1 ano' => '1 ano',
                                                            '2 ano' => '2 ano',
                                                            '3 ano' => '3 ano',
                                                        ])
                                                        ->columnSpan(4)
                                                        ->required(),
                                                    CheckboxList::make('motivo_retido')
                                                        ->label('As retenções ocorreram por:')
                                                        ->options([
                                                            'Faltas' => 'Faltas',
                                                            'Aprendizagem' => 'Aprendizagem',
                                                        ])
                                                        ->descriptions([
                                                            'Faltas' => 'Excesso de faltas',
                                                            'Aprendizagem' => 'Dificuldades na aprendizagem',
                                                        ])
                                                        ->columns(2)
                                                        ->columnSpan(7),
                                                ]),

                                        ])
                                        ->columnSpan(6),


                                ]),

                        ]),

                    Fieldset::make('Informações Medicas')
                        ->schema([
                            Select::make('laudo')
                                ->label('Laudo')
                                ->required()
                                ->relationship('laudo', 'nome'),

                            FileUpload::make('anexo')
                                ->label('Anexo')
                                ->helperText('Anexe um pdf com todos os laudos e anexos.')
                                ->openable()
                                ->previewable(false)
                                ->acceptedFileTypes(['application/pdf'])


                        ]),
                ])


        ];
    }

    public function configurarTabela(Table $table, ?User $user): Table
    {
        return $table
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
            TextColumn::make('turma.escola.nome')
                ->label('Escola')
                ->wrap()
                ->sortable()
                ->searchable(),

            TextColumn::make('turma.serie.nome')
                ->label('Série')
                ->wrap()
                ->sortable()
                ->searchable(),

            TextColumn::make('turma.turma')
                ->label('Turma')
                ->wrap()
                ->sortable()
                ->searchable(),

            TextColumn::make('cgm')
                ->label('CGM')
                ->wrap()
                ->sortable()
                ->searchable(),

            TextColumn::make('nome')
                ->label('Nome')
                ->wrap()
                ->sortable()
                ->searchable(),

            TextColumn::make('professor.nome')
                ->label('Profissional de Apoio')
                ->wrap()
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('data_nascimento')
                ->label('Data de Nascimento')
                ->wrap()
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('sexo')
                ->label('Sexo')
                ->wrap()
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->label('Criado em')
                ->since()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label('Atualizado em')
                ->since()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public function filtrosTabela(): array
    {
        return [
            SelectFilter::make('id_escola')
                ->label('Escola')
                ->relationship('turma.escola', 'nome'),
        ];
    }

    private function acoesTabela(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()
        ];
    }

    private function acoesEmMassa(?User $user): array
    {
        return [
            DeleteBulkAction::make(),

            FilamentExportBulkAction::make('exportar_filtrados')
                ->label('Exportar XLSX')
                ->defaultFormat('xlsx')
                ->directDownload(),
        ];
    }

    public function desabilitarSelectTurma(?int $idEscola): bool
    {
        return blank($idEscola);
    }

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
