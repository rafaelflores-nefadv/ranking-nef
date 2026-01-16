/**
 * Mapeamento de permissões por perfil
 * Define quais ações cada tipo de usuário pode realizar
 */

import { UserRole, Permission } from '../types';

export const ROLE_PERMISSIONS = {
  [UserRole.ADMIN]: [
    Permission.VIEW_DASHBOARD,
    Permission.VIEW_SELLERS,
    Permission.CREATE_SELLER,
    Permission.EDIT_SELLER,
    Permission.DELETE_SELLER,
    Permission.VIEW_TEAMS,
    Permission.CREATE_TEAM,
    Permission.EDIT_TEAM,
    Permission.DELETE_TEAM,
    Permission.VIEW_SETTINGS,
    Permission.EDIT_SETTINGS,
    Permission.VIEW_SCORES,
    Permission.MANAGE_SCORES,
    Permission.VIEW_SEASONS,
    Permission.MANAGE_SEASONS,
  ],
  
  [UserRole.SUPERVISOR]: [
    Permission.VIEW_DASHBOARD,
    Permission.VIEW_SELLERS,
    Permission.CREATE_SELLER,
    Permission.EDIT_SELLER,
    Permission.VIEW_TEAMS,
    Permission.EDIT_TEAM,
    Permission.VIEW_SCORES,
    Permission.VIEW_SEASONS,
  ],
  
  [UserRole.USER]: [
    Permission.VIEW_DASHBOARD,
    Permission.VIEW_SELLERS,
    Permission.VIEW_TEAMS,
    Permission.VIEW_SCORES,
    Permission.VIEW_SEASONS,
  ],
};

/**
 * Verifica se um perfil tem uma permissão específica
 */
export function hasPermission(role, permission) {
  return ROLE_PERMISSIONS[role]?.includes(permission) ?? false;
}

/**
 * Verifica se um perfil tem pelo menos uma das permissões
 */
export function hasAnyPermission(role, permissions) {
  return permissions.some(permission => hasPermission(role, permission));
}

/**
 * Verifica se um perfil tem todas as permissões
 */
export function hasAllPermissions(role, permissions) {
  return permissions.every(permission => hasPermission(role, permission));
}

