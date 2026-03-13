import React from "react";
import { Plus, Minus } from "lucide-react";

/**
 * Standard Quantity Controller for Medicine Dispensing
 * 
 * @param {number} value - Current quantity
 * @param {number} max - Maximum allowed quantity (usually stock remaining)
 * @param {function} onChange - Callback triggered on quantity change
 */
export default function QuantityController({ value = 1, max = 99, onChange }) {
  const decrement = () => {
    if (value > 1) {
      onChange(Math.max(1, value - 1));
    }
  };

  const increment = () => {
    if (value < max) {
      onChange(Math.min(max, value + 1));
    }
  };

  return (
    <div className="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200 h-[60px] justify-center transition-all">
      <button
        type="button"
        disabled={value <= 1}
        onClick={decrement}
        className="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-600 font-black hover:bg-slate-100 transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <Minus size={16} />
      </button>

      <div className="w-12 text-center select-none">
        <span className="font-black text-slate-800 text-lg tabular-nums">
          {value || 1}
        </span>
      </div>

      <button
        type="button"
        disabled={value >= max}
        onClick={increment}
        className="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 rounded-xl text-slate-600 font-black hover:bg-slate-100 transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <Plus size={16} />
      </button>
    </div>
  );
}
