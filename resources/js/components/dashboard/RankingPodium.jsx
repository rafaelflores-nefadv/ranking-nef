import React from 'react';
import { motion } from 'framer-motion';
import { Trophy, Crown, Medal } from 'lucide-react';
import { RankingCard } from './RankingCard';
import { cn } from '@/lib/utils';

export function RankingPodium({ top3 }) {
  if (top3.length === 0) {
    return (
      <div className="text-center py-16 text-slate-400">
        <Trophy className="w-16 h-16 mx-auto mb-4 opacity-30" />
        <p className="text-lg">Nenhum participante ainda</p>
        <p className="text-sm mt-2">Comece cadastrando participantes</p>
      </div>
    );
  }

  const podiumOrder = [
    top3[1],
    top3[0],
    top3[2],
  ].filter(Boolean);

  const getPodiumHeight = (position) => {
    switch (position) {
      case 1:
        return 'h-48';
      case 2:
        return 'h-36';
      case 3:
        return 'h-32';
      default:
        return 'h-32';
    }
  };

  const getPodiumIcon = (position) => {
    switch (position) {
      case 1:
        return <Crown className="w-8 h-8 text-yellow-500" />;
      case 2:
        return <Medal className="w-8 h-8 text-slate-400" />;
      case 3:
        return <Medal className="w-8 h-8 text-orange-500" />;
      default:
        return <Trophy className="w-8 h-8" />;
    }
  };

  return (
    <div className="relative w-full">
      <motion.div
        animate={{
          opacity: [0.3, 0.5, 0.3],
          scale: [1, 1.1, 1],
        }}
        transition={{
          duration: 3,
          repeat: Infinity,
          ease: 'easeInOut',
        }}
        className="absolute inset-0 bg-gradient-to-t from-yellow-500/20 via-transparent to-transparent rounded-2xl blur-2xl"
      />

      <div className="relative flex items-end justify-center gap-4 px-8">
        {podiumOrder.map((entry, index) => {
          const originalPosition = entry.position;
          const isChampion = originalPosition === 1;

          return (
            <motion.div
              key={entry.id}
              initial={{ opacity: 0, y: 50 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{
                delay: index * 0.2,
                duration: 0.5,
                type: 'spring',
                stiffness: 200,
              }}
              className={cn(
                'flex-1 max-w-[200px] flex flex-col items-center',
                isChampion && 'transform scale-110'
              )}
            >
              <motion.div
                animate={isChampion ? {
                  y: [0, -10, 0],
                } : {}}
                transition={isChampion ? {
                  duration: 2,
                  repeat: Infinity,
                  ease: 'easeInOut',
                } : {}}
                className="w-full mb-4"
              >
                <RankingCard entry={entry} index={index} isTop3={true} />
              </motion.div>

              <motion.div
                initial={{ height: 0 }}
                animate={{ height: 'auto' }}
                transition={{
                  delay: index * 0.2 + 0.3,
                  duration: 0.5,
                  type: 'spring',
                }}
                className={cn(
                  'w-full rounded-t-2xl bg-gradient-to-t flex flex-col items-center justify-center p-4',
                  getPodiumHeight(originalPosition),
                  originalPosition === 1 && 'from-yellow-500/30 to-yellow-600/10 border-t-2 border-yellow-500/50',
                  originalPosition === 2 && 'from-slate-400/30 to-slate-500/10 border-t-2 border-slate-400/50',
                  originalPosition === 3 && 'from-orange-500/30 to-orange-600/10 border-t-2 border-orange-500/50',
                )}
              >
                <motion.div
                  animate={isChampion ? {
                    rotate: [0, 10, -10, 0],
                  } : {}}
                  transition={isChampion ? {
                    duration: 2,
                    repeat: Infinity,
                    ease: 'easeInOut',
                  } : {}}
                >
                  {getPodiumIcon(originalPosition)}
                </motion.div>
                <span className="text-white font-bold text-2xl mt-2">
                  #{originalPosition}
                </span>
                <span className="text-slate-300 text-sm mt-1">
                  {entry.points.toLocaleString('pt-BR')} pts
                </span>
              </motion.div>
            </motion.div>
          );
        })}
      </div>
    </div>
  );
}

