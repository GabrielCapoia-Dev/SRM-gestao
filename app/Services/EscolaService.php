<?php

namespace App\Services;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;

class EscolaService
{

    /** Configura a tabela completa (paginações, colunas, filtros, ações, ordenação). */
    public function configurarTabela(Table $table, ?User $user): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->columns($this->colunasTabela())
            ->actions($this->acoesTabela($user))
            ->bulkActions($this->acoesEmMassa($user))
            ->defaultSort('updated_at', 'desc')
            ->striped();
    }

    private function colunasTabela(): array
    {
        return [];
    }

    private function acoesTabela(?User $user): array
    {
        return [];
    }

    private function acoesEmMassa(?User $user): array
    {
        return [];
    }

    // Configura o formulário completo (campos, ações, etc.)
    public function configurarFormulario(Form $form): Form
    {
        return $form->schema($this->schemaFormulario());
    }

    protected function schemaFormulario(): array
    {
        return [];
    }
}
