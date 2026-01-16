/**
 * Service HTTP central para comunicação com a API Laravel
 * Usa autenticação por sessão (cookies)
 */

// Obter CSRF token do meta tag
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : null;
}

export async function apiFetch(url, options = {}) {
  const csrfToken = getCsrfToken();
  
  const defaultOptions = {
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
      ...options.headers,
    },
  };

  // Se tiver body e não for FormData, stringify
  if (options.body && !(options.body instanceof FormData)) {
    if (typeof options.body === 'object') {
      defaultOptions.body = JSON.stringify(options.body);
    }
  } else if (options.body) {
    defaultOptions.body = options.body;
    // Remove Content-Type para FormData (browser define automaticamente)
    delete defaultOptions.headers['Content-Type'];
  }

  try {
    const response = await fetch(url, {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...options.headers,
      },
    });

    // Tratar 401 - Não autenticado
    if (response.status === 401) {
      window.location.href = '/login';
      throw new Error('Não autenticado');
    }

    // Tratar 403 - Acesso negado
    if (response.status === 403) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || 'Acesso negado');
    }

    // Tratar 422 - Erros de validação
    if (response.status === 422) {
      const errorData = await response.json().catch(() => ({}));
      const errors = errorData.errors || {};
      const firstError = Object.values(errors).flat()[0] || errorData.message || 'Erro de validação';
      throw new Error(firstError);
    }

    // Tratar 500 - Erro do servidor
    if (response.status >= 500) {
      throw new Error('Erro interno do servidor. Tente novamente mais tarde.');
    }

    // Se não tiver conteúdo, retornar sucesso
    if (response.status === 204 || response.headers.get('content-length') === '0') {
      return null;
    }

    // Tentar parsear JSON
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      const data = await response.json();
      return data;
    }

    // Se não for JSON, retornar texto
    return await response.text();
  } catch (error) {
    // Se já é um Error, relançar
    if (error instanceof Error) {
      throw error;
    }
    // Caso contrário, criar novo Error
    throw new Error(error.message || 'Erro ao realizar requisição');
  }
}

// Helpers para métodos HTTP
export const api = {
  get: (url, options = {}) => apiFetch(url, { ...options, method: 'GET' }),
  post: (url, data, options = {}) => apiFetch(url, { ...options, method: 'POST', body: data }),
  put: (url, data, options = {}) => apiFetch(url, { ...options, method: 'PUT', body: data }),
  delete: (url, options = {}) => apiFetch(url, { ...options, method: 'DELETE' }),
};
