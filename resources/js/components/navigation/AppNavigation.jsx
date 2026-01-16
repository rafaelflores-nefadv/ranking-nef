import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  Trophy,
  Users,
  Shield,
  Settings,
  Bell,
  MessageCircle,
  User as UserIcon,
} from 'lucide-react';
import { useAppStore } from '@/core/store/AppStore';
import { Permission } from '@/core/types';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const navItems = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    path: '/',
    icon: Trophy,
    permission: Permission.VIEW_DASHBOARD,
  },
  {
    name: 'sellers',
    label: 'Vendedores',
    path: '/sellers',
    icon: Users,
    permission: Permission.VIEW_SELLERS,
  },
  {
    name: 'teams',
    label: 'Equipes',
    path: '/teams',
    icon: Shield,
    permission: Permission.VIEW_TEAMS,
  },
  {
    name: 'settings',
    label: 'Configurações',
    path: '/settings',
    icon: Settings,
    permission: Permission.VIEW_SETTINGS,
  },
];

export function AppNavigation() {
  const location = useLocation();
  const { state, hasPermission } = useAppStore();

  const visibleItems = navItems.filter((item) => {
    if (!item.permission) return true;
    return hasPermission(item.permission);
  });

  const currentPath = location.pathname;

  return (
    <nav className="bg-[#0d1117] border-b border-slate-800/50 backdrop-blur-md sticky top-0 z-50">
      <div className="px-6 py-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-6">
            <Link to="/" className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
                <Trophy className="w-5 h-5 text-white" />
              </div>
              <div>
                <h1 className="text-white font-bold text-sm">
                  {state.config?.name || 'Ranking de Vendas'}
                </h1>
                <p className="text-slate-400 text-xs">Sistema de pontuação</p>
              </div>
            </Link>

            <div className="flex items-center gap-1 ml-8">
              {visibleItems.map((item) => {
                const Icon = item.icon;
                const isActive = currentPath === item.path || 
                  (item.path !== '/' && currentPath.startsWith(item.path));

                return (
                  <Link key={item.name} to={item.path} className="relative">
                    <motion.div
                      whileHover={{ scale: 1.05 }}
                      whileTap={{ scale: 0.95 }}
                      className={cn(
                        'flex items-center gap-2 px-4 py-2 rounded-lg transition-all',
                        isActive
                          ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
                      )}
                    >
                      <Icon className="w-4 h-4" />
                      <span className="text-sm font-medium">{item.label}</span>
                    </motion.div>
                  </Link>
                );
              })}
            </div>
          </div>

          <div className="flex items-center gap-3">
            <Button
              variant="ghost"
              size="sm"
              className="p-2 hover:bg-slate-800 rounded-lg transition-colors relative"
            >
              <Bell className="w-5 h-5 text-slate-400 hover:text-white transition-colors" />
              <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </Button>

            <Button
              variant="ghost"
              size="sm"
              className="p-2 hover:bg-slate-800 rounded-lg transition-colors"
            >
              <MessageCircle className="w-5 h-5 text-slate-400 hover:text-white transition-colors" />
            </Button>

            <div className="h-6 w-px bg-slate-700"></div>

            <Button
              variant="ghost"
              className="flex items-center gap-2 px-3 py-2 hover:bg-slate-800 rounded-lg transition-colors"
            >
              <div className="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                {state.user?.avatar ? (
                  <img
                    src={state.user.avatar}
                    alt={state.user.name}
                    className="w-full h-full rounded-full"
                  />
                ) : (
                  <UserIcon className="w-4 h-4 text-white" />
                )}
              </div>
              <div className="text-left">
                <p className="text-white text-sm font-medium">
                  {state.user?.name || 'Usuário'}
                </p>
                <p className="text-slate-400 text-xs">
                  {state.user?.role === 'admin' ? 'Administrador' :
                   state.user?.role === 'manager' ? 'Gestor' : 'Usuário'}
                </p>
              </div>
            </Button>
          </div>
        </div>
      </div>
    </nav>
  );
}

