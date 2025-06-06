<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache; // Opcional: Para cachear permissões
use Illuminate\Support\Facades\Log; // Adicionado

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'login',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        // Garante que as relações necessárias estão carregadas
        if (!$this->relationLoaded('grupos') || ($this->grupos->first() && !$this->grupos->first()->relationLoaded('acessos'))) {
            $this->load('grupos.acessos');
        }

        $permissions = collect();
        foreach ($this->grupos as $grupo) {
            if ($grupo->relationLoaded('acessos') && $grupo->acessos) {
                $permissions = $permissions->merge($grupo->acessos->pluck('key'));
            }
        }

        // Retorna as permissões únicas como uma claim chamada 'permissions'
        return [
            'id' => $this->id,
            'name' => $this->name,
            'login' => $this->login,
            'permissions' => $permissions->unique()->values()->all()
        ];
    }

    /**
     * Os grupos aos quais o usuário pertence.
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_usuario');
    }

    /**
     * Verifica se o usuário possui uma determinada permissão através de seus grupos.
     *
     * @param string $permissionKey A chave (key) da permissão a ser verificada.
     * @return bool
     */
    public function hasPermissionTo(string $permissionKey): bool
    {
        //Log::debug("Usuario::hasPermissionTo: Verificando permissão '{$permissionKey}' para usuário ID {$this->id}.");

        if (empty($permissionKey)) {
            //Log::warning("Usuario::hasPermissionTo: Tentativa de verificar uma chave de permissão vazia para usuário ID {$this->id}. Retornando false.");
            return false;
        }

        // Garante que as relações necessárias estão carregadas
        if (!$this->relationLoaded('grupos')) {
            //Log::debug("Usuario::hasPermissionTo: Relação 'grupos' não carregada para usuário ID {$this->id}. Carregando 'grupos.acessos'...");
            $this->load('grupos.acessos'); // Carrega grupos e seus acessos de uma vez
        } else {
            // Se grupos já estão carregados, verifica se os acessos de cada grupo estão
            foreach ($this->grupos as $grupo) {
                if (!$grupo->relationLoaded('acessos')) {
                    //Log::debug("Usuario::hasPermissionTo: Relação 'acessos' não carregada para o grupo ID {$grupo->id} (usuário ID {$this->id}). Carregando acessos para este grupo...");
                    $grupo->load('acessos');
                }
            }
        }

        if ($this->grupos->isEmpty()) {
            //Log::debug("Usuario::hasPermissionTo: Usuário ID {$this->id} não pertence a nenhum grupo. Permissão '{$permissionKey}' NEGADA.");
            return false;
        }

        foreach ($this->grupos as $grupo) {
            // Acessos do grupo já devem estar carregados aqui devido ao 'load' acima
            if ($grupo->acessos && $grupo->acessos->contains('key', $permissionKey)) {
                //Log::info("Usuario::hasPermissionTo: Permissão '{$permissionKey}' ENCONTRADA para usuário ID {$this->id} através do grupo ID {$grupo->id} ('{$grupo->nome}')."); // Assumindo que Grupo tem um atributo 'nome'
                return true;
            }
        }

        //Log::info("Usuario::hasPermissionTo: Permissão '{$permissionKey}' NÃO encontrada para usuário ID {$this->id} em nenhum de seus grupos.");
        return false;
    }

    // Opcional: Método para limpar cache de permissões ao atualizar grupos/acessos
    // public static function boot()
    // {
    //     parent::boot();
    //     static::saved(function ($model) {
    //         Cache::forget("user_{$model->id}_permissions");
    //     });
    //     // Adicionar listeners para Grupo e Acesso também, se usar cache
    // }
}
