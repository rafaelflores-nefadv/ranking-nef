// Configuração central de rotas (camada de "roteamento" da arquitetura MVC)
// Aqui definimos as páginas (Views) e metadados de navegação apenas com JavaScript.

import Dashboard from '@/pages/Dashboard';
import Sellers from '@/pages/Sellers';
import Teams from '@/pages/Teams';
import Settings from '@/pages/Settings';
import Login from '@/pages/Login';
import Register from '@/pages/Register';

export const routes = [
  {
    name: 'Home',
    label: 'Dashboard',
    path: '/',
    element: Dashboard,
    protected: true,
  },
  {
    name: 'Sellers',
    label: 'Vendedores',
    path: '/sellers',
    element: Sellers,
    protected: true,
  },
  {
    name: 'Teams',
    label: 'Equipes',
    path: '/teams',
    element: Teams,
    protected: true,
  },
  {
    name: 'Settings',
    label: 'Configurações',
    path: '/settings',
    element: Settings,
    protected: true,
  },
  {
    name: 'Login',
    label: 'Login',
    path: '/login',
    element: Login,
    protected: false,
  },
  {
    name: 'Register',
    label: 'Cadastro',
    path: '/register',
    element: Register,
    protected: false,
  },
];


