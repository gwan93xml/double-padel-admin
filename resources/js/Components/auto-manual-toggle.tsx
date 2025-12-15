"use client"

import { motion } from "framer-motion"
import { Settings, Hand } from "lucide-react"
import { cn } from "@/lib/utils"

interface AutoManualToggleProps {
  isAuto: boolean
  onToggle: (isAuto: boolean) => void
  disabled?: boolean
  className?: string
}

export default function AutoManualToggle({ isAuto, onToggle, disabled = false, className }: AutoManualToggleProps) {
  const handleToggle = () => {
    if (!disabled) {
      onToggle(!isAuto)
    }
  }

  return (
    <div className={cn("flex items-center space-x-3", className)}>
      {/* Toggle Switch */}
      <motion.button
        type="button"
        className={cn(
          "relative w-16 h-8 rounded-full p-1 transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-opacity-50",
          isAuto ? "bg-blue-500 focus:ring-blue-300" : "bg-orange-500 focus:ring-orange-300",
          disabled && "opacity-50 cursor-not-allowed",
        )}
        onClick={handleToggle}
        disabled={disabled}
        whileTap={disabled ? {} : { scale: 0.95 }}
      >
        {/* Sliding Circle with Icon */}
        <motion.div
          className="relative w-6 h-6 bg-white rounded-full shadow-md flex items-center justify-center"
          animate={{
            x: isAuto ? 0 : 32,
          }}
          transition={{
            type: "spring",
            stiffness: 500,
            damping: 30,
          }}
        >
          <motion.div
            key={isAuto ? "auto" : "manual"}
            initial={{ scale: 0, rotate: -90 }}
            animate={{ scale: 1, rotate: 0 }}
            transition={{
              type: "spring",
              stiffness: 600,
              damping: 25,
            }}
          >
            {isAuto ? <Settings className="w-3 h-3 text-blue-500" /> : <Hand className="w-3 h-3 text-orange-500" />}
          </motion.div>
        </motion.div>
      </motion.button>

      {/* Label */}
      <motion.span
        className="text-sm font-medium text-gray-700"
        key={isAuto ? "auto-label" : "manual-label"}
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 0.2 }}
      >
      </motion.span>
    </div>
  )
}
