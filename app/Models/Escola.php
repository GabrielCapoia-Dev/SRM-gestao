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
}
