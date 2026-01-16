/**
 * Serviço de equipes
 * Gerencia CRUD de equipes
 */

import { getApiClient } from './client';
import type { Team, ApiResponse, PaginatedResponse } from '../../types';

export interface CreateTeamPayload {
  name: string;
  logo?: string;
  color?: string;
  memberIds?: string[];
  scoringType?: 'sum' | 'average' | 'max';
  sortOrder?: 'asc' | 'desc';
  metadata?: Record<string, any>;
}

export interface UpdateTeamPayload {
  name?: string;
  logo?: string;
  color?: string;
  memberIds?: string[];
  scoringType?: 'sum' | 'average' | 'max';
  sortOrder?: 'asc' | 'desc';
  metadata?: Record<string, any>;
}

export class TeamsService {
  /**
   * Listar equipes
   */
  async list(params?: {
    page?: number;
    pageSize?: number;
    sortBy?: string;
  }): Promise<ApiResponse<PaginatedResponse<Team>>> {
    const client = getApiClient();
    return client.get<PaginatedResponse<Team>>('/api/teams', params);
  }

  /**
   * Buscar equipe por ID
   */
  async getById(id: string): Promise<ApiResponse<Team>> {
    const client = getApiClient();
    return client.get<Team>(`/api/teams/${id}`);
  }

  /**
   * Criar equipe
   */
  async create(payload: CreateTeamPayload): Promise<ApiResponse<Team>> {
    const client = getApiClient();
    return client.post<Team>('/api/teams', payload);
  }

  /**
   * Atualizar equipe
   */
  async update(id: string, payload: UpdateTeamPayload): Promise<ApiResponse<Team>> {
    const client = getApiClient();
    return client.put<Team>(`/api/teams/${id}`, payload);
  }

  /**
   * Deletar equipe
   */
  async delete(id: string): Promise<ApiResponse<void>> {
    const client = getApiClient();
    return client.delete<void>(`/api/teams/${id}`);
  }

  /**
   * Buscar ranking de equipes
   */
  async getRanking(params?: {
    seasonId?: string;
    limit?: number;
  }): Promise<ApiResponse<Team[]>> {
    const client = getApiClient();
    return client.get<Team[]>('/api/teams/ranking', params);
  }
}

export const teamsService = new TeamsService();

