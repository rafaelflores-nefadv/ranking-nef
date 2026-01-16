// Controller do Dashboard
// Concentra a lógica de orquestração de dados da tela principal

import { useEffect, useMemo } from 'react';
import { useAppStore } from '@/core/store/AppStore';
import { useRanking } from '@/core/hooks/useRanking';
import { useRealtime } from '@/core/hooks/useRealtime';
import { Permission } from '@/core/types';
import { enrichRankingEntriesWithGamification } from '@/models/rankingModel';

export function useDashboardController() {
  const { state, dispatch, hasPermission } = useAppStore();
  const rankingHook = useRanking(state.rankingMode);

  // Enriquecimento gamificado do ranking (níveis, badges, status visual)
  const enrichedRanking = useMemo(() => {
    return {
      ...rankingHook.ranking,
      entries: enrichRankingEntriesWithGamification(rankingHook.ranking.entries),
    };
  }, [rankingHook.ranking]);

  // Substitui os dados crus do hook por dados enriquecidos
  const top3 = enrichedRanking.entries.slice(0, 3);
  const top10 = enrichedRanking.entries.slice(0, 10);

  // Tempo real
  useRealtime({
    enabled: state.config?.realtimeEnabled ?? true,
    pollingInterval: state.config?.pollingInterval ?? 5000,
    onScoreUpdate: () => {
      if (state.config?.soundsEnabled) {
        // Placeholder para efeito sonoro ou feedback futuro
      }
    },
  });

  // Verificação de permissão
  useEffect(() => {
    if (!hasPermission(Permission.VIEW_DASHBOARD)) {
      dispatch({
        type: 'SET_ERROR',
        payload: 'Você não tem permissão para acessar o dashboard',
      });
    }
  }, [hasPermission, dispatch]);

  // Estatísticas agregadas do painel
  const stats = useMemo(() => {
    const activeSellers = state.sellers.filter((s) => s.status === 'active');

    const totalPoints = activeSellers.reduce((sum, s) => sum + s.points, 0);
    const averagePoints =
      activeSellers.length > 0 ? totalPoints / activeSellers.length : 0;

    return {
      totalParticipants: activeSellers.length,
      totalTeams: state.teams.length,
      totalPoints,
      averagePoints,
    };
  }, [state.sellers, state.teams]);

  const setRankingMode = (mode) => {
    dispatch({ type: 'SET_RANKING_MODE', payload: mode });
  };

  return {
    state,
    stats,
    ranking: enrichedRanking,
    top3,
    top10,
    isLoading: rankingHook.isLoading,
    setRankingMode,
  };
}


