<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    //

    protected $table = 'professores';

    protected $fillable = [
        'matricula',
        'nome',
        'email',
    ];

    public function alunos()
    {
        return $this->hasMany(Aluno::class);
    }


}
