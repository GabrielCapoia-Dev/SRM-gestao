<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escola extends Model
{
    protected $table = 'escolas';

    protected $fillable = [
        'codigo',
        'nome',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_escola');
    }

    public function turmas()
    {
        return $this->hasMany(Turma::class, 'id_escola');
    }

    public function professores()
    {
        return $this->hasMany(Professor::class, 'id_escola');
    }
}
