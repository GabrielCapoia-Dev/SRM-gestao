<?php

namespace Database\Seeders;

use App\Models\DominioEmail;
use App\Models\Serie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa cache das permissões do Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Lista de permissões que serão atribuídas à role Admin
        $permissionsList = [
            'Listar Usuários',
            'Criar Usuários',
            'Editar Usuários',
            'Excluir Usuários',
            'Listar Níveis de Acesso',
            'Criar Níveis de Acesso',
            'Editar Níveis de Acesso',
            'Excluir Níveis de Acesso',
            'Listar Permissões de Execução',
            'Criar Permissões de Execução',
            'Editar Permissões de Execução',
            'Excluir Permissões de Execução',
            'Listar Dominios de Email',
            'Criar Dominios de Email',
            'Editar Dominios de Email',
            'Excluir Dominios de Email',
            'Listar Séries',
            'Criar Séries',
            'Editar Séries',
            'Excluir Séries',
            'Listar Escolas',
            'Criar Escolas',
            'Editar Escolas',
            'Excluir Escolas',
            'Listar Turmas',
            'Criar Turmas',
            'Editar Turmas',
            'Excluir Turmas',
            'Listar Alunos',
            'Criar Alunos',
            'Editar Alunos',
            'Excluir Alunos',
        ];

        $permissionsCoordenador = [
            'Listar Usuários',
            'Criar Usuários',
            'Editar Usuários',
            'Excluir Usuários',
            'Listar Séries',
            'Criar Séries',
            'Editar Séries',
            'Excluir Séries',
            'Listar Turmas',
            'Criar Turmas',
            'Editar Turmas',
            'Excluir Turmas',
            'Listar Alunos',
            'Criar Alunos',
            'Editar Alunos',
            'Excluir Alunos',
        ];

        $password = "Senha@123";

        // Criação das permissões
        foreach ($permissionsList as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Criação da rule Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $coordenadorRole = Role::firstOrCreate(['name' => 'Coordenador']);

        // Atribui todas as permissões à role Admin
        $adminRole->syncPermissions($permissionsList);
        $coordenadorRole->syncPermissions($permissionsCoordenador);


        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'email_approved' => true
            ]
        );
        $coordenadorUser = User::firstOrCreate(
            ['email' => 'coordenador@coordenador.com'],
            [
                'name' => 'Coordenador',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'email_approved' => true
            ]
        );

        $adminUser->assignRole($adminRole);
        $coordenadorUser->assignRole($coordenadorRole);

        /**
         * Criar domínios de email
         */

        $emailPermissionsList = [
            [
                'gmail.com',
                'edu.umuarama.pr.gov.br',
                'umuarama.pr.gov.br',
            ],
            [
                'Geral',
                'Educação',
                'Administrativo'
            ]
        ];

        foreach ($emailPermissionsList[0] as $index => $dominio) {
            $setor = $emailPermissionsList[1][$index] ?? 'Geral';

            DominioEmail::create([
                'dominio_email' => $dominio,
                'setor' => $setor,
                'status' => 1,
            ]);
        }

        /**
         * Criar séries
         */
        $seriesList = [
            'Infantil 4',
            'Infantil 5',
            '1º Ano',
            '2º Ano',
            '3º Ano',
            '4º Ano',
            '5º Ano',
        ];

        foreach ($seriesList as $seriesName) {
            $codigo = $this->gerarCodigoSerie($seriesName);

            // se já existe pelo nome, atualiza o código; senão, cria
            Serie::updateOrCreate(
                ['nome' => $seriesName],
                ['codigo' => $codigo]
            );
        }

        $this->call([
            EscolaSeeder::class,
            TurmaCodigoSeeder::class,
        ]);
    }

    private function gerarCodigoSerie(string $nome): ?string
    {
        // Infantil 4/5 => SI4 / SI5
        if (preg_match('/^Infantil\s*(4|5)$/iu', $nome, $m)) {
            return 'SI' . $m[1];
        }

        // 1º ao 5º Ano => S1A ... S5A
        if (preg_match('/^([1-5])º\s*Ano$/iu', $nome, $m)) {
            return 'S' . $m[1] . 'A';
        }

        // fallback se aparecer algo fora do padrão
        return null;
    }
}
