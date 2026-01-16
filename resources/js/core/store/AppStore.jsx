import React, { createContext, useContext, useReducer } from 'react';
import { hasPermission } from '../constants/permissions';
import { Permission, UserRole } from '../types';
import { authService } from '../services/auth';

// Store global da aplicaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
// Gerencia estado centralizado com Context API


// ============================================
// REDUCER
// ============================================

const getInitialState = () => {
  // NÃƒÆ’Ã‚Â£o buscar sessÃƒÆ’Ã‚Â£o local na inicializaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
  // SerÃƒÆ’Ã‚Â¡ buscado via /api/me quando necessÃƒÆ’Ã‚Â¡rio
  return {
    user: null,
    isAuthenticated: false,
    sellers: [],
    teams: [],
    seasons: [],
    activeSeason: null,
    ranking: null,
    config: null,
    loading: false,
    error: null,
    rankingMode: 'general',
  };
};

const initialState = getInitialState();

function appReducer(state, action) {
  switch (action.type) {
    case 'SET_USER':
      return {
        ...state,
        user: action.payload,
        isAuthenticated: !!action.payload,
      };

    case 'SET_SELLERS':
      return { ...state, sellers: action.payload };

    case 'ADD_SELLER':
      return { ...state, sellers: [...state.sellers, action.payload] };

    case 'UPDATE_SELLER':
      return {
        ...state,
        sellers: state.sellers.map((s) =>
          s.id === action.payload.id ? action.payload : s
        ),
      };

    case 'DELETE_SELLER':
      return {
        ...state,
        sellers: state.sellers.filter((s) => s.id !== action.payload),
      };

    case 'SET_TEAMS':
      return { ...state, teams: action.payload };

    case 'ADD_TEAM':
      return { ...state, teams: [...state.teams, action.payload] };

    case 'UPDATE_TEAM':
      return {
        ...state,
        teams: state.teams.map((t) =>
          t.id === action.payload.id ? action.payload : t
        ),
      };

    case 'DELETE_TEAM':
      return {
        ...state,
        teams: state.teams.filter((t) => t.id !== action.payload),
      };

    case 'SET_SEASONS':
      return { ...state, seasons: action.payload };

    case 'SET_ACTIVE_SEASON':
      return { ...state, activeSeason: action.payload };

    case 'SET_RANKING':
      return { ...state, ranking: action.payload };

    case 'SET_CONFIG':
      return { ...state, config: action.payload };

    case 'SET_RANKING_MODE':
      return { ...state, rankingMode: action.payload };

    case 'SET_LOADING':
      return { ...state, loading: action.payload };

    case 'SET_ERROR':
      return { ...state, error: action.payload };

    case 'LOGOUT':
      return {
        user: null,
        isAuthenticated: false,
        sellers: [],
        teams: [],
        seasons: [],
        activeSeason: null,
        ranking: null,
        config: null,
        loading: false,
        error: null,
        rankingMode: 'general',
      };

    default:
      return state;
  }
}

// ============================================
// CONTEXT
// ============================================

const AppContext = React.createContext(undefined);

// ============================================
// PROVIDER
// ============================================

export function AppStoreProvider({ children }) {
  const [state, dispatch] = useReducer(appReducer, initialState);

  // Helper para verificar permissÃƒÆ’Ã‚Âµes
  const checkPermission = (permission) => {
    if (!state.user) return false;
    return hasPermission(state.user.role, permission);
  };

  // Helper para verificar acesso (com erro se nÃƒÆ’Ã‚Â£o tiver permissÃƒÆ’Ã‚Â£o)
  const canAccess = (permission) => {
    const hasAccess = checkPermission(permission);
    if (!hasAccess) {
      dispatch({
        type: 'SET_ERROR',
        payload: 'VocÃƒÆ’Ã‚Âª nÃƒÆ’Ã‚Â£o tem permissÃƒÆ’Ã‚Â£o para acessar este recurso',
      });
    }
    return hasAccess;
  };

  // Helper para verificar se ÃƒÆ’Ã‚Â© ADMIN
  const isAdmin = () => {
    return state.user?.role === UserRole.ADMIN;
  };

  // Helper para verificar se ÃƒÆ’Ã‚Â© SUPERVISOR
  const isSupervisor = () => {
    return state.user?.role === UserRole.SUPERVISOR;
  };

  // Helper para obter adminId do usuÃƒÆ’Ã‚Â¡rio atual
  const getAdminId = () => {
    return state.user?.adminId;
  };

  // Helper para verificar se pode acessar equipe (baseado em adminId e equipes do supervisor)
  const canAccessTeam = (team) => {
    if (!state.user || !team) return false;
    
    // ADMIN pode acessar todas as equipes do seu adminId
    if (isAdmin()) {
      return team.adminId === state.user.adminId;
    }
    
    // SUPERVISOR sÃƒÆ’Ã‚Â³ pode acessar equipes vinculadas a ele
    if (isSupervisor()) {
      return team.adminId === state.user.adminId && 
             state.user.equipes?.includes(team.id);
    }
    
    return false;
  };

  // Login
  const login = async (email, password) => {
    const result = await authService.login(email, password);
    if (result.success) {
      dispatch({ type: 'SET_USER', payload: result.user });
    }
    return result;
  };

  // Logout
  const logout = async () => {
    await authService.logout();
    dispatch({ type: 'LOGOUT' });
  };

  // Verificar autenticaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o atual
  const checkAuth = async () => {
    try {
      const user = await authService.getCurrentUser();
      if (user) {
        dispatch({ type: 'SET_USER', payload: user });
        return true;
      }
      dispatch({ type: 'SET_USER', payload: null });
      return false;
    } catch (error) {
      dispatch({ type: 'SET_USER', payload: null });
      return false;
    }
  };

  const value = {
    state,
    dispatch,
    hasPermission: checkPermission,
    canAccess,
    isAdmin,
    isSupervisor,
    getAdminId,
    canAccessTeam,
    login,
    logout,
    checkAuth,
  };

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

// ============================================
// HOOK
// ============================================

export function useAppStore() {
  const context = useContext(AppContext);
  if (context === undefined) {
    throw new Error('useAppStore must be used within AppStoreProvider');
  }
  return context;
}