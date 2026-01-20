<?php

namespace App\Services;

use App\Models\Config;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    const CACHE_KEY = 'supervisor_permissions';
    const CACHE_TTL = 3600; // 1 hora

    /**
     * Verifica se um usuário pode executar uma ação em um módulo.
     * 
     * @param \App\Models\User|null $user
     * @param string $module
     * @param string $action
     * @return bool
     */
    public static function can(?object $user, string $module, string $action): bool
    {
        // Se não houver usuário, nega acesso
        if (!$user) {
            return false;
        }

        // Admin sempre tem acesso total
        if ($user->role === 'admin') {
            return true;
        }

        // Apenas supervisores têm permissões configuráveis
        if ($user->role !== 'supervisor') {
            return false;
        }

        // Buscar permissões do supervisor (com cache)
        $permissions = self::getSupervisorPermissions();

        // Verificar se o módulo existe nas permissões
        if (!isset($permissions[$module])) {
            return false;
        }

        // Verificar se a ação está permitida para o módulo
        return in_array($action, $permissions[$module], true);
    }

    /**
     * Obtém as permissões configuradas para supervisores.
     * 
     * @return array
     */
    public static function getSupervisorPermissions(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $config = Config::where('key', 'supervisor_permissions')->first();
            
            if (!$config || !$config->value) {
                return self::getDefaultPermissions();
            }

            $decoded = json_decode($config->value, true);
            
            // Validar JSON
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return self::getDefaultPermissions();
            }

            return $decoded;
        });
    }

    /**
     * Define as permissões para supervisores.
     * 
     * @param array $permissions
     * @return void
     */
    public static function setSupervisorPermissions(array $permissions): void
    {
        // Validar estrutura
        $validated = self::validatePermissions($permissions);
        
        // Salvar no banco
        Config::updateOrCreate(
            ['key' => 'supervisor_permissions'],
            ['value' => json_encode($validated)]
        );

        // Limpar cache
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Valida e normaliza a estrutura de permissões.
     * 
     * @param array $permissions
     * @return array
     */
    protected static function validatePermissions(array $permissions): array
    {
        $validated = [];
        $validActions = ['view', 'create', 'edit', 'delete', 'toggle'];

        foreach ($permissions as $module => $actions) {
            // Garantir que actions é um array
            if (!is_array($actions)) {
                continue;
            }

            // Filtrar apenas ações válidas
            $validatedActions = array_filter($actions, function ($action) use ($validActions) {
                return in_array($action, $validActions, true);
            });

            // Remover duplicatas e reindexar
            $validatedActions = array_values(array_unique($validatedActions));

            // Adicionar apenas se houver ações válidas
            if (!empty($validatedActions)) {
                $validated[$module] = $validatedActions;
            }
        }

        return $validated;
    }

    /**
     * Retorna permissões padrão para instalação.
     * 
     * @return array
     */
    public static function getDefaultPermissions(): array
    {
        return [
            'dashboard' => ['view'],
            'sellers' => ['view', 'edit'],
            'teams' => ['view'],
            'goals' => ['view', 'create'],
            'reports' => ['view'],
        ];
    }

    /**
     * Limpa o cache de permissões.
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Retorna lista de módulos disponíveis no sistema.
     * 
     * @return array
     */
    public static function getAvailableModules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'sellers' => 'Vendedores',
            'teams' => 'Equipes',
            'goals' => 'Metas',
            'reports' => 'Relatórios',
        ];
    }

    /**
     * Retorna lista de ações disponíveis.
     * 
     * @return array
     */
    public static function getAvailableActions(): array
    {
        return [
            'view' => 'Visualizar',
            'create' => 'Criar',
            'edit' => 'Editar',
            'delete' => 'Excluir',
            'toggle' => 'Ativar/Desativar',
        ];
    }
}
