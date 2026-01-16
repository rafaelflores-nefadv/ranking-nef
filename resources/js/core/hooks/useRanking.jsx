import { useEffect, useMemo } from 'react';
import { useAppStore } from '../store/AppStore';

export function useRanking(mode = 'general', teamId) {
  const { state, dispatch } = useAppStore();

  const ranking = useMemo(() => {
    let entries = [];

    switch (mode) {
      case 'general': {
        const baseEntries = state.sellers
          .filter((s) => s.status === 'active')
          .map((seller) => ({
            id: seller.id,
            name: seller.name,
            points: seller.points,
            previousPosition: seller.previousPosition,
            avatar: seller.avatar,
            teamId: seller.teamId,
            teamName: state.teams.find((t) => t.id === seller.teamId)?.name,
          }));

        entries = baseEntries
          .sort((a, b) => b.points - a.points)
          .map((entry, index) => ({
            ...entry,
            position: index + 1,
            change: entry.previousPosition
              ? entry.previousPosition - (index + 1)
              : 0,
          }));
        break;
      }

      case 'team': {
        const baseEntries = state.teams.map((team) => ({
          id: team.id,
          name: team.name,
          points: team.points,
          previousPosition: team.previousPosition,
          avatar: team.logo,
        }));

        entries = baseEntries
          .sort((a, b) => b.points - a.points)
          .map((entry, index) => ({
            ...entry,
            position: index + 1,
            change: entry.previousPosition
              ? entry.previousPosition - (index + 1)
              : 0,
          }));
        break;
      }

      case 'individual': {
        const sellers = teamId
          ? state.sellers.filter(
              (s) => s.teamId === teamId && s.status === 'active',
            )
          : state.sellers.filter((s) => s.status === 'active');

        const baseEntries = sellers.map((seller) => ({
          id: seller.id,
          name: seller.name,
          points: seller.points,
          previousPosition: seller.previousPosition,
          avatar: seller.avatar,
          teamId: seller.teamId,
          teamName: state.teams.find((t) => t.id === seller.teamId)?.name,
        }));

        entries = baseEntries
          .sort((a, b) => b.points - a.points)
          .map((entry, index) => ({
            ...entry,
            position: index + 1,
            change: entry.previousPosition
              ? entry.previousPosition - (index + 1)
              : 0,
          }));
        break;
      }
    }

    return {
      mode,
      entries,
      seasonId: state.activeSeason?.id,
      updatedAt: new Date().toISOString(),
    };
  }, [state.sellers, state.teams, mode, teamId, state.activeSeason]);

  useEffect(() => {
    dispatch({
      type: 'SET_RANKING',
      payload: ranking,
    });
  }, [ranking, dispatch]);

  return {
    ranking,
    top3: ranking.entries.slice(0, 3),
    top10: ranking.entries.slice(0, 10),
    isLoading: state.loading,
  };
}

