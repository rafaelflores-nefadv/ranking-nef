import React from "react";
import { motion } from "framer-motion";
import { TrendingUp, Medal } from "lucide-react";

export default function RankingList({ sellers }) {
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value || 0);
  };

  const getMedalColor = (position) => {
    if (position === 4) return "text-purple-400";
    if (position === 5) return "text-blue-400";
    return "text-slate-400";
  };

  return (
    <div className="space-y-3">
      {sellers.slice(3).map((seller, index) => (
        <motion.div
          key={seller.id}
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.3, delay: index * 0.1 }}
          className="bg-slate-800/50 backdrop-blur-sm rounded-xl p-4 flex items-center justify-between hover:bg-slate-800/70 transition-all duration-300 border border-slate-700/50"
        >
          <div className="flex items-center gap-4">
            <div className="flex items-center justify-center w-10 h-10 rounded-full bg-slate-700/50">
              <span className={`font-bold ${getMedalColor(index + 4)}`}>{index + 4}º</span>
            </div>
            
            <div className="w-12 h-12 rounded-full bg-gradient-to-br from-slate-600 to-slate-700 flex items-center justify-center overflow-hidden">
              {seller.avatar_url ? (
                <img src={seller.avatar_url} alt={seller.name} className="w-full h-full object-cover" />
              ) : (
                <span className="text-lg font-bold text-white">
                  {seller.name?.charAt(0)?.toUpperCase()}
                </span>
              )}
            </div>
            
            <div>
              <h4 className="text-white font-medium">{seller.name}</h4>
              <p className="text-slate-400 text-sm">{seller.department || "Sem departamento"}</p>
            </div>
          </div>
          
          <div className="text-right">
            <p className="text-white font-semibold">{formatCurrency(seller.total_sales)}</p>
            <p className="text-slate-400 text-sm flex items-center justify-end gap-1">
              <TrendingUp className="w-3 h-3" />
              {seller.sales_count || 0} vendas
            </p>
          </div>
        </motion.div>
      ))}
    </div>
  );
}