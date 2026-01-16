import { useEffect } from 'react';
import { useAppStore } from '../store/AppStore';
import { sellersService } from '../services/api/sellers';
import { teamsService } from '../services/api/teams';
import { seasonsService } from '../services/api/seasons';
import { configsService } from '../services/api/configs';
import { rankingService } from '../services/api/ranking';

export function useInitialData() {
  const { dispatch, state, checkAuth } = useAppStore();

  useEffect(() => {
    const loadInitialData = async () => {
      // Verificar autenticação primeiro
      const isAuth = await checkAuth();
      if (!isAuth) {
        dispatch({ type: 'SET_LOADING', payload: false });
        return;
      }

      dispatch({ type: 'SET_LOADING', payload: true });

      try {
        // Carregar dados em paralelo
        const [sellers, teams, seasons, configs, ranking] = await Promise.allSettled([
          sellersService.list(),
          teamsService.list(),
          seasonsService.list(),
          configsService.list(),
          rankingService.getRanking(),
        ]);

        // Processar sellers
        if (sellers.status === 'fulfilled') {
          dispatch({ type: 'SET_SELLERS', payload: sellers.value || [] });
        }

        // Processar teams
        if (teams.status === 'fulfilled') {
          dispatch({ type: 'SET_TEAMS', payload: teams.value || [] });
        }

        // Processar seasons
        if (seasons.status === 'fulfilled') {
          const seasonsData = seasons.value || [];
          dispatch({ type: 'SET_SEASONS', payload: seasonsData });
          const activeSeason = seasonsData.find((s) => s.is_active);
          if (activeSeason) {
            dispatch({ type: 'SET_ACTIVE_SEASON', payload: activeSeason });
          }
        }

        // Processar configs
        if (configs.status === 'fulfilled') {
          dispatch({ type: 'SET_CONFIG', payload: configs.value || [] });
        }

        // Processar ranking
        if (ranking.status === 'fulfilled') {
          dispatch({ type: 'SET_RANKING', payload: ranking.value || [] });
        }
      } catch (error) {
        console.error('Erro ao carregar dados:', error);
        dispatch({
          type: 'SET_ERROR',
          payload: error.message || 'Erro ao carregar dados',
        });
      } finally {
        dispatch({ type: 'SET_LOADING', payload: false });
      }
    };

    loadInitialData();
  }, [dispatch, checkAuth]);
}
