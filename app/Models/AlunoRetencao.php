<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlunoRetencao extends Model
{
    protected $table = 'aluno_retencoes';

    protected $fillable = [
        'id_aluno',
        'vezes_retido',
        'id_serie',
        'ano_retido',
        'motivo_retido',
    ];

    protected $casts = [
        'motivo_retido' => 'array',
        'ano_retido' => 'array',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class, 'id_aluno');
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class, 'id_serie');
    }
}
