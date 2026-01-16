/**
 * View: Página de Equipes
 * Responsável apenas pela renderização visual (JSX)
 * Toda a lógica de negócio está no Controller (useTeamsController)
 */

import React from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Search, Plus, Edit, Trash2, Users, Trophy } from "lucide-react";
import TeamFormModal from "@/components/modals/TeamFormModal.jsx";
import { useTeamsController } from "@/controllers/useTeamsController";

export default function Teams() {
  // Usa o controller para toda a lógica de negócio
  const {
    teams,
    isLoading,
    searchTerm,
    setSearchTerm,
    showTeamForm,
    setShowTeamForm,
    editingTeam,
    handleEdit,
    handleDelete,
    handleCloseForm,
  } = useTeamsController();

  return (
    <div className="min-h-screen bg-[#0a0e1a] p-6">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-8"
        >
          <h1 className="text-3xl font-bold text-white mb-2">Times</h1>
          <p className="text-slate-400">Gerencie as equipes da competição</p>
        </motion.div>

        {/* Search and Actions */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="flex flex-col md:flex-row gap-4 mb-6"
        >
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
            <Input
              type="text"
              placeholder="Pesquisar"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10 bg-slate-900/60 border-slate-700 text-white placeholder:text-slate-500"
            />
          </div>
          
          <Button
            onClick={() => setShowTeamForm(true)}
            className="bg-blue-600 hover:bg-blue-700"
          >
            <Plus className="w-4 h-4 mr-2" />
            Criar time
          </Button>
        </motion.div>

        {/* Teams Grid */}
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.2 }}
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
          <AnimatePresence>
            {isLoading ? (
              <div className="col-span-full text-center py-12 text-slate-400">
                Carregando times...
              </div>
            ) : teams.length === 0 ? (
              <div className="col-span-full text-center py-12 text-slate-400">
                <Plus className="w-16 h-16 mx-auto mb-4 opacity-30" />
                <p>Nenhum time encontrado</p>
              </div>
            ) : (
              teams.map((team, index) => (
                <motion.div
                  key={team.id}
                  initial={{ opacity: 0, scale: 0.9 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.9 }}
                  transition={{ delay: index * 0.05 }}
                  className="relative bg-[#0d1525] backdrop-blur-sm rounded-2xl p-6 border border-slate-800/50 hover:border-blue-500/50 transition-all group"
                >
                  {/* Delete button */}
                  <button
                    onClick={() => handleDelete(team)}
                    className="absolute top-4 right-4 p-2 bg-red-600/10 hover:bg-red-600/20 rounded-lg opacity-0 group-hover:opacity-100 transition-all"
                  >
                    <Trash2 className="w-4 h-4 text-red-400" />
                  </button>

                  {/* Team Info */}
                  <div className="flex flex-col items-center text-center mb-4">
                    <div className="relative mb-4">
                      <motion.div
                        className="relative w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center overflow-hidden border-2 border-slate-700"
                        whileHover={{ scale: 1.1, rotate: 5 }}
                        transition={{ duration: 0.2 }}
                      >
                        {team.logo_url ? (
                          <img src={team.logo_url} alt={team.name} className="w-full h-full object-cover" />
                        ) : (
                          <span className="text-2xl font-bold text-white">
                            {team.name?.charAt(0)?.toUpperCase()}
                          </span>
                        )}
                        {/* Indicador de pontos totais (gamificação) */}
                        {team.totalPoints > 0 && (
                          <motion.div
                            className="absolute -top-2 -right-2 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full px-2 py-0.5 flex items-center gap-1 shadow-lg border-2 border-slate-900"
                            initial={{ opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                          >
                            <Trophy className="w-3 h-3 text-slate-900" />
                            <span className="text-[10px] font-bold text-slate-900">
                              {team.totalPoints.toLocaleString('pt-BR')}
                            </span>
                          </motion.div>
                        )}
                      </motion.div>
                    </div>

                    <div className="mb-4">
                      <div className="flex items-center gap-2 justify-center mb-2">
                        <h3 className="text-white font-bold text-xl">{team.code || "—"}</h3>
                      </div>
                      <p className="text-slate-400 text-sm mb-1">{team.name}</p>
                      <p className="text-blue-400 text-xs">Por Unidade</p>
                    </div>

                    <div className="flex items-center gap-4 text-center w-full justify-between px-4">
                      <div>
                        <p className="text-slate-400 text-xs mb-1">
                          {team.created_month || "Junho"}
                        </p>
                        <p className="text-white font-semibold">{team.created_year || "2025"}</p>
                      </div>
                      
                      <div className="h-8 w-px bg-slate-700"></div>
                      
                      <div className="flex items-center gap-2">
                        <Users className="w-4 h-4 text-blue-400" />
                        <div>
                          <p className="text-slate-400 text-xs mb-1">Membros</p>
                          <p className="text-white font-semibold">{team.memberCount || team.member_count || 0}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Edit button */}
                  <button
                    onClick={() => handleEdit(team)}
                    className="w-full py-2 bg-blue-600/10 hover:bg-blue-600/20 rounded-lg transition-colors flex items-center justify-center gap-2"
                  >
                    <Edit className="w-4 h-4 text-blue-400" />
                    <span className="text-blue-400 text-sm font-medium">Editar</span>
                  </button>
                </motion.div>
              ))
            )}
          </AnimatePresence>
        </motion.div>
      </div>

      {/* Modal */}
      <TeamFormModal
        open={showTeamForm}
        onClose={handleCloseForm}
        team={editingTeam}
      />
    </div>
  );
}