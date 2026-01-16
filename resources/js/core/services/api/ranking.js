import { api } from '../../api';

export class RankingService {
  async getRanking(params = {}) {
    try {
      const queryString = new URLSearchParams(params).toString();
      const url = queryString ? `/api/ranking?${queryString}` : '/api/ranking';
      return await api.get(url);
    } catch (error) {
      throw error;
    }
  }
}

export const rankingService = new RankingService();
