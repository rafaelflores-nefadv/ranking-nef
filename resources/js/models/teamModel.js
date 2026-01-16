/**
 * Model: Team
 * Regras de negócio e validações para Equipes
 */

/**
 * Valida dados de uma equipe
 */
export function validateTeam(data) {
  const errors = [];

  if (!data.name || data.name.trim().length === 0) {
    errors.push('Nome da equipe é obrigatório');
  }

  if (data.adminId && typeof data.adminId !== 'string') {
    errors.push('adminId deve ser uma string');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * Calcula pontos totais de uma equipe baseado nos membros
 */
export function calculateTeamPoints(team, sellers = []) {
  if (!team.memberIds || team.memberIds.length === 0) {
    return 0;
  }

  const teamSellers = sellers.filter((seller) => team.memberIds.includes(seller.id));
  return teamSellers.reduce((sum, seller) => sum + (seller.points || 0), 0);
}

/**
 * Calcula estatísticas de uma equipe
 */
export function calculateTeamStats(team, allTeams = [], sellers = []) {
  const points = calculateTeamPoints(team, sellers);
  const sortedTeams = [...allTeams]
    .map((t) => ({
      ...t,
      calculatedPoints: calculateTeamPoints(t, sellers),
    }))
    .sort((a, b) => b.calculatedPoints - a.calculatedPoints);

  const position = sortedTeams.findIndex((t) => t.id === team.id) + 1;

  const teamSellers = sellers.filter((seller) => team.memberIds?.includes(seller.id) || false);

  return {
    position: position || allTeams.length + 1,
    points,
    membersCount: teamSellers.length,
    totalTeams: allTeams.length,
  };
}

/**
 * Normaliza dados de uma equipe
 */
export function normalizeTeam(data) {
  return {
    id: data.id,
    name: data.name?.trim() || '',
    description: data.description?.trim() || '',
    status: data.status || 'active',
    adminId: data.adminId || null,
    memberIds: data.memberIds || [],
    points: data.points || 0,
    metadata: data.metadata || {},
    createdAt: data.createdAt || new Date().toISOString(),
    updatedAt: data.updatedAt || new Date().toISOString(),
  };
}

