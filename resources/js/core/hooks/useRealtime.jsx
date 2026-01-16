import { useEffect, useRef } from 'react';
import { useAppStore } from '../store/AppStore';
import { sellersService } from '../services/api/sellers';
import { teamsService } from '../services/api/teams';

export function useRealtime(options = {}) {
  const { state, dispatch } = useAppStore();
  const intervalRef = useRef(null);
  const {
    enabled = true,
    pollingInterval = 5000,
    onScoreUpdate,
  } = options;

  useEffect(() => {
    if (!enabled || !state.config?.realtimeEnabled) {
      return;
    }

    const poll = async () => {
      try {
        const sellersResponse = await sellersService.getRanking({
          seasonId: state.activeSeason?.id,
        });

        if (sellersResponse.success && sellersResponse.data) {
          const updatedSellers = sellersResponse.data.map((seller) => {
            const existing = state.sellers.find((s) => s.id === seller.id);
            return {
              ...seller,
              previousPosition: existing?.position,
            };
          });

          dispatch({ type: 'SET_SELLERS', payload: updatedSellers });

          updatedSellers.forEach((seller) => {
            if (seller.previousPosition && seller.previousPosition !== seller.position) {
              const event = {
                type: 'position_change',
                payload: {
                  entityId: seller.id,
                  entityType: 'seller',
                  oldPosition: seller.previousPosition,
                  newPosition: seller.position,
                },
                timestamp: new Date().toISOString(),
              };

              onScoreUpdate?.(event);
            }
          });
        }

        if (state.rankingMode === 'team') {
          const teamsResponse = await teamsService.getRanking({
            seasonId: state.activeSeason?.id,
          });

          if (teamsResponse.success && teamsResponse.data) {
            const updatedTeams = teamsResponse.data.map((team) => {
              const existing = state.teams.find((t) => t.id === team.id);
              return {
                ...team,
                previousPosition: existing?.position,
              };
            });

            dispatch({ type: 'SET_TEAMS', payload: updatedTeams });
          }
        }
      } catch (error) {
        console.error('Erro ao buscar atualizações em tempo real:', error);
      }
    };

    poll();

    const interval = state.config.pollingInterval || pollingInterval;
    intervalRef.current = setInterval(poll, interval);

    return () => {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
      }
    };
  }, [
    enabled,
    pollingInterval,
    state.config,
    state.activeSeason,
    state.rankingMode,
    state.sellers,
    state.teams,
    dispatch,
    onScoreUpdate,
  ]);

  return {
    isConnected: enabled && !!state.config?.realtimeEnabled,
  };
}

