/**
 * Model: Auth
 * Regras de negócio e validações para Autenticação
 */

/**
 * Valida dados de cadastro de administrador
 */
export function validateAdminRegister(data) {
  const errors = [];

  if (!data.nome || data.nome.trim().length === 0) {
    errors.push('Nome é obrigatório');
  }

  if (!data.empresa || data.empresa.trim().length === 0) {
    errors.push('Nome da empresa é obrigatório');
  }

  if (!data.email || data.email.trim().length === 0) {
    errors.push('E-mail é obrigatório');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.push('E-mail inválido');
  }

  if (!data.senha || data.senha.length < 6) {
    errors.push('Senha deve ter no mínimo 6 caracteres');
  }

  if (data.senha !== data.confirmarSenha) {
    errors.push('As senhas não coincidem');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * Valida dados de login
 */
export function validateLogin(data) {
  const errors = [];

  if (!data.email || data.email.trim().length === 0) {
    errors.push('E-mail é obrigatório');
  }

  if (!data.senha || data.senha.length === 0) {
    errors.push('Senha é obrigatória');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * Normaliza dados de administrador
 */
export function normalizeAdmin(data) {
  return {
    id: data.id,
    nome: data.nome?.trim() || '',
    email: data.email?.trim().toLowerCase() || '',
    empresa: data.empresa?.trim() || '',
    senha: data.senha,
    role: 'admin',
    createdAt: data.createdAt || new Date().toISOString(),
    updatedAt: data.updatedAt || new Date().toISOString(),
  };
}

