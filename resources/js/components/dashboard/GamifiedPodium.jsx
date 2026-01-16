/**
 * Pódio Gamificado com Escudos (Shields)
 * Visual estilo game com escudos grandes, coroas e barras de progresso
 */

import React from 'react';
import { motion } from 'framer-motion';
import { Crown } from 'lucide-react';
import { cn } from '@/lib/utils';

export function GamifiedPodium({ top3 }) {
  if (top3.length === 0) {
    return (
      <div className="text-center py-16 text-slate-400">
        <p className="text-lg">Nenhum participante ainda</p>
      </div>
    );
  }

  // Ordem do pódio: 2º, 1º, 3º
  const podiumOrder = [
    top3[1], // 2º lugar
    top3[0], // 1º lugar
    top3[2], // 3º lugar
  ].filter(Boolean);

  const getShieldStyle = (position) => {
    switch (position) {
      case 1:
        return {
          gradient: 'from-blue-500 via-blue-600 to-blue-800',
          border: 'border-blue-400',
          glow: 'shadow-[0_0_40px_rgba(59,130,246,0.6)]',
          avatarBg: 'bg-yellow-500',
          avatarText: 'text-black',
          textColor: 'text-white',
          size: 'w-80 h-[420px]',
          positionNumberBg: 'bg-yellow-500',
        };
      case 2:
        return {
          gradient: 'from-blue-500 via-blue-600 to-blue-800',
          border: 'border-blue-400',
          glow: 'shadow-[0_0_30px_rgba(59,130,246,0.5)]',
          avatarBg: 'bg-blue-500',
          avatarText: 'text-white',
          textColor: 'text-white',
          size: 'w-72 h-[380px]',
          positionNumberBg: 'bg-blue-500',
        };
      case 3:
        return {
          gradient: 'from-blue-500 via-blue-600 to-blue-800',
          border: 'border-blue-400',
          glow: 'shadow-[0_0_30px_rgba(59,130,246,0.5)]',
          avatarBg: 'bg-orange-500',
          avatarText: 'text-white',
          textColor: 'text-white',
          size: 'w-72 h-[380px]',
          positionNumberBg: 'bg-orange-500',
        };
      default:
        return {
          gradient: 'from-blue-500 via-blue-600 to-blue-800',
          border: 'border-blue-400',
          glow: '',
          avatarBg: 'bg-slate-500',
          avatarText: 'text-white',
          textColor: 'text-white',
          size: 'w-64 h-72',
          positionNumberBg: 'bg-slate-500',
        };
    }
  };

  // Calcular progresso baseado em pontos (exemplo: máximo = pontos do 1º lugar)
  const maxPoints = top3[0]?.points || 1;
  const calculateProgress = (points) => Math.min((points / maxPoints) * 100, 100);

  return (
    <div className="relative w-full flex items-end justify-center gap-8 px-8 py-12">
      {podiumOrder.map((entry, index) => {
        const style = getShieldStyle(entry.position);
        const isChampion = entry.position === 1;
        const progress = calculateProgress(entry.points);

        return (
          <motion.div
            key={entry.id}
            initial={{ opacity: 0, y: 100, scale: 0.8 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            transition={{
              delay: index * 0.2,
              duration: 0.6,
              type: 'spring',
              stiffness: 150,
            }}
            className={cn('relative flex flex-col items-center', style.size)}
          >
            {/* Coroa no 1º lugar */}
            {isChampion && (
              <motion.div
                animate={{
                  y: [0, -8, 0],
                  rotate: [0, 5, -5, 0],
                }}
                transition={{
                  duration: 2,
                  repeat: Infinity,
                  ease: 'easeInOut',
                }}
                className="absolute -top-12 z-20"
              >
                <Crown className="w-12 h-12 text-yellow-400 drop-shadow-[0_0_10px_rgba(234,179,8,0.8)]" />
              </motion.div>
            )}

            {/* Escudo (Shield) */}
            <motion.div
              whileHover={{ scale: 1.05 }}
              className={cn(
                'relative w-full h-full rounded-3xl border-4',
                `bg-gradient-to-b ${style.gradient}`,
                style.border,
                style.glow,
                'flex flex-col items-center justify-between p-6',
                'backdrop-blur-sm'
              )}
            >
              {/* Efeito de brilho animado */}
              <motion.div
                animate={{
                  opacity: [0.3, 0.6, 0.3],
                }}
                transition={{
                  duration: 2,
                  repeat: Infinity,
                  ease: 'easeInOut',
                }}
                className={cn(
                  'absolute inset-0 rounded-3xl',
                  `bg-gradient-to-br ${style.gradient} opacity-30`
                )}
              />

              {/* Avatar circular grande */}
              <div className="relative z-10 mt-8">
                <motion.div
                  animate={isChampion ? {
                    scale: [1, 1.05, 1],
                  } : {}}
                  transition={isChampion ? {
                    duration: 2,
                    repeat: Infinity,
                    ease: 'easeInOut',
                  } : {}}
                  className={cn(
                    'w-40 h-40 rounded-full border-4 border-white/50',
                    'flex items-center justify-center',
                    'shadow-2xl',
                    entry.avatar ? 'overflow-hidden bg-slate-900' : style.avatarBg
                  )}
                >
                  {entry.avatar ? (
                    <img
                      src={entry.avatar}
                      alt={entry.name}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <span className={cn('text-7xl font-bold drop-shadow-lg', style.avatarText)}>
                      {entry.name.charAt(0).toUpperCase()}
                    </span>
                  )}
                </motion.div>
              </div>

              {/* Nome e Pontos */}
              <div className="relative z-10 text-center mt-6 flex-1 flex flex-col justify-center">
                <h3 className={cn('font-bold text-2xl mb-2 drop-shadow-lg', style.textColor)}>
                  {entry.name}
                </h3>
                <p className={cn('text-xl font-semibold drop-shadow-md', style.textColor)}>
                  {entry.points.toLocaleString('pt-BR')} Pontos
                </p>
              </div>

              {/* Barra de Progresso */}
              <div className="relative z-10 w-full mt-6 mb-2">
                <div className="h-3 bg-white/20 rounded-full overflow-hidden shadow-inner">
                  <motion.div
                    initial={{ width: 0 }}
                    animate={{ width: `${progress}%` }}
                    transition={{ delay: index * 0.2 + 0.5, duration: 1.2, ease: 'easeOut' }}
                    className={cn(
                      'h-full rounded-full',
                      `bg-gradient-to-r ${style.gradient}`,
                      'shadow-lg'
                    )}
                  />
                </div>
              </div>

              {/* Número da posição */}
              <div className="absolute -bottom-10 z-10">
                <motion.div
                  initial={{ scale: 0 }}
                  animate={{ scale: 1 }}
                  transition={{ delay: index * 0.2 + 0.8, type: 'spring', stiffness: 200 }}
                  className={cn(
                    'w-16 h-16 rounded-full border-4 border-white/50',
                    'flex items-center justify-center',
                    style.positionNumberBg,
                    'shadow-2xl font-bold text-white text-3xl'
                  )}
                >
                  {entry.position}
                </motion.div>
              </div>
            </motion.div>
          </motion.div>
        );
      })}
    </div>
  );
}

