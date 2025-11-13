<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    //

    protected $table = 'professores';

    protected $fillable = [
        'id_escola',
        'matricula',
        'nome',
        'email',
        'especializacao',
        'turno',
        'professor_srm',
        'profissional_apoio',
        'especializacao_educacao_especial'
    ];

    public function alunos()
    {
        return $this->hasMany(Aluno::class);
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class, 'id_escola');
    }
}
