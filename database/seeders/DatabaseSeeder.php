<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Acesso;
use App\Models\Grupo;
use App\Models\Usuario;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create(); // Remover ou ajustar se não necessário

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]); // Comentar esta linha, pois a tabela 'users' não existe mais

        // Chamar os seeders específicos
        $this->call([
            AcessoSeeder::class,
            GrupoSeeder::class,
            UsuarioSeeder::class,
        ]);

        // --- Associações Padrão ---

        // Buscar o grupo admin
        $grupoAdmin = Grupo::where('descricao', 'admin')->first();

        // Buscar o usuário admin
        $usuarioAdmin = Usuario::where('login', 'admin')->first();

        // Buscar todos os acessos
        $todosAcessos = Acesso::all();

        if ($grupoAdmin && $todosAcessos->isNotEmpty()) {
            // Associar TODOS os acessos ao grupo admin
            $grupoAdmin->acessos()->sync($todosAcessos->pluck('id')->toArray());
            $this->command->info('Todos os acessos sincronizados com o grupo admin.');
        }

        if ($grupoAdmin && $usuarioAdmin) {
            // Associar o usuário admin ao grupo admin (se já não estiver)
            $usuarioAdmin->grupos()->syncWithoutDetaching([$grupoAdmin->id]);
            $this->command->info('Usuário admin associado ao grupo admin.');
        }
    }
}
