/**
 * Controller para a página de Equipes (Teams)
 * Centraliza toda a lógica de negócio e orquestração de dados
 * 
 * Responsabilidades:
 * - Carregar equipes do store/API
 * - Gerenciar estado local (busca, modais, edição)
 * - Executar operações CRUD (criar, editar, deletar)
 * - Calcular estatísticas das equipes (pontos totais, membros)
 */

import { useState, useEffect, useMemo } from 'react';
import { useAppStore } from '@/core/store/AppStore';
import { teamsService } from '@/core/services/api/teams';

export function useTeamsController() {
  const { state, dispatch } = useAppStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [showTeamForm, setShowTeamForm] = useState(false);
  const [editingTeam, setEditingTeam] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // Carregar equipes do servidor
  useEffect(() => {
    const loadData = async () => {
      setIsLoading(true);
      try {
        const response = await teamsService.list();
        if (response.success && response.data) {
          dispatch({
            type: 'SET_TEAMS',
            payload: response.data.data || response.data || [],
          });
        }
      } catch (error) {
        console.error('Erro ao carregar equipes:', error);
      } finally {
        setIsLoading(false);
      }
    };

    // Só carrega se ainda não tiver dados
    if (state.teams.length === 0) {
      loadData();
    }
  }, [dispatch, state.teams.length]);

  // Filtrar equipes baseado no termo de busca
  const filteredTeams = useMemo(() => {
    return state.teams.filter(
      (team) =>
        team.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        team.code?.toLowerCase().includes(searchTerm.toLowerCase())
    );
  }, [state.teams, searchTerm]);

  // Enriquecer equipes com estatísticas (contagem de membros, pontos totais)
  const enrichedTeams = useMemo(() => {
    return filteredTeams.map((team) => {
      // Contar quantos vendedores pertencem a esta equipe
      const members = state.sellers.filter(
        (seller) => seller.team_id === team.id && seller.status === 'active'
      );

      // Calcular pontos totais da equipe (soma dos pontos dos membros)
      const totalPoints = members.reduce((sum, seller) => sum + (seller.points || 0), 0);

      return {
        ...team,
        memberCount: members.length,
        totalPoints,
      };
    });
  }, [filteredTeams, state.sellers]);

  // Abrir modal de edição
  const handleEdit = (team) => {
    setEditingTeam(team);
    setShowTeamForm(true);
  };

  // Deletar equipe (com confirmação)
  const handleDelete = async (team) => {
    if (confirm(`Tem certeza que deseja excluir a equipe ${team.name}?`)) {
      setIsLoading(true);
      try {
        const response = await teamsService.delete(team.id);
        if (response.success) {
          dispatch({ type: 'DELETE_TEAM', payload: team.id });
        }
      } catch (error) {
        console.error('Erro ao deletar equipe:', error);
      } finally {
        setIsLoading(false);
      }
    }
  };

  // Fechar modal de formulário
  const handleCloseForm = () => {
    setShowTeamForm(false);
    setEditingTeam(null);
  };

  return {
    // Estado
    teams: enrichedTeams,
    isLoading,

    // Busca
    searchTerm,
    setSearchTerm,

    // Modais
    showTeamForm,
    setShowTeamForm,
    editingTeam,

    // Ações
    handleEdit,
    handleDelete,
    handleCloseForm,
  };
}

