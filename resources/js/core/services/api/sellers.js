import { api } from '../../api';

export class SellersService {
  async list() {
    try {
      return await api.get('/api/sellers');
    } catch (error) {
      throw error;
    }
  }

  async getById(id) {
    try {
      return await api.get(`/api/sellers/${id}`);
    } catch (error) {
      throw error;
    }
  }

  async create(payload) {
    try {
      return await api.post('/api/sellers', payload);
    } catch (error) {
      throw error;
    }
  }

  async update(id, payload) {
    try {
      return await api.put(`/api/sellers/${id}`, payload);
    } catch (error) {
      throw error;
    }
  }

  async delete(id) {
    try {
      return await api.delete(`/api/sellers/${id}`);
    } catch (error) {
      throw error;
    }
  }
}

export const sellersService = new SellersService();
