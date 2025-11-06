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
        'dificuldade_aprendizagem',
        'frequenta_srm',
        'encaminhado_para_sme',
        'encaminhado_para_caei',
        'encaminhado_para_especialista',
        'status_fonoaudiologo',
        'status_psicologo',
        'ja_foi_retido',
        'status_psicopedagogo',
        'avanco_caei',
        'anexo_laudo_path',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'dificuldade_aprendizagem' => 'bool',
        'frequenta_srm' => 'bool',
        'ja_foi_retido' => 'bool',
        'encaminhado_para_sme' => 'bool',
        'encaminhado_para_caei' => 'bool',
        'encaminhado_para_especialista' => 'bool',
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

    public function retencoes()
    {
        return $this->hasMany(AlunoRetencao::class);
    }
}
