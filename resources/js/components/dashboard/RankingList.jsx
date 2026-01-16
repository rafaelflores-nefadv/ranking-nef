import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { RankingCard } from './RankingCard';
import { ScrollArea } from '@/components/ui/scroll-area';

export function RankingList({ entries, highlightId, maxHeight = '600px' }) {
  const listEntries = entries.slice(3);

  if (listEntries.length === 0) {
    return (
      <div className="text-center py-8 text-slate-400">
        <p className="text-sm">Nenhum participante adicional</p>
      </div>
    );
  }

  return (
    <ScrollArea className="w-full" style={{ maxHeight }}>
      <div className="space-y-3 pr-4">
        <AnimatePresence>
          {listEntries.map((entry, index) => (
            <motion.div
              key={entry.id}
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 20 }}
              transition={{
                delay: index * 0.05,
                duration: 0.3,
              }}
            >
              <RankingCard
                entry={entry}
                index={index + 3}
                highlight={entry.id === highlightId}
              />
            </motion.div>
          ))}
        </AnimatePresence>
      </div>
    </ScrollArea>
  );
}

