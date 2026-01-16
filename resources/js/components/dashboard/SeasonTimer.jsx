import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { Clock, Calendar } from 'lucide-react';
import { cn } from '@/lib/utils';

export function SeasonTimer({ season, className }) {
  const [timeRemaining, setTimeRemaining] = useState(null);

  useEffect(() => {
    if (!season || !season.isActive) {
      setTimeRemaining(null);
      return;
    }

    const updateTimer = () => {
      const now = new Date().getTime();
      const end = new Date(season.endDate).getTime();
      const diff = end - now;

      if (diff <= 0) {
        setTimeRemaining({ days: 0, hours: 0, minutes: 0, seconds: 0 });
        return;
      }

      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((diff % (1000 * 60)) / 1000);

      setTimeRemaining({ days, hours, minutes, seconds });
    };

    updateTimer();
    const interval = setInterval(updateTimer, 1000);

    return () => clearInterval(interval);
  }, [season]);

  if (!season || !timeRemaining) {
    return null;
  }

  const isUrgent = timeRemaining.days < 7;

  return (
    <motion.div
      initial={{ opacity: 0, y: -10 }}
      animate={{ opacity: 1, y: 0 }}
      className={cn(
        'bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-slate-700/50',
        isUrgent && 'border-red-500/50 bg-red-950/20',
        className
      )}
    >
      <div className="flex items-center gap-3 mb-3">
        <Clock className={cn('w-5 h-5', isUrgent ? 'text-red-500' : 'text-blue-500')} />
        <div>
          <h3 className="text-white font-semibold text-sm">{season.name}</h3>
          <p className="text-slate-400 text-xs flex items-center gap-1">
            <Calendar className="w-3 h-3" />
            Termina em
          </p>
        </div>
      </div>

      <div className="grid grid-cols-4 gap-2">
        {[
          { label: 'Dias', value: timeRemaining.days },
          { label: 'Horas', value: timeRemaining.hours },
          { label: 'Min', value: timeRemaining.minutes },
          { label: 'Seg', value: timeRemaining.seconds },
        ].map((item, index) => (
          <motion.div
            key={item.label}
            initial={{ scale: 0.8, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ delay: index * 0.1 }}
            className={cn(
              'bg-slate-800/50 rounded-lg p-2 text-center',
              isUrgent && 'bg-red-900/30'
            )}
          >
            <div className={cn('text-2xl font-bold', isUrgent ? 'text-red-400' : 'text-blue-400')}>
              {String(item.value).padStart(2, '0')}
            </div>
            <div className="text-xs text-slate-400 mt-1">{item.label}</div>
          </motion.div>
        ))}
      </div>
    </motion.div>
  );
}

