<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Acesso;

class AcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissoes = [
            // Usuarios
            ['key' => 'view_usuarios', 'descricao' => 'Visualizar Usuários', 'menu' => 'Usuarios'],
            ['key' => 'create_usuarios', 'descricao' => 'Criar Usuários', 'menu' => 'Usuarios'],
            ['key' => 'update_usuarios', 'descricao' => 'Atualizar Usuários', 'menu' => 'Usuarios'],
            ['key' => 'delete_usuarios', 'descricao' => 'Excluir Usuários', 'menu' => 'Usuarios'],
            // Grupos
            ['key' => 'view_grupos', 'descricao' => 'Visualizar Grupos', 'menu' => 'Grupos'],
            ['key' => 'create_grupos', 'descricao' => 'Criar Grupos', 'menu' => 'Grupos'],
            ['key' => 'update_grupos', 'descricao' => 'Atualizar Grupos', 'menu' => 'Grupos'],
            ['key' => 'delete_grupos', 'descricao' => 'Excluir Grupos', 'menu' => 'Grupos'],
            ['key' => 'manage_grupo_acessos', 'descricao' => 'Gerenciar Acessos do Grupo', 'menu' => 'Grupos'],
            ['key' => 'manage_grupo_usuarios', 'descricao' => 'Gerenciar Usuários do Grupo', 'menu' => 'Grupos'],
            // Acessos
            ['key' => 'view_acessos', 'descricao' => 'Visualizar Acessos', 'menu' => 'Acessos'],
            ['key' => 'create_acessos', 'descricao' => 'Criar Acessos', 'menu' => 'Acessos'],
            ['key' => 'update_acessos', 'descricao' => 'Atualizar Acessos', 'menu' => 'Acessos'],
            ['key' => 'delete_acessos', 'descricao' => 'Excluir Acessos', 'menu' => 'Acessos'],
             //Grupos Usuários
             ['key' => 'view_grupo_usuario', 'descricao' => 'Visualizar Grupos Usuários', 'menu' => 'GruposUsuarios'],
             ['key' => 'create_grupo_usuario', 'descricao' => 'Criar Grupos Usuários', 'menu' => 'GruposUsuarios'],
             ['key' => 'update_grupo_usuario', 'descricao' => 'Atualizar Grupos Usuários', 'menu' => 'GruposUsuarios'],
             ['key' => 'delete_grupo_usuario', 'descricao' => 'Excluir Grupos Usuários', 'menu' => 'GruposUsuarios'],
            //Acesso Grupos ( Permissões )
            ['key' => 'view_acesso_grupo', 'descricao' => 'Visualizar Acesso Grupos', 'menu' => 'AcessoGrupos'],
            ['key' => 'create_acesso_grupo', 'descricao' => 'Criar Acesso Grupos', 'menu' => 'AcessoGrupos'],
            ['key' => 'update_acesso_grupo', 'descricao' => 'Atualizar Acesso Grupos', 'menu' => 'AcessoGrupos'],
            ['key' => 'delete_acesso_grupo', 'descricao' => 'Excluir Acesso Grupos', 'menu' => 'AcessoGrupos'],
            //Configurações
            ['key' => 'create_configuracoes', 'descricao' => 'Criar Configurações', 'menu' => 'Configuracoes'],            
            ['key' => 'view_configuracoes', 'descricao' => 'Visualizar Configurações', 'menu' => 'Configuracoes'],
            ['key' => 'update_configuracoes', 'descricao' => 'Atualizar Configurações', 'menu' => 'Configuracoes'],
            ['key' => 'delete_configuracoes', 'descricao' => 'Excluir Configurações', 'menu' => 'Configuracoes'],
            //Eventos
            ['key' => 'view_eventos', 'descricao' => 'Visualizar Eventos', 'menu' => 'Eventos'],
            ['key' => 'create_eventos', 'descricao' => 'Criar Eventos', 'menu' => 'Eventos'],
            ['key' => 'update_eventos', 'descricao' => 'Atualizar Eventos', 'menu' => 'Eventos'],
            ['key' => 'delete_eventos', 'descricao' => 'Excluir Eventos', 'menu' => 'Eventos'],
            //Inscrições
            ['key' => 'view_inscricoes', 'descricao' => 'Visualizar Inscrições', 'menu' => 'Inscricoes'],
            ['key' => 'create_inscricoes', 'descricao' => 'Criar Inscrições', 'menu' => 'Inscricoes'],
            ['key' => 'update_inscricoes', 'descricao' => 'Atualizar Inscrições', 'menu' => 'Inscricoes'],
            ['key' => 'delete_inscricoes', 'descricao' => 'Excluir Inscrições', 'menu' => 'Inscricoes'],
            //Participantes
            ['key' => 'view_participantes', 'descricao' => 'Visualizar Participantes', 'menu' => 'Participantes'],
            ['key' => 'create_participantes', 'descricao' => 'Criar Participantes', 'menu' => 'Participantes'],
            ['key' => 'update_participantes', 'descricao' => 'Atualizar Participantes', 'menu' => 'Participantes'],
            ['key' => 'delete_participantes', 'descricao' => 'Excluir Participantes', 'menu' => 'Participantes'],
        ];

        foreach ($permissoes as $permissao) {
            Acesso::updateOrCreate(
                ['key' => $permissao['key']], // Cria ou atualiza baseado na chave
                [
                    'descricao' => $permissao['descricao'],
                    'menu' => $permissao['menu']
                ]
            );
        }
    }
}
