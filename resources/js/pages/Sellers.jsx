/**
 * View: Página de Vendedores
 * Responsável apenas pela renderização visual (JSX)
 * Toda a lógica de negócio está no Controller (useSellersController)
 */

import React from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Search, UserPlus, Upload, Edit, Trash2, Sparkles } from "lucide-react";
import SellerFormModal from "@/components/modals/SellerFormModal.jsx";
import ImportSellersModal from "@/components/modals/ImportSellersModal.jsx";
import { useSellersController } from "@/controllers/useSellersController";
import { cn } from "@/lib/utils";

export default function Sellers() {
  // Usa o controller para toda a lógica de negócio
  const {
    sellers,
    teams,
    isLoading,
    searchTerm,
    setSearchTerm,
    showSellerForm,
    setShowSellerForm,
    showImport,
    setShowImport,
    editingSeller,
    handleEdit,
    handleDelete,
    handleCloseForm,
    getTeamName,
    getAccessLevelLabel,
  } = useSellersController();

  // Função auxiliar para cores de nível (gamificação visual)
  const getLevelColor = (level) => {
    if (level >= 5) return 'from-amber-400 to-amber-600 border-amber-500/50';
    if (level >= 4) return 'from-purple-400 to-purple-700 border-purple-500/50';
    if (level >= 3) return 'from-blue-400 to-blue-700 border-blue-500/50';
    if (level >= 2) return 'from-emerald-400 to-emerald-700 border-emerald-500/50';
    return 'from-slate-600 to-slate-800 border-slate-500/50';
  };

  return (
    <div className="min-h-screen bg-[#0a0e1a] p-6">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-8"
        >
          <h1 className="text-3xl font-bold text-white mb-2">Vendedores</h1>
          <p className="text-slate-400">Adicione, remova e edite os seus vendedores.</p>
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
              placeholder="Pesquisar usuário"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10 bg-slate-900/60 border-slate-700 text-white placeholder:text-slate-500"
            />
          </div>
          
          <div className="flex gap-3">
            <Button
              onClick={() => setShowImport(true)}
              variant="outline"
              className="bg-slate-900/60 border-blue-600 text-blue-400 hover:bg-blue-600 hover:text-white"
            >
              <Upload className="w-4 h-4 mr-2" />
              Importar colaboradores
            </Button>
            
            <Button
              onClick={() => setShowSellerForm(true)}
              className="bg-green-600 hover:bg-green-700"
            >
              <UserPlus className="w-4 h-4 mr-2" />
              Criar colaborador
            </Button>
          </div>
        </motion.div>

        {/* Sellers List */}
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.2 }}
          className="space-y-3"
        >
          <AnimatePresence>
            {isLoading ? (
              <div className="text-center py-12 text-slate-400">
                Carregando vendedores...
              </div>
            ) : sellers.length === 0 ? (
              <div className="text-center py-12 text-slate-400">
                <UserPlus className="w-16 h-16 mx-auto mb-4 opacity-30" />
                <p>Nenhum vendedor encontrado</p>
              </div>
            ) : (
              sellers.map((seller, index) => (
                <motion.div
                  key={seller.id}
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 20 }}
                  transition={{ delay: index * 0.05 }}
                  className="bg-[#0d1525] backdrop-blur-sm rounded-xl p-4 border border-slate-800/50 hover:border-blue-500/50 transition-all"
                >
                  <div className="flex items-center gap-4">
                    {/* Avatar com Gamificação */}
                    <div className="relative flex-shrink-0">
                      {seller.avatar_url ? (
                        <motion.img
                          src={seller.avatar_url}
                          alt={seller.name}
                          className="w-16 h-16 rounded-lg border-2 border-slate-700 object-cover"
                          whileHover={{ scale: 1.05 }}
                          transition={{ duration: 0.2 }}
                        />
                      ) : (
                        <motion.div
                          className={cn(
                            "w-16 h-16 rounded-lg border-2 flex items-center justify-center bg-gradient-to-br",
                            getLevelColor(seller.level || 1)
                          )}
                          whileHover={{ scale: 1.05 }}
                          transition={{ duration: 0.2 }}
                        >
                          <span className="text-white font-bold text-xl">
                            {seller.name?.charAt(0)?.toUpperCase()}
                          </span>
                        </motion.div>
                      )}
                      {/* Badge de nível gamificado */}
                      {seller.badge && (
                        <motion.div
                          className="absolute -top-2 -right-2 px-1.5 py-0.5 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 text-[10px] font-bold text-slate-900 flex items-center gap-1 shadow-lg border-2 border-slate-900"
                          initial={{ opacity: 0, scale: 0.8 }}
                          animate={{ opacity: 1, scale: 1 }}
                          whileHover={{ scale: 1.1 }}
                        >
                          <Sparkles className="w-3 h-3" />
                          <span>{seller.badge}</span>
                        </motion.div>
                      )}
                      {/* Indicador de nível */}
                      {seller.level && (
                        <div className="absolute -bottom-1 left-1/2 transform -translate-x-1/2 px-1.5 py-0.5 rounded-full bg-slate-900/90 border border-slate-700">
                          <span className="text-[10px] font-bold text-white">Lv.{seller.level}</span>
                        </div>
                      )}
                    </div>

                    {/* Info Grid */}
                    <div className="flex-1 grid grid-cols-5 gap-4 items-center">
                      <div>
                        <p className="text-white font-semibold">{seller.name}</p>
                        <p className="text-slate-400 text-sm">{seller.cpf || "—"}</p>
                      </div>
                      
                      <div>
                        <p className="text-slate-400 text-xs mb-1">E-mail</p>
                        <p className="text-blue-400 text-sm">{seller.email}</p>
                      </div>
                      
                      <div>
                        <p className="text-slate-400 text-xs mb-1">Data de criação</p>
                        <p className="text-white text-sm">
                          {seller.created_date ? new Date(seller.created_date).toLocaleDateString('pt-BR') : "—"}
                        </p>
                      </div>
                      
                      <div>
                        <p className="text-slate-400 text-xs mb-1">Total de times</p>
                        <p className="text-white text-sm">{getTeamName(seller.team_id)}</p>
                      </div>
                      
                      <div>
                        <p className="text-slate-400 text-xs mb-1">Nível de acesso</p>
                        <p className="text-white text-sm">{getAccessLevelLabel(seller.access_level)}</p>
                      </div>
                    </div>

                    {/* Actions */}
                    <div className="flex gap-2">
                      <button
                        onClick={() => handleEdit(seller)}
                        className="p-2 hover:bg-blue-600/20 rounded-lg transition-colors"
                      >
                        <Edit className="w-4 h-4 text-blue-400" />
                      </button>
                      
                      <button
                        onClick={() => handleDelete(seller)}
                        className="p-2 hover:bg-red-600/20 rounded-lg transition-colors"
                      >
                        <Trash2 className="w-4 h-4 text-red-400" />
                      </button>
                    </div>
                  </div>
                </motion.div>
              ))
            )}
          </AnimatePresence>
        </motion.div>
      </div>

      {/* Modals */}
      <SellerFormModal
        open={showSellerForm}
        onClose={handleCloseForm}
        seller={editingSeller}
        teams={teams}
      />
      
      <ImportSellersModal
        open={showImport}
        onClose={() => setShowImport(false)}
      />
    </div>
  );
}