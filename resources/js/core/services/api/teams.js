import { api } from '../../api';

export class TeamsService {
  async list() {
    try {
      return await api.get('/api/teams');
    } catch (error) {
      throw error;
    }
  }

  async getById(id) {
    try {
      return await api.get(`/api/teams/${id}`);
    } catch (error) {
      throw error;
    }
  }

  async create(payload) {
    try {
      return await api.post('/api/teams', payload);
    } catch (error) {
      throw error;
    }
  }

  async update(id, payload) {
    try {
      return await api.put(`/api/teams/${id}`, payload);
    } catch (error) {
      throw error;
    }
  }

  async delete(id) {
    try {
      return await api.delete(`/api/teams/${id}`);
    } catch (error) {
      throw error;
    }
  }
}

export const teamsService = new TeamsService();
