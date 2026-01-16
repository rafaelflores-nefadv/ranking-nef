import React from "react";
import { motion } from "framer-motion";
import { TrendingUp, TrendingDown, Minus } from "lucide-react";

export default function RankingSidebar({ sellers }) {
  const formatPoints = (value) => {
    return Math.round(value || 0);
  };

  const getPositionColor = (position) => {
    if (position <= 3) return "from-blue-600 to-blue-500";
    if (position <= 6) return "from-purple-600 to-purple-500";
    return "from-slate-600 to-slate-500";
  };

  const renderTrend = (change) => {
    if (!change) {
      return <Minus className="w-4 h-4 text-slate-500" />;
    }
    if (change > 0) {
      return <TrendingUp className="w-4 h-4 text-green-500" />;
    }
    return <TrendingDown className="w-4 h-4 text-red-500" />;
  };

  return (
    <div className="space-y-2">
      {sellers.map((seller, index) => {
        const position = index + 1;
        
        return (
          <motion.div
            key={seller.id}
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.3, delay: index * 0.05 }}
            className="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-3 border border-slate-700/50 hover:border-blue-500/50 transition-all group"
          >
            <div className="flex items-center gap-3">
              {/* Position badge */}
              <div className={`relative w-8 h-8 rounded-lg bg-gradient-to-br ${getPositionColor(position)} flex items-center justify-center flex-shrink-0`}>
                <span className="text-white font-bold text-sm">{position}</span>
              </div>
              
              {/* Avatar */}
              <div className="relative">
                <div className="w-12 h-12 rounded-full overflow-hidden border-2 border-slate-600 group-hover:border-blue-500 transition-colors">
                  {seller.avatar_url ? (
                    <img src={seller.avatar_url} alt={seller.name} className="w-full h-full object-cover" />
                  ) : (
                    <div className="w-full h-full bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                      <span className="text-base font-bold text-white">
                        {seller.name?.charAt(0)?.toUpperCase()}
                      </span>
                    </div>
                  )}
                </div>
                
                {/* Online indicator */}
                <div className="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 rounded-full border-2 border-slate-900" />
              </div>
              
              {/* Info */}
              <div className="flex-1 min-w-0">
                <h4 className="text-white font-semibold text-sm truncate">
                  {seller.name}
                </h4>
                <div className="flex items-center gap-2 text-xs">
                  <span className="text-slate-400">
                    Pontos: <span className="text-blue-400 font-semibold">{formatPoints(seller.total_sales)}</span>
                  </span>
                  <span className="text-slate-500">•</span>
                  <span className="text-slate-400">
                    Passados: <span className="text-slate-300">{seller.sales_count || 0}</span>
                  </span>
                </div>
              </div>
              
              {/* Trend indicator */}
              <div className="flex-shrink-0">
                {renderTrend(seller.change)}
              </div>
            </div>
          </motion.div>
        );
      })}
    </div>
  );
}