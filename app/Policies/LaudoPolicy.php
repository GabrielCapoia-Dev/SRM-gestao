<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Laudo;

class LaudoPolicy

{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Listar Laudos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Laudo $model): bool
    {
        return $user->hasPermissionTo('Listar Laudos');

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Laudos');
        ;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Laudo $model): bool
    {
        return $user->hasPermissionTo('Editar Laudos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Laudo $model): bool
    {
        return $user->hasPermissionTo('Excluir Laudos');
    }

    // /**
    //  * Determine whether the user can restore the model.
    //  */
    // public function restore(User $user, Laudo $model): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can permanently delete the model.
    //  */
    // public function forceDelete(User $user, Laudo $model): bool
    // {
    //     return false;
    // }
}

