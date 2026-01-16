/**
 * Controller para a página de Vendedores (Sellers)
 * Centraliza toda a lógica de negócio e orquestração de dados
 * 
 * Responsabilidades:
 * - Carregar vendedores e equipes do store/API
 * - Gerenciar estado local (busca, modais, edição)
 * - Executar operações CRUD (criar, editar, deletar)
 * - Enriquecer dados com informações de gamificação
 */

import { useState, useEffect, useMemo } from 'react';
import { useAppStore } from '@/core/store/AppStore';
import { sellersService } from '@/core/services/api/sellers';
import { teamsService } from '@/core/services/api/teams';
import { calculateLevel, getBadgeForLevel } from '@/models/rankingModel';

export function useSellersController() {
  const { state, dispatch } = useAppStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [showSellerForm, setShowSellerForm] = useState(false);
  const [showImport, setShowImport] = useState(false);
  const [editingSeller, setEditingSeller] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  // Carregar dados iniciais (vendedores e equipes)
  useEffect(() => {
    const loadData = async () => {
      setIsLoading(true);
      try {
        // Carregar vendedores
        const sellersResponse = await sellersService.list();
        if (sellersResponse.success && sellersResponse.data) {
          dispatch({
            type: 'SET_SELLERS',
            payload: sellersResponse.data.data || sellersResponse.data || [],
          });
        }

        // Carregar equipes (necessário para exibir nome da equipe de cada vendedor)
        const teamsResponse = await teamsService.list();
        if (teamsResponse.success && teamsResponse.data) {
          dispatch({
            type: 'SET_TEAMS',
            payload: teamsResponse.data.data || teamsResponse.data || [],
          });
        }
      } catch (error) {
        console.error('Erro ao carregar dados:', error);
      } finally {
        setIsLoading(false);
      }
    };

    // Só carrega se ainda não tiver dados
    if (state.sellers.length === 0 || state.teams.length === 0) {
      loadData();
    }
  }, [dispatch, state.sellers.length, state.teams.length]);

  // Filtrar vendedores baseado no termo de busca
  const filteredSellers = useMemo(() => {
    return state.sellers.filter(
      (seller) =>
        seller.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        seller.email?.toLowerCase().includes(searchTerm.toLowerCase())
    );
  }, [state.sellers, searchTerm]);

  // Enriquecer vendedores com dados de gamificação (nível e badge baseado em pontos)
  const enrichedSellers = useMemo(() => {
    return filteredSellers.map((seller) => {
      const level = calculateLevel(seller.points || 0);
      const badge = getBadgeForLevel(level);

      return {
        ...seller,
        level,
        badge,
      };
    });
  }, [filteredSellers]);

  // Buscar nome da equipe pelo ID
  const getTeamName = (teamId) => {
    const team = state.teams.find((t) => t.id === teamId);
    return team?.name || 'Sem equipe';
  };

  // Traduzir nível de acesso para português
  const getAccessLevelLabel = (level) => {
    const labels = {
      admin: 'Administrador',
      gestor: 'Gestor',
      vendedor: 'Vendedor (sem edição)',
    };
    return labels[level] || level;
  };

  // Abrir modal de edição
  const handleEdit = (seller) => {
    setEditingSeller(seller);
    setShowSellerForm(true);
  };

  // Deletar vendedor (com confirmação)
  const handleDelete = async (seller) => {
    if (confirm(`Tem certeza que deseja excluir ${seller.name}?`)) {
      setIsLoading(true);
      try {
        const response = await sellersService.delete(seller.id);
        if (response.success) {
          dispatch({ type: 'DELETE_SELLER', payload: seller.id });
        }
      } catch (error) {
        console.error('Erro ao deletar vendedor:', error);
      } finally {
        setIsLoading(false);
      }
    }
  };

  // Fechar modal de formulário
  const handleCloseForm = () => {
    setShowSellerForm(false);
    setEditingSeller(null);
  };

  return {
    // Estado
    sellers: enrichedSellers,
    teams: state.teams,
    isLoading,

    // Busca
    searchTerm,
    setSearchTerm,

    // Modais
    showSellerForm,
    setShowSellerForm,
    showImport,
    setShowImport,
    editingSeller,

    // Ações
    handleEdit,
    handleDelete,
    handleCloseForm,
    getTeamName,
    getAccessLevelLabel,
  };
}

