
import React from "react";
import { Link } from "react-router-dom";
import { createPageUrl } from "./utils";
import { Trophy, Users, Shield, Settings, Bell, MessageCircle, User as UserIcon } from "lucide-react";
import { motion } from "framer-motion";

export default function Layout({ children, currentPageName }) {
  const menuItems = [
    { name: "Home", label: "Dashboard", icon: Trophy },
    { name: "Sellers", label: "Vendedores", icon: Users },
    { name: "Teams", label: "Equipes", icon: Shield },
    { name: "Settings", label: "Configurações", icon: Settings },
  ];

  return (
    <div className="min-h-screen bg-[#0a0e1a]">
      {/* Top Navigation Bar */}
      <nav className="bg-[#0d1117] border-b border-slate-800/50 backdrop-blur-md sticky top-0 z-50">
        <div className="px-6 py-3">
          <div className="flex items-center justify-between">
            {/* Left - Logo and Menu */}
            <div className="flex items-center gap-6">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
                  <Trophy className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h1 className="text-white font-bold text-sm">Ranking de vendas</h1>
                  <p className="text-slate-400 text-xs">Sistema de pontuação</p>
                </div>
              </div>

              <div className="flex items-center gap-1 ml-8">
                {menuItems.map((item) => {
                  const Icon = item.icon;
                  const isActive = currentPageName === item.name;
                  
                  return (
                    <Link
                      key={item.name}
                      to={createPageUrl(item.name)}
                      className="relative"
                    >
                      <motion.div
                        whileHover={{ scale: 1.05 }}
                        whileTap={{ scale: 0.95 }}
                        className={`flex items-center gap-2 px-4 py-2 rounded-lg transition-all ${
                          isActive
                            ? "bg-blue-600 text-white shadow-lg shadow-blue-500/30"
                            : "text-slate-400 hover:text-white hover:bg-slate-800/50"
                        }`}
                      >
                        <Icon className="w-4 h-4" />
                        <span className="text-sm font-medium">{item.label}</span>
                      </motion.div>
                    </Link>
                  );
                })}
              </div>
            </div>

            {/* Right - Actions and User */}
            <div className="flex items-center gap-3">
              <button className="p-2 hover:bg-slate-800 rounded-lg transition-colors relative">
                <Bell className="w-5 h-5 text-slate-400 hover:text-white transition-colors" />
                <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
              </button>
              
              <button className="p-2 hover:bg-slate-800 rounded-lg transition-colors">
                <MessageCircle className="w-5 h-5 text-slate-400 hover:text-white transition-colors" />
              </button>
              
              <div className="h-6 w-px bg-slate-700"></div>
              
              <button className="flex items-center gap-2 px-3 py-2 hover:bg-slate-800 rounded-lg transition-colors">
                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-pink-600 flex items-center justify-center">
                  <UserIcon className="w-4 h-4 text-white" />
                </div>
                <div className="text-left">
                  <p className="text-white text-sm font-medium">Admin</p>
                  <p className="text-slate-400 text-xs">Administrador</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main>
        {children}
      </main>
    </div>
  );
}
