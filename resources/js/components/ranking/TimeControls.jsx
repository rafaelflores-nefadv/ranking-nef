import React, { useState } from "react";
import { motion } from "framer-motion";
import { Play, Pause } from "lucide-react";

export default function TimeControls() {
  const [isPlaying, setIsPlaying] = useState(true);
  const [selectedTime, setSelectedTime] = useState("30s");
  
  const timeOptions = ["1m", "3m", "5m", "10m", "15m"];

  return (
    <div className="flex flex-col items-center gap-4">
      {/* Time selector buttons */}
      <div className="flex flex-col gap-2">
        <button
          onClick={() => setSelectedTime("15s")}
          className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${
            selectedTime === "15s"
              ? "bg-blue-600 text-white shadow-lg shadow-blue-500/50"
              : "bg-slate-800/50 text-slate-400 hover:bg-slate-700/50"
          }`}
        >
          15s
        </button>

        <button
          onClick={() => setSelectedTime("30s")}
          className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${
            selectedTime === "30s"
              ? "bg-blue-600 text-white shadow-lg shadow-blue-500/50"
              : "bg-slate-800/50 text-slate-400 hover:bg-slate-700/50"
          }`}
        >
          30s
        </button>
        
        {timeOptions.map((time) => (
          <button
            key={time}
            onClick={() => setSelectedTime(time)}
            className={`px-4 py-2 rounded-lg text-sm font-medium transition-all ${
              selectedTime === time
                ? "bg-blue-600 text-white shadow-lg shadow-blue-500/50"
                : "bg-slate-800/50 text-slate-400 hover:bg-slate-700/50"
            }`}
          >
            {time}
          </button>
        ))}
      </div>
      
      {/* Play/Pause button */}
      <motion.button
        whileHover={{ scale: 1.05 }}
        whileTap={{ scale: 0.95 }}
        onClick={() => setIsPlaying(!isPlaying)}
        className="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/50 hover:bg-blue-700 transition-colors"
      >
        {isPlaying ? (
          <Pause className="w-5 h-5 text-white fill-white" />
        ) : (
          <Play className="w-5 h-5 text-white fill-white" />
        )}
      </motion.button>
      
      {/* Timer display */}
      <motion.div
        animate={{ scale: [1, 1.05, 1] }}
        transition={{ duration: 1, repeat: Infinity }}
        className="relative w-32 h-32"
      >
        {/* Circular progress */}
        <svg className="w-full h-full transform -rotate-90">
          <circle
            cx="64"
            cy="64"
            r="56"
            stroke="rgba(100, 116, 139, 0.3)"
            strokeWidth="8"
            fill="none"
          />
          <circle
            cx="64"
            cy="64"
            r="56"
            stroke="url(#gradient)"
            strokeWidth="8"
            fill="none"
            strokeLinecap="round"
            strokeDasharray="351.86"
            strokeDashoffset="87.965"
            className="transition-all duration-1000"
          />
          <defs>
            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stopColor="#3b82f6" />
              <stop offset="100%" stopColor="#8b5cf6" />
            </linearGradient>
          </defs>
        </svg>
        
        {/* Timer text */}
        <div className="absolute inset-0 flex flex-col items-center justify-center">
          <span className="text-white text-xl font-bold">2 sem 4d</span>
          <span className="text-slate-400 text-xs">restantes</span>
        </div>
      </motion.div>
    </div>
  );
}