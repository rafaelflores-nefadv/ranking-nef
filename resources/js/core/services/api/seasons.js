import { api } from '../../api';

export class SeasonsService {
  async list() {
    try {
      return await api.get('/api/seasons');
    } catch (error) {
      throw error;
    }
  }

  async getById(id) {
    try {
      return await api.get(`/api/seasons/${id}`);
    } catch (error) {
      throw error;
    }
  }

  async create(payload) {
    try {
      return await api.post('/api/seasons', payload);
    } catch (error) {
      throw error;
    }
  }

  async update(id, payload) {
    try {
      return await api.put(`/api/seasons/${id}`, payload);
    } catch (error) {
      throw error;
    }
  }

  async delete(id) {
    try {
      return await api.delete(`/api/seasons/${id}`);
    } catch (error) {
      throw error;
    }
  }
}

export const seasonsService = new SeasonsService();
