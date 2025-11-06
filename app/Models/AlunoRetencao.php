<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlunoRetencao extends Model
{
    protected $table = 'aluno_retencoes';

    protected $fillable = [
        'aluno_id',
        'vezes_retido',
        'serie_id',
        'ano_letivo',
    ];

    protected $casts = [
        'motivos' => 'array',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }
}
