<?php

namespace Database\Seeders;

use App\Models\Professor;

class ProfessorSeeder extends DatabaseSeeder
{
    public function run(): void
    {
        $professorsList = [
            'Prof. 1',
            'Prof. 2',
            'Prof. 3',
            'Prof. 4',
            'Prof. 5',
        ];

        foreach ($professorsList as $nome) {
            Professor::firstOrCreate(['nome' => $nome]);
        }
    }
}