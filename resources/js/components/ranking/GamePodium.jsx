import React from "react";
import { motion } from "framer-motion";
import { Crown } from "lucide-react";

const podiumStyles = {
  1: {
    gradient: "from-yellow-400 via-amber-500 to-yellow-600",
    badgeGradient: "from-blue-600 via-blue-500 to-blue-400",
    height: "h-64",
    width: "w-64",
    zIndex: "z-30",
    scale: 1.2,
    delay: 0.2,
    borderColor: "border-yellow-400",
    glowColor: "shadow-yellow-500/50"
  },
  2: {
    gradient: "from-cyan-400 via-cyan-500 to-cyan-600",
    badgeGradient: "from-cyan-600 via-cyan-500 to-cyan-400",
    height: "h-56",
    width: "w-56",
    zIndex: "z-20",
    scale: 1,
    delay: 0.3,
    borderColor: "border-cyan-400",
    glowColor: "shadow-cyan-500/50"
  },
  3: {
    gradient: "from-orange-400 via-orange-500 to-red-500",
    badgeGradient: "from-orange-600 via-red-500 to-red-600",
    height: "h-56",
    width: "w-56",
    zIndex: "z-20",
    scale: 1,
    delay: 0.4,
    borderColor: "border-orange-400",
    glowColor: "shadow-orange-500/50"
  }
};

export default function GamePodium({ topThree }) {
  const formatPoints = (value) => {
    return Math.round(value || 0) + " Pontos";
  };

  const renderBadge = (seller, position) => {
    const style = podiumStyles[position];
    
    return (
      <motion.div
        initial={{ opacity: 0, y: 50, scale: 0.8 }}
        animate={{ opacity: 1, y: 0, scale: style.scale }}
        transition={{ duration: 0.6, delay: style.delay, type: "spring" }}
        className={`relative ${style.zIndex}`}
      >
        {/* Badge Shape */}
        <div className={`relative ${style.width} ${style.height} flex items-center justify-center`}>
          {/* Glow effect */}
          <div className={`absolute inset-0 bg-gradient-to-b ${style.gradient} opacity-20 blur-2xl ${style.glowColor} shadow-2xl`} />
          
          {/* Badge background */}
          <svg viewBox="0 0 200 240" className="w-full h-full">
            <defs>
              <linearGradient id={`gradient-${position}`} x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" className="text-blue-600" style={{ stopColor: 'currentColor' }} />
                <stop offset="50%" className="text-blue-500" style={{ stopColor: 'currentColor' }} />
                <stop offset="100%" className="text-blue-700" style={{ stopColor: 'currentColor' }} />
              </linearGradient>
            </defs>
            
            {/* Shield shape */}
            <path
              d="M 100 10 L 170 40 L 170 120 Q 170 180 100 230 Q 30 180 30 120 L 30 40 Z"
              fill={`url(#gradient-${position})`}
              stroke={style.borderColor}
              strokeWidth="3"
              className="drop-shadow-2xl"
            />
            
            {/* Inner shield detail */}
            <path
              d="M 100 25 L 160 50 L 160 115 Q 160 165 100 210 Q 40 165 40 115 L 40 50 Z"
              fill="rgba(0,0,0,0.3)"
            />
          </svg>
          
          {/* Content overlay */}
          <div className="absolute inset-0 flex flex-col items-center justify-center">
            {/* Position Crown for 1st place */}
            {position === 1 && (
              <motion.div
                animate={{ rotate: [-5, 5, -5] }}
                transition={{ duration: 2, repeat: Infinity }}
                className="absolute -top-8"
              >
                <Crown className="w-12 h-12 text-yellow-400 fill-yellow-400" />
              </motion.div>
            )}
            
            {/* Avatar */}
            <div className={`w-20 h-20 rounded-full overflow-hidden border-4 ${style.borderColor} mb-3`}>
              {seller?.avatar_url ? (
                <img src={seller.avatar_url} alt={seller.name} className="w-full h-full object-cover" />
              ) : (
                <div className="w-full h-full bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                  <span className="text-2xl font-bold text-white">
                    {seller?.name?.charAt(0)?.toUpperCase() || "?"}
                  </span>
                </div>
              )}
            </div>
            
            {/* Name */}
            <h3 className="text-white font-bold text-lg text-center px-4 mb-1">
              {seller?.name || "—"}
            </h3>
            
            {/* Points */}
            <p className="text-white/90 font-semibold text-sm">
              {formatPoints(seller?.total_sales)}
            </p>
            
            {/* Position badge at bottom */}
            <div className={`absolute -bottom-4 w-10 h-10 rounded-full bg-gradient-to-br ${style.gradient} border-4 border-slate-900 flex items-center justify-center ${style.glowColor} shadow-xl`}>
              <span className="text-white font-bold text-lg">{position}</span>
            </div>
          </div>
        </div>
      </motion.div>
    );
  };

  return (
    <div className="relative py-12">
      {/* Holographic platform effect */}
      <div className="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-[600px] h-32">
        <motion.div
          animate={{ 
            rotateX: [0, 5, 0],
            scale: [1, 1.02, 1]
          }}
          transition={{ duration: 3, repeat: Infinity }}
          className="relative w-full h-full"
        >
          {/* Platform glow */}
          <div className="absolute inset-0 bg-gradient-to-r from-cyan-500/20 via-blue-500/30 to-purple-500/20 blur-xl" />
          
          {/* Platform rings */}
          <div className="absolute inset-0 flex items-center justify-center">
            <motion.div
              animate={{ scale: [1, 1.1, 1], opacity: [0.3, 0.6, 0.3] }}
              transition={{ duration: 2, repeat: Infinity }}
              className="w-96 h-4 rounded-full border-2 border-cyan-400/40 bg-cyan-500/10"
            />
          </div>
        </motion.div>
      </div>
      
      {/* Podium badges */}
      <div className="flex items-end justify-center gap-8 relative z-10">
        {/* 2nd place - left */}
        {topThree[1] && renderBadge(topThree[1], 2)}
        
        {/* 1st place - center */}
        {topThree[0] && renderBadge(topThree[0], 1)}
        
        {/* 3rd place - right */}
        {topThree[2] && renderBadge(topThree[2], 3)}
      </div>
    </div>
  );
}