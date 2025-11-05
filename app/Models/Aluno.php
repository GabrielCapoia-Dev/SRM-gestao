<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    protected $table = 'alunos';

    protected $fillable = [
        'id_professor',
        'id_turma',
        'id_laudo',
        'cgm',
        'nome',
        'sexo',
        'data_nascimento',
    ];

    public function turma()
    {
        return $this->belongsTo(Turma::class, 'id_turma');
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class, 'id_professor');
    }

    public function laudo()
    {
        return $this->belongsTo(Laudo::class, 'id_laudo');
    }
}
