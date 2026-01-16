/**
 * Model: Season
 * Regras de negócio e validações para Temporadas
 */

/**
 * Valida dados de uma temporada
 */
export function validateSeason(data) {
  const errors = [];

  if (!data.name || data.name.trim().length === 0) {
    errors.push('Nome da temporada é obrigatório');
  }

  if (!data.startDate) {
    errors.push('Data de início é obrigatória');
  }

  if (!data.endDate) {
    errors.push('Data de fim é obrigatória');
  }

  if (data.startDate && data.endDate) {
    const start = new Date(data.startDate);
    const end = new Date(data.endDate);

    if (start >= end) {
      errors.push('Data de fim deve ser posterior à data de início');
    }
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * Verifica se uma temporada está ativa
 */
export function isSeasonActive(season) {
  if (!season) return false;
  if (!season.startDate || !season.endDate) return false;

  const now = new Date();
  const start = new Date(season.startDate);
  const end = new Date(season.endDate);

  return now >= start && now <= end;
}

/**
 * Calcula dias restantes de uma temporada
 */
export function getDaysRemaining(season) {
  if (!season || !season.endDate) return 0;

  const now = new Date();
  const end = new Date(season.endDate);
  const diff = end - now;

  if (diff <= 0) return 0;

  return Math.ceil(diff / (1000 * 60 * 60 * 24));
}

/**
 * Normaliza dados de uma temporada
 */
export function normalizeSeason(data) {
  return {
    id: data.id,
    name: data.name?.trim() || '',
    startDate: data.startDate,
    endDate: data.endDate,
    isActive: data.isActive !== undefined ? data.isActive : isSeasonActive(data),
    adminId: data.adminId || null,
    metadata: data.metadata || {},
    createdAt: data.createdAt || new Date().toISOString(),
    updatedAt: data.updatedAt || new Date().toISOString(),
  };
}

