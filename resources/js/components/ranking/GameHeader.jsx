import React, { useState, useEffect } from "react";
import { motion } from "framer-motion";
import { Clock, Users, TrendingUp, Settings } from "lucide-react";

export default function GameHeader({ stats }) {
  const [time, setTime] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => setTime(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);

  const formatTime = (date) => {
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${hours}:${minutes}:${seconds}`;
  };

  const formatNumber = (num) => {
    return num.toString().padStart(4, '0');
  };

  const calculatePercentage = () => {
    const target = 500000;
    const percentage = ((stats.totalSales / target) * 100).toFixed(2);
    return percentage;
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: -20 }}
      animate={{ opacity: 1, y: 0 }}
      className="bg-slate-900/80 backdrop-blur-md border-b border-slate-700/50 px-6 py-3"
    >
      <div className="flex items-center justify-between max-w-7xl mx-auto">
        {/* Left section - Time info */}
        <div className="flex items-center gap-6">
          <div className="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50">
            <Clock className="w-4 h-4 text-blue-400" />
            <span className="text-slate-400 text-sm">Total de tempo:</span>
            <span className="text-cyan-400 font-bold">{formatNumber(stats.totalCount)}</span>
          </div>
          
          <div className="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50">
            <TrendingUp className="w-4 h-4 text-green-400" />
            <span className="text-slate-400 text-sm">Porcentagem do time:</span>
            <span className="text-green-400 font-bold">{calculatePercentage()}%</span>
          </div>
        </div>

        {/* Center - Current time */}
        <div className="flex items-center gap-3">
          <div className="text-center px-4 py-2 bg-blue-600/20 rounded-lg border border-blue-500/30">
            <span className="text-blue-400 font-mono text-lg font-bold">{formatTime(time)}</span>
          </div>
        </div>

        {/* Right section - User info */}
        <div className="flex items-center gap-4">
          <div className="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50">
            <Users className="w-4 h-4 text-purple-400" />
            <span className="text-slate-400 text-sm">Usuários:</span>
            <span className="text-purple-400 font-bold">({stats.sellersCount}/14)</span>
          </div>
          
          <button className="p-2 hover:bg-slate-800 rounded-lg transition-colors">
            <Settings className="w-5 h-5 text-slate-400 hover:text-white transition-colors" />
          </button>
        </div>
      </div>
    </motion.div>
  );
}