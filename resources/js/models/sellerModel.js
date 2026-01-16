/**
 * Model: Seller
 * Regras de negócio e validações para Vendedores
 */

/**
 * Valida dados de um vendedor
 */
export function validateSeller(data) {
  const errors = [];

  if (!data.name || data.name.trim().length === 0) {
    errors.push('Nome é obrigatório');
  }

  if (!data.email || data.email.trim().length === 0) {
    errors.push('E-mail é obrigatório');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.push('E-mail inválido');
  }

  if (data.points !== undefined && (isNaN(data.points) || data.points < 0)) {
    errors.push('Pontos devem ser um número não negativo');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * Calcula estatísticas de um vendedor
 */
export function calculateSellerStats(seller, allSellers = []) {
  const sortedSellers = [...allSellers].sort((a, b) => (b.points || 0) - (a.points || 0));
  const position = sortedSellers.findIndex((s) => s.id === seller.id) + 1;

  return {
    position: position || allSellers.length + 1,
    totalSellers: allSellers.length,
    points: seller.points || 0,
  };
}

/**
 * Normaliza dados de um vendedor
 */
export function normalizeSeller(data) {
  return {
    id: data.id,
    name: data.name?.trim() || '',
    email: data.email?.trim().toLowerCase() || '',
    points: data.points || 0,
    status: data.status || 'active',
    teamId: data.teamId || null,
    adminId: data.adminId || null,
    avatar: data.avatar || null,
    metadata: data.metadata || {},
    createdAt: data.createdAt || new Date().toISOString(),
    updatedAt: data.updatedAt || new Date().toISOString(),
  };
}

