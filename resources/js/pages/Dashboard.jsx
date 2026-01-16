import React, { useState } from "react";
import { motion } from "framer-motion";
import { useNavigate } from "react-router-dom";
import {
  Trophy,
  UserPlus,
  Plus,
  ChevronLeft,
  Bell,
  MessageCircle,
  User
} from "lucide-react";

/* COMPONENTES VISUAIS */
import GamePodium from "@/components/ranking/GamePodium";
import RankingSidebar from "@/components/ranking/RankingSidebar";
import GameHeader from "@/components/ranking/GameHeader";
import TimeControls from "@/components/ranking/TimeControls";

/* CONTROLLER E ESTADO GLOBAL */
import { useDashboardController } from "@/controllers/useDashboardController";

export default function Dashboard() {
  const navigate = useNavigate();
  const [showSellerForm, setShowSellerForm] = useState(false);
  const [showSaleForm, setShowSaleForm] = useState(false);

  // Usa o controller para acessar estado global e dados
  const {
    state,
    stats,
    ranking,
    top3,
    isLoading,
  } = useDashboardController();

  // Converter dados do ranking para formato esperado pelos componentes visuais
  const sellers = (ranking?.entries || []).map((entry) => ({
    id: entry.id,
    name: entry.name,
    total_sales: entry.points || 0,
    sales_count: Math.floor((entry.points || 0) / 1000) || 0,
    avatar_url: entry.avatar,
    change: entry.change,
  }));

  const topThree = (top3 || []).map((entry) => ({
    id: entry.id,
    name: entry.name,
    total_sales: entry.points || 0,
    sales_count: Math.floor((entry.points || 0) / 1000) || 0,
    avatar_url: entry.avatar,
  }));

  // Adaptar stats para formato esperado pelo GameHeader
  const adaptedStats = {
    totalSales: stats?.totalPoints || 0,
    totalCount: stats?.totalParticipants || 0,
    sellersCount: stats?.totalParticipants || 0,
  };

  // Se estiver carregando, mostrar loading
  if (isLoading) {
    return (
      <div className="min-h-screen bg-[#0a0e1a] flex items-center justify-center">
        <div className="text-center">
          <motion.div
            animate={{ rotate: 360 }}
            transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
            className="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"
          />
          <p className="text-slate-400">Carregando dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#0a0e1a] relative overflow-hidden">
      {/* BACKGROUND ANIMADO */}
      <div className="fixed inset-0 pointer-events-none">
        <div className="absolute inset-0 bg-gradient-to-br from-blue-950/20 via-slate-950 to-purple-950/20" />

        <motion.div
          animate={{ scale: [1, 1.2, 1], opacity: [0.1, 0.2, 0.1] }}
          transition={{ duration: 8, repeat: Infinity }}
          className="absolute top-1/4 -left-32 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl"
        />

        <motion.div
          animate={{ scale: [1.2, 1, 1.2], opacity: [0.15, 0.25, 0.15] }}
          transition={{ duration: 10, repeat: Infinity }}
          className="absolute bottom-1/4 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl"
        />
      </div>

      {/* HEADER SUPERIOR */}
      <GameHeader stats={adaptedStats} />

      <div className="relative z-10">
        {/* BARRA SUPERIOR */}
        <div className="flex items-center justify-between px-6 py-4">
          <div className="flex items-center gap-4">
            <button className="p-2 hover:bg-slate-800/50 rounded-lg">
              <ChevronLeft className="w-6 h-6 text-white" />
            </button>

            <div className="flex items-center gap-3 px-4 py-2 bg-slate-900/60 rounded-xl border border-slate-700/50">
              <div className="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                <Trophy className="w-5 h-5 text-white" />
              </div>
              <div>
                <h2 className="text-white font-bold">Ranking de vendas</h2>
                <p className="text-slate-400 text-xs">Por pontuação</p>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => navigate('/sellers')}
              className="flex items-center gap-2 px-3 py-2 bg-slate-800/50 border border-slate-700 text-white rounded-lg hover:bg-slate-700"
            >
              <UserPlus className="w-4 h-4" />
              Vendedor
            </button>

            <button
              onClick={() => setShowSaleForm(true)}
              className="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg hover:from-blue-700 hover:to-blue-600"
            >
              <Plus className="w-4 h-4" />
              Nova Venda
            </button>

            <button className="p-2 hover:bg-slate-800/50 rounded-lg">
              <Bell className="w-5 h-5 text-white" />
            </button>
            <button className="p-2 hover:bg-slate-800/50 rounded-lg">
              <MessageCircle className="w-5 h-5 text-white" />
            </button>
            <button className="p-2 hover:bg-slate-800/50 rounded-lg">
              <User className="w-5 h-5 text-white" />
            </button>
          </div>
        </div>

        {/* GRID PRINCIPAL */}
        <div className="grid grid-cols-12 gap-6 px-6 py-4">
          {/* SIDEBAR ESQUERDA */}
          <div className="col-span-3">
            <div className="bg-slate-900/40 backdrop-blur-sm rounded-2xl p-4 border border-slate-700/50 max-h-[600px] overflow-y-auto">
              <h3 className="text-white font-semibold mb-4 text-sm">
                Classificação Geral
              </h3>
              {sellers.length > 0 ? (
                <RankingSidebar sellers={sellers} />
              ) : (
                <div className="text-center py-8 text-slate-400">
                  <p className="text-sm">Nenhum participante encontrado</p>
                </div>
              )}
            </div>
          </div>

          {/* PÓDIO */}
          <div className="col-span-6 flex items-center justify-center">
            {topThree.length > 0 ? (
              <GamePodium topThree={topThree} />
            ) : (
              <div className="text-center py-16 text-slate-400">
                <Trophy className="w-16 h-16 mx-auto mb-4 opacity-30" />
                <p className="text-lg">Nenhum participante ainda</p>
                <p className="text-sm mt-2">Comece cadastrando participantes</p>
              </div>
            )}
          </div>

          {/* CONTROLES */}
          <div className="col-span-3 flex items-center justify-center">
            <TimeControls />
          </div>
        </div>
      </div>
    </div>
  );
}
