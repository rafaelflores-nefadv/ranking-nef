import { api } from '../../api';

export class ScoreRulesService {
  async list() {
    try {
      return await api.get('/api/score-rules');
    } catch (error) {
      throw error;
    }
  }

  async getById(id) {
    try {
      return await api.get(`/api/score-rules/${id}`);
    } catch (error) {
      throw error;
    }
  }

  async create(payload) {
    try {
      return await api.post('/api/score-rules', payload);
    } catch (error) {
      throw error;
    }
  }

  async update(id, payload) {
    try {
      return await api.put(`/api/score-rules/${id}`, payload);
    } catch (error) {
      throw error;
    }
  }

  async delete(id) {
    try {
      return await api.delete(`/api/score-rules/${id}`);
    } catch (error) {
      throw error;
    }
  }
}

export const scoreRulesService = new ScoreRulesService();
