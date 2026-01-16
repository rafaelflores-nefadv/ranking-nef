/**
 * Cliente HTTP independente para comunicação com API
 */

export class ApiClient {
  constructor(config) {
    this.config = {
      baseUrl: config.baseUrl || '',
      apiKey: config.apiKey || '',
      timeout: config.timeout || 30000,
      headers: {
        'Content-Type': 'application/json',
        ...config.headers,
      },
    };

    if (this.config.apiKey) {
      this.config.headers['Authorization'] = `Bearer ${this.config.apiKey}`;
    }
  }

  async get(endpoint, params) {
    const url = this.buildUrl(endpoint, params);
    
    try {
      const response = await this.fetchWithTimeout(url, {
        method: 'GET',
        headers: this.config.headers,
      });

      return await this.handleResponse(response);
    } catch (error) {
      return this.handleError(error);
    }
  }

  async post(endpoint, data) {
    try {
      const response = await this.fetchWithTimeout(`${this.config.baseUrl}${endpoint}`, {
        method: 'POST',
        headers: this.config.headers,
        body: JSON.stringify(data),
      });

      return await this.handleResponse(response);
    } catch (error) {
      return this.handleError(error);
    }
  }

  async put(endpoint, data) {
    try {
      const response = await this.fetchWithTimeout(`${this.config.baseUrl}${endpoint}`, {
        method: 'PUT',
        headers: this.config.headers,
        body: JSON.stringify(data),
      });

      return await this.handleResponse(response);
    } catch (error) {
      return this.handleError(error);
    }
  }

  async delete(endpoint) {
    try {
      const response = await this.fetchWithTimeout(`${this.config.baseUrl}${endpoint}`, {
        method: 'DELETE',
        headers: this.config.headers,
      });

      return await this.handleResponse(response);
    } catch (error) {
      return this.handleError(error);
    }
  }

  buildUrl(endpoint, params) {
    const url = new URL(endpoint, this.config.baseUrl);
    
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          url.searchParams.append(key, String(value));
        }
      });
    }

    return url.toString();
  }

  async fetchWithTimeout(url, options) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);

    try {
      const response = await fetch(url, {
        ...options,
        signal: controller.signal,
      });
      clearTimeout(timeoutId);
      return response;
    } catch (error) {
      clearTimeout(timeoutId);
      throw error;
    }
  }

  async handleResponse(response) {
    const contentType = response.headers.get('content-type');
    const isJson = contentType?.includes('application/json');

    if (!response.ok) {
      const errorData = isJson ? await response.json() : { message: response.statusText };
      return {
        success: false,
        error: errorData.message || `HTTP ${response.status}`,
      };
    }

    if (isJson) {
      const data = await response.json();
      return {
        success: true,
        data: data,
      };
    }

    return {
      success: true,
      data: undefined,
    };
  }

  handleError(error) {
    if (error.name === 'AbortError') {
      return {
        success: false,
        error: 'Request timeout',
      };
    }

    return {
      success: false,
      error: error.message || 'Unknown error',
    };
  }
}

let apiClientInstance = null;

export function createApiClient(config) {
  apiClientInstance = new ApiClient(config);
  return apiClientInstance;
}

export function getApiClient() {
  if (!apiClientInstance) {
    throw new Error('API Client not initialized. Call createApiClient first.');
  }
  return apiClientInstance;
}

