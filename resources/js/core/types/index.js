/**
 * Tipos e constantes principais do sistema
 * Arquitetura independente e escalável
 */

// ============================================
// PERFIS E PERMISSÕES
// ============================================

export const UserRole = {
  ADMIN: 'admin',
  SUPERVISOR: 'supervisor',
  USER: 'user'
};

export const Permission = {
  // Dashboard
  VIEW_DASHBOARD: 'view_dashboard',
  
  // Vendedores
  VIEW_SELLERS: 'view_sellers',
  CREATE_SELLER: 'create_seller',
  EDIT_SELLER: 'edit_seller',
  DELETE_SELLER: 'delete_seller',
  
  // Equipes
  VIEW_TEAMS: 'view_teams',
  CREATE_TEAM: 'create_team',
  EDIT_TEAM: 'edit_team',
  DELETE_TEAM: 'delete_team',
  
  // Configurações
  VIEW_SETTINGS: 'view_settings',
  EDIT_SETTINGS: 'edit_settings',
  
  // Pontuações
  VIEW_SCORES: 'view_scores',
  MANAGE_SCORES: 'manage_scores',
  
  // Temporadas
  VIEW_SEASONS: 'view_seasons',
  MANAGE_SEASONS: 'manage_seasons'
};

