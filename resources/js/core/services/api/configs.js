import { api } from '../../api';

export class ConfigsService {
  async list() {
    try {
      return await api.get('/api/configs');
    } catch (error) {
      throw error;
    }
  }

  async getById(id) {
    try {
      return await api.get(`/api/configs/${id}`);
    } catch (error) {
      throw error;
    }
  }

  async create(payload) {
    try {
      return await api.post('/api/configs', payload);
    } catch (error) {
      throw error;
    }
  }

  async update(id, payload) {
    try {
      return await api.put(`/api/configs/${id}`, payload);
    } catch (error) {
      throw error;
    }
  }

  async delete(id) {
    try {
      return await api.delete(`/api/configs/${id}`);
    } catch (error) {
      throw error;
    }
  }
}

export const configsService = new ConfigsService();
