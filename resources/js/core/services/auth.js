/**
 * Serviço de autenticação
 * Integrado com Laravel Breeze (sessão + cookies)
 */

import { api } from '../api.js';

export class AuthService {
  /**
   * Login de usuário
   */
  async login(email, password) {
    try {
      // Fazer login via Laravel Breeze
      const response = await api.post('/login', {
        email,
        password,
      });

      // Se login bem-sucedido, buscar dados do usuário
      if (response) {
        const user = await this.getCurrentUser();
        if (user) {
          this.setSession(user);
          return { success: true, user };
        }
      }

      return { success: false, error: 'Erro ao realizar login' };
    } catch (error) {
      return { 
        success: false, 
        error: error.message || 'E-mail ou senha incorretos' 
      };
    }
  }

  /**
   * Logout
   */
  async logout() {
    try {
      await api.post('/logout');
    } catch (error) {
      // Ignorar erros no logout
    } finally {
      this.clearSession();
    }
  }

  /**
   * Obter usuário atual da sessão
   */
  async getCurrentUser() {
    try {
      const user = await api.get('/api/me');
      return user;
    } catch (error) {
      return null;
    }
  }

  /**
   * Obter usuário da sessão local (cache)
   */
  getSession() {
    if (typeof window === 'undefined') return null;
    
    try {
      const stored = sessionStorage.getItem('ranking_auth');
      if (!stored) return null;
      
      const session = JSON.parse(stored);
      // Verificar se não expirou (1 hora)
      if (Date.now() - session.timestamp > 3600000) {
        this.clearSession();
        return null;
      }
      return session.user;
    } catch (e) {
      return null;
    }
  }

  /**
   * Definir sessão
   */
  setSession(user) {
    if (typeof window !== 'undefined') {
      sessionStorage.setItem('ranking_auth', JSON.stringify({
        user,
        timestamp: Date.now(),
      }));
    }
  }

  /**
   * Limpar sessão
   */
  clearSession() {
    if (typeof window !== 'undefined') {
      sessionStorage.removeItem('ranking_auth');
    }
  }

  /**
   * Verificar se está autenticado
   */
  isAuthenticated() {
    return !!this.getSession();
  }

  /**
   * Atualizar dados do usuário (após mudanças)
   */
  async refreshUser() {
    const user = await this.getCurrentUser();
    if (user) {
      this.setSession(user);
      return user;
    }
    return null;
  }
}

export const authService = new AuthService();
