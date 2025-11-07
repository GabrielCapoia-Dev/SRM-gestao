<?php

namespace App\Services;


use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use App\Models\Aluno;
use App\Models\Turma;
use App\Models\User;
use App\Models\Serie;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Set;

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
                                    Grid::make(3)
                                        ->schema([
                                            Checkbox::make('dificuldade_aprendizagem')
                                                ->columnSpan(3)
                                                ->label('Apresenta dificuldade na aprendizagem?'),

                                            Checkbox::make('frequenta_srm')
                                                ->columnSpan(3)
                                                ->label('Frequenta Sala de Recursos Multifuncionais?'),

                                            Checkbox::make('encaminhado_para_SME')
                                                ->columnSpan(3)
                                                ->label('Encaminhado(a) para a Equipe Multiprofissional da SME?'),
                                        ]),
                                ])
                                ->columnSpan(6),


                            Fieldset::make('Profissional de Apoio')
                                ->schema([
                                    Grid::make()->columns(1)->schema([
                                        Checkbox::make('profissional_apoio')
                                            ->label('Tem acompanhamento de Profissional de Apoio?')
                                            ->reactive()
                                            ->afterStateUpdated(function (bool $state, Set $set) {
                                                if (! $state) {
                                                    $set('id_professor', null);
                                                }
                                            }),

                                        Select::make('id_professor')
                                            ->label('Profissional de Apoio')
                                            ->relationship('professor', 'nome')
                                            ->searchable()
                                            ->preload()
                                            ->hidden(fn(Get $get) => ! $get('profissional_apoio'))
                                            ->dehydrated(fn(Get $get) => (bool) $get('profissional_apoio'))
                                            ->required(fn(Get $get) => (bool) $get('profissional_apoio')),
                                    ]),
                                ])
                                ->columnSpan(6),
                        ])
                ]),

            Section::make('Retenções')
                ->collapsible()
                ->schema([
                    Grid::make(12)
                        ->schema([
                            Grid::make()
                                ->schema([
                                    Fieldset::make()
                                        ->columns(12)
                                        ->schema([
                                            Grid::make(12)
                                                ->columnSpan(12)
                                                ->schema([
                                                    Radio::make('ja_foi_retido')
                                                        ->label('Já foi retido?')
                                                        ->columns(2)
                                                        ->columnSpan(2)
                                                        ->options([
                                                            'Sim' => 'Sim',
                                                            'Nao' => 'Não',
                                                        ])
                                                        ->required()
                                                        ->reactive()
                                                        ->afterStateUpdated(function ($state, Set $set) {
                                                            if ($state !== 'Sim') {
                                                                $set('retidos', null);
                                                            }
                                                        }),

                                                ]),

                                            Repeater::make('retidos')
                                                ->label('Retenções')
                                                ->relationship('retencoes')
                                                ->defaultItems(1)
                                                ->collapsible()
                                                ->reorderable(false)
                                                ->columns(12)
                                                ->columnSpan(12)
                                                ->hidden(fn(Get $get) => $get('ja_foi_retido') !== 'Sim')
                                                ->dehydrated(fn(Get $get) => $get('ja_foi_retido') === 'Sim')
                                                ->required(fn(Get $get) => $get('ja_foi_retido') === 'Sim')
                                                ->schema([
                                                    Select::make('vezes_retido')
                                                        ->columnSpan(3)
                                                        ->required()
                                                        ->label('Quantas vezes foi retido?')
                                                        ->options([
                                                            '1 vez' => '1 vez',
                                                            '2 vezes' => '2 vezes',
                                                            '3 vezes' => '3 vezes',
                                                            '4 ou mais' => '4 ou mais',
                                                        ]),

                                                    Select::make('id_serie') // ajuste aqui pro nome real da coluna FK
                                                        ->label('Série em que foi retido')
                                                        ->options(fn() => Serie::all()->pluck('nome', 'id'))
                                                        ->columnSpan(3)
                                                        ->required(),

                                                    Select::make('ano_retido')
                                                        ->label('Ano retido')
                                                        ->required()
                                                        ->multiple()
                                                        ->columnSpan(3)
                                                        ->options(function () {
                                                            $anoAtual = now()->year;
                                                            $anos = [];

                                                            for ($i = 0; $i < 10; $i++) {
                                                                $ano = $anoAtual - $i;
                                                                $anos[$ano] = $ano;
                                                            }

                                                            return $anos;
                                                        }),

                                                    CheckboxList::make('motivo_retido')
                                                        ->label('As retenções ocorreram por:')
                                                        ->required()
                                                        ->options([
                                                            'Faltas' => 'Faltas',
                                                            'Aprendizagem' => 'Aprendizagem',
                                                        ])
                                                        ->descriptions([
                                                            'Faltas' => 'Excesso de faltas',
                                                            'Aprendizagem' => 'Dificuldades na aprendizagem',
                                                        ])
                                                        ->columns(2)
                                                        ->columnSpan(3),
                                                ])
                                        ])
                                        ->columnSpan(6),


                                ]),

                        ]),

                ]),
            Section::make('Informações CAEI')
                ->collapsible()
                ->schema([
                    Grid::make(12)
                        ->schema([
                            Fieldset::make()
                                ->columns(12)
                                ->schema([
                                    Radio::make('encaminhado_para_caei')
                                        ->label('Encaminhado(a) para a Equipe Multiprofissional da CAEI?')
                                        ->columns(2)
                                        ->columnSpan(5)
                                        ->options([
                                            'Sim' => 'Sim',
                                            'Nao' => 'Não',
                                        ])
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if (! in_array('Sim', (array) $state, true)) {
                                                $set('encaminhado_para_especialista', null);
                                                $set('fonoaudiologo', null);
                                                $set('psicologo', null);
                                                $set('psicopedagogo', null);
                                                $set('avanco_caei', null);
                                            }
                                        })
                                        ->required(),

                                    Fieldset::make()
                                        ->hidden(fn(Get $get) => ! in_array('Sim', (array) $get('encaminhado_para_caei'), true))
                                        ->columns(12)
                                        ->schema([
                                            Radio::make('encaminhado_para_especialista')
                                                ->label('Encaminhado(a) para um especialista?')
                                                ->columns(2)
                                                ->columnSpan(7)
                                                ->options([
                                                    'Sim' => 'Sim',
                                                    'Nao' => 'Não',
                                                ])
                                                ->reactive()
                                                ->hidden(fn(Get $get) => ! in_array('Sim', (array) $get('encaminhado_para_caei'), true))
                                                ->dehydrated(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_caei'), true))
                                                ->afterStateUpdated(function ($state, Set $set) {
                                                    if (! in_array('Sim', (array) $state, true)) {
                                                        $set('fonoaudiologo', null);
                                                        $set('psicologo', null);
                                                        $set('psicopedagogo', null);
                                                    }
                                                }),

                                            Grid::make()
                                                ->hidden(fn(Get $get) => ! in_array('Sim', (array) $get('encaminhado_para_especialista'), true))
                                                ->columnSpan(6)
                                                ->schema([
                                                    Radio::make('status_fonoaudiologo')
                                                        ->label('Fonoaudiólogo')
                                                        ->columnSpan(6)
                                                        ->columns(3)
                                                        ->options(['Sim' => 'Sim', 'Não' => 'Não', 'Lista de Espera' => 'Lista de Espera'])
                                                        ->dehydrated(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_especialista'), true))
                                                        ->required(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_especialista'), true)),

                                                    Radio::make('status_psicologo')
                                                        ->label('Psicólogo')
                                                        ->columnSpan(6)
                                                        ->columns(3)
                                                        ->options(['Sim' => 'Sim', 'Não' => 'Não', 'Lista de Espera' => 'Lista de Espera'])
                                                        ->dehydrated(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_especialista'), true))
                                                        ->required(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_especialista'), true)),

                                                    Radio::make('status_psicopedagogo')
                                                        ->label('Psicopedagogo')
                                                        ->columnSpan(6)
                                                        ->columns(3)
                                                        ->options(['Sim' => 'Sim', 'Não' => 'Não', 'Lista de Espera' => 'Lista de Espera'])
                                                        ->dehydrated(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_especialista'), true))
                                                        ->required(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_especialista'), true)),
                                                ]),
                                        ]),
                                    Fieldset::make()
                                        ->hidden(fn(Get $get) => ! in_array('Sim', (array) $get('encaminhado_para_caei'), true))
                                        ->columns(12)
                                        ->schema([


                                            Radio::make('avanco_caei')
                                                ->label('Após o atendimento no CAEI, o(a) estudante apresentou avanços na aprendizagem?')
                                                ->columns(3)
                                                ->columnSpan(7)
                                                ->options([
                                                    'Sim' => 'Sim',
                                                    'Nao' => 'Não',
                                                    'Nao está em atendimento' => 'Não está em atendimento',
                                                ])
                                                ->hidden(fn(Get $get) => ! in_array('Sim', (array) $get('encaminhado_para_caei'), true))
                                                ->dehydrated(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_caei'), true))
                                                ->required(fn(Get $get) => in_array('Sim', (array) $get('encaminhado_para_caei'), true))
                                        ]),
                                ]),

                        ]),
                ]),
            Section::make('Informações Medicas')
                ->collapsible()
                ->schema([
                    Grid::make(12)
                        ->schema([
                            Fieldset::make()
                                ->columns(12)
                                ->schema([
                                    Select::make('laudos')
                                        ->label('Laudos')
                                        ->multiple()
                                        ->relationship('laudos', 'nome')
                                        ->preload()
                                        ->searchable()
                                        ->columnSpan(4),

                                    FileUpload::make('anexo_laudo_path')
                                        ->label('Anexo')
                                        ->helperText('Anexe um pdf com todos os laudos e anexos.')
                                        ->disk('public')
                                        ->directory('laudos')
                                        ->openable()
                                        ->previewable(false)
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->columnSpan(8),
                                ]),
                        ]),
                ]),


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

            ToggleColumn::make('dificuldade_aprendizagem')
                ->label('Dificuldade de Aprendizagem')
                ->sortable()
                ->disabled()
                ->visible()
                ->inline(false)
                ->onColor('success')
                ->offColor('danger')
                ->onIcon('heroicon-s-check')
                ->offIcon('heroicon-s-x-mark')
                ->toggleable(isToggledHiddenByDefault: true),

            ToggleColumn::make('frequenta_srm')
                ->label('Frequenta SRM')
                ->sortable()
                ->disabled()
                ->visible()
                ->inline(false)
                ->onColor('success')
                ->offColor('danger')
                ->onIcon('heroicon-s-check')
                ->offIcon('heroicon-s-x-mark')
                ->toggleable(isToggledHiddenByDefault: true),

            ToggleColumn::make('encaminhado_para_sme')
                ->label('Encaminhado para SME')
                ->sortable()
                ->disabled()
                ->visible()
                ->inline(false)
                ->onColor('success')
                ->offColor('danger')
                ->onIcon('heroicon-s-check')
                ->offIcon('heroicon-s-x-mark')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('data_nascimento')
                ->label('Data de Nascimento')
                ->wrap()
                ->date('d/m/Y')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('data_nascimento')
                ->label('Idade')
                ->formatStateUsing(function ($state) {
                    if (empty($state)) {
                        return '-';
                    }
                    $idade = Carbon::parse($state)->age;

                    return $idade . ' ' . ($idade == 1 ? 'ano' : 'anos');
                })
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('sexo')
                ->label('Sexo')
                ->wrap()
                ->sortable()
                ->searchable()
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Masculino' => 'primary',
                    'Feminino' => "danger",
                })
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('laudos.nome')
                ->label('Laudos')
                ->formatStateUsing(
                    fn($state, $record) =>
                    $record->laudos->pluck('nome')->implode(', ')
                )
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('anexo_laudo_path')
                ->label('Anexo')
                ->formatStateUsing(fn($state) => $state ? 'Baixar laudo' : '-')
                ->url(fn($state) => $state ? asset('storage/' . $state) : null)
                ->openUrlInNewTab()
                ->icon(fn($state) => $state ? 'heroicon-o-arrow-down-tray' : null),


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
