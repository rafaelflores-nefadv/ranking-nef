import { BrowserRouter as Router, Route, Routes, Navigate } from 'react-router-dom';
import { AppNavigation } from '@/components/navigation/AppNavigation';
import { ProtectedRoute } from '@/components/auth/ProtectedRoute';
import { useAppStore } from '@/core/store/AppStore';
import { useInitialData } from '@/core/hooks/useInitialData';
import { routes } from '@/routes/config';
import { Toaster } from '@/components/ui/sonner';

function AppRoutes() {
  const { state } = useAppStore();
  const { isAuthenticated } = state;
  
  // Carregar dados iniciais quando autenticado
  useInitialData();

  return (
    <>
      <Toaster />
      <div className="min-h-screen bg-[#0a0e1a]">
        <Routes>
          {routes.map((route) => {
            const Element = route.element;
            const isAuthRoute = route.path === '/login' || route.path === '/register';
            
            if (isAuthRoute) {
              // Se já está autenticado, redirecionar para dashboard
              return (
                <Route
                  key={route.path}
                  path={route.path}
                  element={
                    isAuthenticated ? (
                      <Navigate to="/" replace />
                    ) : (
                      <Element />
                    )
                  }
                />
              );
            }
            
            // Rotas protegidas
            return (
              <Route
                key={route.path}
                path={route.path}
                element={
                  <ProtectedRoute requireAuth={route.protected !== false}>
                    <AppNavigation />
                    <main>
                      <Element />
                    </main>
                  </ProtectedRoute>
                }
              />
            );
          })}
          {/* Rota catch-all para redirecionar para login se não autenticado */}
          <Route
            path="*"
            element={
              <Navigate to="/login" replace />
            }
          />
        </Routes>
      </div>
    </>
  );
}

export default function Pages() {
  return (
    <Router>
      <AppRoutes />
    </Router>
  );
}
