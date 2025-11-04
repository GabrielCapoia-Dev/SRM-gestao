<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    //
    protected $table = 'series';

    protected $fillable = ['nome', 'status'];

    // public function turmas()
    // {
    //     return $this->hasMany(Turma::class);
    // }
}
