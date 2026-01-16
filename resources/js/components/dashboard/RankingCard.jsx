import React, { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Trophy, TrendingUp, TrendingDown, Minus, Sparkles } from 'lucide-react';
import { cn } from '@/lib/utils';

export function RankingCard({ entry, index, isTop3 = false, highlight = false }) {
  const [displayPoints, setDisplayPoints] = useState(entry.points);
  const [isAnimating, setIsAnimating] = useState(false);

  useEffect(() => {
    if (displayPoints !== entry.points) {
      setIsAnimating(true);
      const diff = entry.points - displayPoints;
      const steps = 30;
      const increment = diff / steps;
      let current = displayPoints;
      let step = 0;

      const timer = setInterval(() => {
        step++;
        current += increment;
        setDisplayPoints(Math.round(current));

        if (step >= steps) {
          setDisplayPoints(entry.points);
          setIsAnimating(false);
          clearInterval(timer);
        }
      }, 16);

      return () => clearInterval(timer);
    }
  }, [entry.points, displayPoints]);

  const PositionChange = () => {
    if (entry.change === 0) {
      return <Minus className="w-4 h-4 text-slate-500" />;
    }
    if (entry.change > 0) {
      return <TrendingUp className="w-4 h-4 text-green-500" />;
    }
    return <TrendingDown className="w-4 h-4 text-red-500" />;
  };

  const getTop3Colors = () => {
    switch (entry.position) {
      case 1:
        return 'from-yellow-500/20 to-yellow-600/10 border-yellow-500/30';
      case 2:
        return 'from-slate-400/20 to-slate-500/10 border-slate-400/30';
      case 3:
        return 'from-orange-500/20 to-orange-600/10 border-orange-500/30';
      default:
        return 'from-slate-800/40 to-slate-900/20 border-slate-700/30';
    }
  };

  const getLevelColor = (level) => {
    if (level >= 5) return 'from-amber-400 to-amber-600 text-amber-100';
    if (level >= 4) return 'from-purple-400 to-purple-700 text-purple-100';
    if (level >= 3) return 'from-blue-400 to-blue-700 text-blue-100';
    if (level >= 2) return 'from-emerald-400 to-emerald-700 text-emerald-100';
    return 'from-slate-600 to-slate-800 text-slate-100';
  };

  return (
    <AnimatePresence mode="wait">
      <motion.div
        key={entry.id}
        initial={{ opacity: 0, y: 20, scale: 0.95 }}
        animate={{ 
          opacity: 1, 
          y: 0, 
          scale: 1,
          boxShadow: highlight 
            ? '0 0 20px rgba(59, 130, 246, 0.5)' 
            : '0 4px 6px rgba(0, 0, 0, 0.1)',
        }}
        exit={{ opacity: 0, scale: 0.95 }}
        transition={{ 
          duration: 0.3,
          type: 'spring',
          stiffness: 300,
        }}
        layout
        className={cn(
          'relative bg-gradient-to-br backdrop-blur-sm rounded-xl p-4 border',
          isTop3 ? getTop3Colors() : 'from-slate-800/40 to-slate-900/20 border-slate-700/30',
          highlight && 'ring-2 ring-blue-500/50',
          'hover:scale-[1.02] transition-transform duration-200'
        )}
      >
        {isTop3 && (
          <motion.div
            animate={{
              y: [0, -8, 0],
            }}
            transition={{
              duration: 2,
              repeat: Infinity,
              ease: 'easeInOut',
            }}
            className="absolute inset-0 rounded-xl"
          />
        )}

        <div className="relative z-10 flex items-center gap-4">
          <div className="flex-shrink-0 relative">
            {isTop3 ? (
              <div className="relative">
                <Trophy
                  className={cn(
                    'w-8 h-8',
                    entry.position === 1 && 'text-yellow-500',
                    entry.position === 2 && 'text-slate-400',
                    entry.position === 3 && 'text-orange-500'
                  )}
                />
                <span className="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">
                  {entry.position}
                </span>
              </div>
            ) : (
              <div className="w-10 h-10 rounded-full bg-slate-700/50 flex items-center justify-center">
                <span className="text-slate-300 font-bold text-sm">#{entry.position}</span>
              </div>
            )}
          </div>

          <div className="flex-shrink-0">
            {entry.avatar ? (
              <motion.img
                src={entry.avatar}
                alt={entry.name}
                className="w-12 h-12 rounded-full border-2 border-slate-600"
                animate={highlight ? { scale: [1, 1.08, 1] } : {}}
                transition={highlight ? { duration: 1.2, repeat: Infinity } : {}}
              />
            ) : (
              <motion.div
                className="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center"
                animate={highlight ? { y: [0, -2, 0] } : {}}
                transition={highlight ? { duration: 1.5, repeat: Infinity } : {}}
              >
                <span className="text-white font-bold text-sm">
                  {entry.name.charAt(0).toUpperCase()}
                </span>
              </motion.div>
            )}
            {entry.badge && (
              <motion.div
                className="absolute -top-1 -right-1 px-1.5 py-0.5 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 text-[10px] font-bold text-slate-900 flex items-center gap-1 shadow-lg"
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
              >
                <Sparkles className="w-3 h-3" />
                <span>{entry.badge}</span>
              </motion.div>
            )}
          </div>

          <div className="flex-1 min-w-0">
            <h3 className="text-white font-semibold text-sm truncate">{entry.name}</h3>
            {entry.teamName && (
              <p className="text-slate-400 text-xs truncate">{entry.teamName}</p>
            )}
          </div>

          <div className="flex-shrink-0 flex items-center gap-4">
            <div className="hidden sm:flex flex-col items-end text-xs mr-1">
              {entry.level && (
                <div
                  className={`mb-1 px-2 py-0.5 rounded-full bg-gradient-to-r ${getLevelColor(
                    entry.level,
                  )} font-semibold shadow-md`}
                >
                  Nível {entry.level}
                </div>
              )}
              {entry.status && (
                <span className="text-slate-400 capitalize">{entry.status}</span>
              )}
            </div>
            <div className="text-right">
              <motion.div
                key={displayPoints}
                animate={isAnimating ? { scale: [1, 1.2, 1] } : {}}
                className="text-white font-bold text-lg"
              >
                {displayPoints.toLocaleString('pt-BR')}
              </motion.div>
              <div className="flex items-center gap-1 text-xs text-slate-400">
                <PositionChange />
                {entry.change !== 0 && (
                  <span className={cn(
                    entry.change > 0 ? 'text-green-500' : 'text-red-500'
                  )}>
                    {Math.abs(entry.change)}
                  </span>
                )}
              </div>
            </div>
          </div>
        </div>
      </motion.div>
    </AnimatePresence>
  );
}

