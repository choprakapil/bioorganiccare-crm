import React from "react";

const upperTeeth = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];
const lowerTeeth = [32, 31, 30, 29, 28, 27, 26, 25, 24, 23, 22, 21, 20, 19, 18, 17];

export default function DentalChart({ selectedTeeth, toggleTooth }) {
  return (
    <div className="bg-slate-50 border border-slate-100 rounded-[2rem] p-8 shadow-sm">
      <div className="flex justify-between items-center mb-6">
        <h3 className="font-black text-slate-700 uppercase tracking-widest text-xs">
          Interactive Teeth Chart
        </h3>
        <span className="text-[10px] font-bold text-slate-400 bg-white px-3 py-1 rounded-full border border-slate-100">
          {selectedTeeth.length} Selected
        </span>
      </div>

      <div className="space-y-8">
        {/* Upper Arch */}
        <div>
          <p className="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-3 text-center">Upper Arch</p>
          <div className="grid grid-cols-8 gap-2">
            {upperTeeth.map((t) => (
              <button
                key={t}
                type="button"
                onClick={() => toggleTooth(t)}
                className={`aspect-square flex items-center justify-center rounded-xl border-2 font-black transition-all text-xs
                  ${selectedTeeth.includes(t)
                    ? "bg-primary border-primary text-white scale-110 shadow-lg shadow-primary/30"
                    : "bg-white border-slate-100 text-slate-400 hover:border-primary/30 hover:text-primary"}
                `}
              >
                {t}
              </button>
            ))}
          </div>
        </div>

        {/* Lower Arch */}
        <div>
          <p className="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-3 text-center">Lower Arch</p>
          <div className="grid grid-cols-8 gap-2">
            {lowerTeeth.map((t) => (
              <button
                key={t}
                type="button"
                onClick={() => toggleTooth(t)}
                className={`aspect-square flex items-center justify-center rounded-xl border-2 font-black transition-all text-xs
                  ${selectedTeeth.includes(t)
                    ? "bg-primary border-primary text-white scale-110 shadow-lg shadow-primary/30"
                    : "bg-white border-slate-100 text-slate-400 hover:border-primary/30 hover:text-primary"}
                `}
              >
                {t}
              </button>
            ))}
          </div>
        </div>
      </div>
      
      {selectedTeeth.length > 0 && (
        <div className="mt-6 flex flex-wrap gap-2 pt-6 border-t border-slate-200">
          {selectedTeeth.sort((a, b) => a - b).map(t => (
            <span key={t} className="px-2 py-1 bg-primary/10 text-primary text-[10px] font-black rounded-lg border border-primary/20">
              Tooth {t}
            </span>
          ))}
        </div>
      )}
    </div>
  );
}
