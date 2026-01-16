import React from "react";
import { motion } from "framer-motion";
import { Trophy, TrendingUp } from "lucide-react";

const positionStyles = {
  1: {
    gradient: "from-yellow-400 via-yellow-500 to-amber-600",
    shadow: "shadow-yellow-500/30",
    height: "h-40",
    order: "order-2",
    icon: "🥇",
    delay: 0.2
  },
  2: {
    gradient: "from-gray-300 via-gray-400 to-gray-500",
    shadow: "shadow-gray-400/30",
    height: "h-32",
    order: "order-1",
    icon: "🥈",
    delay: 0.3
  },
  3: {
    gradient: "from-amber-600 via-amber-700 to-amber-800",
    shadow: "shadow-amber-600/30",
    height: "h-24",
    order: "order-3",
    icon: "🥉",
    delay: 0.4
  }
};

export default function PodiumCard({ seller, position, totalSales }) {
  const style = positionStyles[position] || positionStyles[3];
  
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value || 0);
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 50 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: style.delay }}
      className={`flex flex-col items-center ${style.order}`}
    >
      <motion.div
        whileHover={{ scale: 1.05 }}
        className="relative mb-3"
      >
        <div className={`w-20 h-20 md:w-24 md:h-24 rounded-full bg-gradient-to-br ${style.gradient} p-1 ${style.shadow} shadow-2xl`}>
          <div className="w-full h-full rounded-full bg-slate-900 flex items-center justify-center overflow-hidden">
            {seller?.avatar_url ? (
              <img src={seller.avatar_url} alt={seller.name} className="w-full h-full object-cover" />
            ) : (
              <span className="text-2xl md:text-3xl font-bold text-white">
                {seller?.name?.charAt(0)?.toUpperCase() || "?"}
              </span>
            )}
          </div>
        </div>
        <span className="absolute -top-2 -right-2 text-2xl md:text-3xl">{style.icon}</span>
      </motion.div>
      
      <h3 className="text-white font-semibold text-sm md:text-base text-center max-w-[100px] truncate">
        {seller?.name || "—"}
      </h3>
      
      <p className={`text-transparent bg-clip-text bg-gradient-to-r ${style.gradient} font-bold text-lg md:text-xl mt-1`}>
        {formatCurrency(totalSales)}
      </p>
      
      <div className={`mt-4 w-20 md:w-28 ${style.height} bg-gradient-to-t ${style.gradient} rounded-t-xl flex items-end justify-center pb-3`}>
        <span className="text-white/90 font-bold text-2xl md:text-4xl">{position}º</span>
      </div>
    </motion.div>
  );
}