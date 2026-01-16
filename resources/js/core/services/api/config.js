import { getApiClient } from './client';

export class ConfigService {
  async get() {
    try {
      const client = getApiClient();
      return client.get('/api/config');
    } catch (error) {
      return { success: false, error: 'API Client not initialized' };
    }
  }

  async update(payload) {
    try {
      const client = getApiClient();
      return client.put('/api/config', payload);
    } catch (error) {
      return { success: false, error: 'API Client not initialized' };
    }
  }
}

export const configService = new ConfigService();

