<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Turma;
use App\Models\Rota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class AlunoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // 15 primeiros nomes (mistos)
        $primeiros = [
            'Maria',
            'Ana',
            'João',
            'Gabriel',
            'Pedro',
            'Lucas',
            'Luiza',
            'Julia',
            'Miguel',
            'Guilherme',
            'Mariana',
            'Matheus',
            'Beatriz',
            'Rafael',
            'Felipe',
        ];

        // 15 nomes do meio (bem comuns no BR)
        $meios = [
            'Aparecida',
            'Clara',
            'Eduarda',
            'Fernanda',
            'Sofia',
            'Carolina',
            'Vitória',
            'Cristina',
            'Letícia',
            'Bianca',
            'Augusto',
            'Henrique',
            'Eduardo',
            'André',
            'César',
        ];

        // 15 sobrenomes
        $sobrenomes = [
            'Silva',
            'Santos',
            'Oliveira',
            'Souza',
            'Rodrigues',
            'Ferreira',
            'Alves',
            'Pereira',
            'Lima',
            'Gomes',
            'Ribeiro',
            'Carvalho',
            'Almeida',
            'Costa',
            'Martins',
        ];

    }
}