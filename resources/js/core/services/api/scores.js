import { getApiClient } from './client';

export class ScoresService {
  async updateScore(payload) {
    try {
      const client = getApiClient();
      return client.post('/api/scores/update', {
        entity_id: payload.entity_id,
        points: payload.points,
        source: payload.source,
        timestamp: payload.timestamp || new Date().toISOString(),
        metadata: payload.metadata,
      });
    } catch (error) {
      return { success: false, error: 'API Client not initialized' };
    }
  }

  async getScoreHistory(entityId, limit = 50) {
    try {
      const client = getApiClient();
      return client.get(`/api/scores/history/${entityId}`, { limit });
    } catch (error) {
      return { success: false, error: 'API Client not initialized' };
    }
  }

  async getCurrentScore(entityId) {
    try {
      const client = getApiClient();
      return client.get(`/api/scores/current/${entityId}`);
    } catch (error) {
      return { success: false, error: 'API Client not initialized' };
    }
  }

  async getMultipleScores(entityIds) {
    try {
      const client = getApiClient();
      return client.post('/api/scores/batch', { entity_ids: entityIds });
    } catch (error) {
      return { success: false, error: 'API Client not initialized' };
    }
  }
}

export const scoresService = new ScoresService();

