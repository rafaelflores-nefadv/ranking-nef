/**
 * Dados mock para desenvolvimento
 */

import { UserRole } from '../types';

// Usuário mock padrão
export const mockUser = {
  id: '1',
  name: 'Administrador',
  email: 'admin@example.com',
  role: UserRole.ADMIN,
  permissions: [],
  createdAt: new Date().toISOString(),
  updatedAt: new Date().toISOString(),
};

// Vendedores mock
export const mockSellers = [
  {
    id: '1',
    name: 'João Silva',
    email: 'joao@example.com',
    points: 1250,
    position: 1,
    status: 'active',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
  {
    id: '2',
    name: 'Maria Santos',
    email: 'maria@example.com',
    points: 980,
    position: 2,
    status: 'active',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
  {
    id: '3',
    name: 'Pedro Oliveira',
    email: 'pedro@example.com',
    points: 850,
    position: 3,
    status: 'active',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
  {
    id: '4',
    name: 'Ana Costa',
    email: 'ana@example.com',
    points: 720,
    position: 4,
    status: 'active',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
  {
    id: '5',
    name: 'Carlos Ferreira',
    email: 'carlos@example.com',
    points: 650,
    position: 5,
    status: 'active',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
];

// Equipes mock
export const mockTeams = [
  {
    id: '1',
    name: 'Equipe Alpha',
    points: 2500,
    position: 1,
    memberIds: ['1', '2'],
    scoringType: 'sum',
    sortOrder: 'desc',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
  {
    id: '2',
    name: 'Equipe Beta',
    points: 1800,
    position: 2,
    memberIds: ['3', '4'],
    scoringType: 'sum',
    sortOrder: 'desc',
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  },
];

// Temporada mock
export const mockSeason = {
  id: '1',
  name: 'Temporada 2024 - Q1',
  startDate: new Date(2024, 0, 1).toISOString(),
  endDate: new Date(2024, 2, 31).toISOString(),
  isActive: true,
  createdAt: new Date().toISOString(),
  updatedAt: new Date().toISOString(),
};

// Configuração mock
export const mockConfig = {
  id: '1',
  name: 'Ranking de Vendas',
  primaryColor: '#3b82f6',
  secondaryColor: '#8b5cf6',
  notificationsSystemEnabled: true,
  notificationsEmailEnabled: true,
  soundsEnabled: true,
  voiceEnabled: false,
  animationsEnabled: true,
  realtimeEnabled: true,
  pollingInterval: 5000,
  updatedAt: new Date().toISOString(),
};

