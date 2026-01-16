/**
 * Lista de Classificação Gamificada
 * Cards coloridos com bordas laterais e visual mais game-like
 */

import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ScrollArea } from '@/components/ui/scroll-area';
import { cn } from '@/lib/utils';

export function GamifiedRankingList({ entries }) {
  // Mostra TODOS os participantes (incluindo top 3 na lista)
  const listEntries = entries;

  if (listEntries.length === 0) {
    return (
      <div className="text-center py-8 text-slate-400">
        <p className="text-sm">Nenhum participante adicional</p>
      </div>
    );
  }

  const getPositionColor = (position) => {
    // Top 3: azul, 4º e 5º: roxo
    if (position <= 3) return 'border-l-blue-500 bg-blue-500/10';
    if (position === 4 || position === 5) return 'border-l-purple-500 bg-purple-500/10';
    return 'border-l-slate-500 bg-slate-500/10';
  };

  const getAvatarGradient = (position) => {
    if (position <= 3) return 'from-blue-500 to-blue-700';
    if (position === 4 || position === 5) return 'from-purple-500 to-purple-700';
    return 'from-slate-600 to-slate-800';
  };

  return (
    <ScrollArea className="w-full" style={{ maxHeight: '600px' }}>
      <div className="space-y-3 pr-4">
        <AnimatePresence>
          {listEntries.map((entry, index) => {
            const positionColor = getPositionColor(entry.position);
            const avatarGradient = getAvatarGradient(entry.position);

            return (
              <motion.div
                key={entry.id}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 20 }}
                transition={{
                  delay: index * 0.05,
                  duration: 0.3,
                }}
                className={cn(
                  'relative rounded-lg border-l-4 p-3',
                  'hover:bg-opacity-80 transition-all cursor-pointer',
                  positionColor
                )}
              >
                <div className="flex items-center gap-3">
                  {/* Número da posição */}
                  <div className="flex-shrink-0">
                    <div
                      className={cn(
                        'w-10 h-10 rounded-full flex items-center justify-center',
                        'bg-gradient-to-br',
                        avatarGradient,
                        'font-bold text-white text-base shadow-md'
                      )}
                    >
                      {entry.position}
                    </div>
                  </div>

                  {/* Avatar */}
                  <div className="flex-shrink-0">
                    {entry.avatar ? (
                      <img
                        src={entry.avatar}
                        alt={entry.name}
                        className="w-14 h-14 rounded-full border-2 border-white/20 object-cover"
                      />
                    ) : (
                      <div
                        className={cn(
                          'w-14 h-14 rounded-full flex items-center justify-center',
                          'bg-gradient-to-br',
                          avatarGradient,
                          'border-2 border-white/20 shadow-md'
                        )}
                      >
                        <span className="text-xl font-bold text-white">
                          {entry.name.charAt(0).toUpperCase()}
                        </span>
                      </div>
                    )}
                  </div>

                  {/* Informações */}
                  <div className="flex-1 min-w-0">
                    <h4 className="text-white font-semibold text-sm truncate mb-1">
                      {entry.name}
                    </h4>
                    <div className="flex flex-col gap-0.5">
                      <span className="text-slate-300 text-xs font-medium">
                        Pontos: {entry.points.toLocaleString('pt-BR')}
                      </span>
                      <span className="text-slate-400 text-xs">
                        Passados: {Math.floor(Math.random() * 50) + 20}
                      </span>
                    </div>
                  </div>
                </div>
              </motion.div>
            );
          })}
        </AnimatePresence>
      </div>
    </ScrollArea>
  );
}

