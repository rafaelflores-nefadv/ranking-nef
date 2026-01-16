// Camada de modelo para regras de gamificação do ranking
// Responsável por calcular nível, badge e status visual a partir da pontuação.

// Tabela simples de níveis baseada em pontos
const levelThresholds = [
  { level: 1, min: 0, max: 999 },
  { level: 2, min: 1000, max: 4999 },
  { level: 3, min: 5000, max: 9999 },
  { level: 4, min: 10000, max: 19999 },
  { level: 5, min: 20000, max: Infinity },
];

export function calculateLevel(points) {
  const found = levelThresholds.find(
    (t) => points >= t.min && points <= t.max,
  );
  return found?.level ?? 1;
}

export function getBadgeForLevel(level) {
  if (level >= 5) return 'Elite';
  if (level >= 4) return 'Pro';
  if (level >= 3) return 'Avançado';
  if (level >= 2) return 'Intermediário';
  return 'Iniciante';
}

export function getStatusFromChange(change) {
  if (change > 0) return 'subindo';
  if (change < 0) return 'caindo';
  return 'estável';
}

export function enrichRankingEntriesWithGamification(entries) {
  return entries.map((entry) => {
    const level = calculateLevel(entry.points ?? 0);
    const badge = getBadgeForLevel(level);
    const status = getStatusFromChange(entry.change ?? 0);

    return {
      ...entry,
      level,
      badge,
      status,
    };
  });
}


