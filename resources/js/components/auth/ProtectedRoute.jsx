import { useEffect, useState } from 'react';
import { Navigate } from 'react-router-dom';
import { useAppStore } from '@/core/store/AppStore';

export function ProtectedRoute({ children, requireAuth = true, allowedRoles = [] }) {
  const { state, checkAuth } = useAppStore();
  const { isAuthenticated, user } = state;
  const [checking, setChecking] = useState(true);

  useEffect(() => {
    const verifyAuth = async () => {
      if (requireAuth) {
        await checkAuth();
      }
      setChecking(false);
    };
    verifyAuth();
  }, [requireAuth]); // Removido checkAuth das dependências para evitar loop

  // Aguardar verificação
  if (checking && requireAuth) {
    return (
      <div className="min-h-screen bg-[#0a0e1a] flex items-center justify-center">
        <div className="text-slate-400">Carregando...</div>
      </div>
    );
  }

  // Se requer autenticação e usuário não está autenticado
  if (requireAuth && !isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  // Se tem roles permitidas e usuário não tem uma delas
  if (allowedRoles.length > 0 && user && !allowedRoles.includes(user.role)) {
    return <Navigate to="/" replace />;
  }

  return children;
}
