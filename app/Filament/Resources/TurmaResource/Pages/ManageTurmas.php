<?php

namespace App\Filament\Resources\TurmaResource\Pages;

use App\Filament\Resources\SerieResource;
use App\Filament\Resources\TurmaResource;
use App\Models\Serie;
use App\Services\TurmaService;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageTurmas extends ManageRecords
{
    protected static string $resource = TurmaResource::class;

    protected TurmaService $turmaService;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('nova_serie')
                ->label('Nova Série')
                ->icon('heroicon-o-clipboard-document-list')
                ->model(Serie::class)
                ->visible(function () {
                    /** @var \App\Models\User */
                    $user = Auth::user();

                    if ($user->hasPermissionTo('Criar Séries')) {
                        return true;
                    }
                    return false;
                })
                ->modalHeading('Criar Série')
                ->form(
                    fn() => SerieResource::service()
                        ->configurarFormulario($this->makeForm())
                        ->getComponents()
                )
                ->createAnother(false)
                ->color('info')
                ->successNotificationTitle('Série criada!'),

            Actions\CreateAction::make()
                ->label('Nova Turma')
                ->icon('heroicon-o-users')
                ->mutateFormDataUsing(function (array $data): array {
                    /** @var \App\Services\TurmaService $service */
                    $service = app(TurmaService::class);

                    $data = $service->aplicarCodigo($data);
                    $data = $service->forcarVinculoComEscola($data, Auth::user());

                    return $data;
                }),
        ];
    }
}
