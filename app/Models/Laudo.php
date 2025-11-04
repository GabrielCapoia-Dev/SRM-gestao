<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laudo extends Model
{
    protected $table = 'laudos';

    protected $fillable = [
        'codigo',
        'laudo',
    ];

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }
}
