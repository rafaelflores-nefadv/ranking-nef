import React from 'react';
import { motion } from 'framer-motion';
import { Trophy, Users, User } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const modes = [
  { value: 'general', label: 'Geral', icon: Trophy },
  { value: 'team', label: 'Equipes', icon: Users },
  { value: 'individual', label: 'Individual', icon: User },
];

export function RankingModeSelector({ mode, onChange, teamId }) {
  return (
    <div className="flex items-center gap-2 p-1 bg-slate-900/60 rounded-lg border border-slate-700/50">
      {modes.map((modeOption) => {
        const Icon = modeOption.icon;
        const isActive = mode === modeOption.value;

        return (
          <motion.div
            key={modeOption.value}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            <Button
              variant="ghost"
              size="sm"
              onClick={() => onChange(modeOption.value)}
              className={cn(
                'flex items-center gap-2 transition-all',
                isActive
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30'
                  : 'text-slate-400 hover:text-white hover:bg-slate-800/50'
              )}
            >
              <Icon className="w-4 h-4" />
              <span className="text-sm font-medium">{modeOption.label}</span>
            </Button>
          </motion.div>
        );
      })}
    </div>
  );
}

