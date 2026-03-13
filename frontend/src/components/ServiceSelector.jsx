import React from 'react';
import { Check, Info } from 'lucide-react';

/**
 * Shared Service Selector Component
 * Renders clinical services as interactive grid cards with price override support.
 */
export default function ServiceSelector({ 
  services = [], 
  selectedServices = [], 
  onToggleService, 
  onPriceChange 
}) {
  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <label className="block text-sm font-black text-slate-700">Select Clinical Services</label>
        <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-1 rounded-lg">
          {selectedServices.length} Selected
        </span>
      </div>

      <div className="grid grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
        {services.length === 0 && (
          <div className="col-span-2 text-center py-8 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-100">
            <p className="text-xs text-slate-400 font-medium">No services available in catalog.</p>
          </div>
        )}
        
        {services.map(service => {
          const isSelected = selectedServices.some(s => (s.react_key === service.react_key) || (s.id === service.id));
          
          return (
            <button
              key={service.react_key || service.id}
              type="button"
              onClick={() => onToggleService(service)}
              className={`relative p-4 rounded-2xl border-2 text-left transition-all group overflow-hidden ${
                isSelected 
                  ? 'border-primary bg-primary/5 ring-4 ring-primary/5' 
                  : 'border-slate-100 bg-white hover:border-slate-200 hover:shadow-md'
              }`}
            >
              {isSelected && (
                <div className="absolute top-2 right-2 w-5 h-5 bg-primary text-white rounded-full flex items-center justify-center animate-in zoom-in duration-200">
                  <Check size={12} strokeWidth={4} />
                </div>
              )}
              
              <div className={`font-bold text-sm mb-1 truncate pr-6 ${isSelected ? 'text-primary' : 'text-slate-700'}`}>
                {service.item_name}
              </div>
              
              <div className="text-xs font-black text-slate-400">
                ₹{service.default_fee}
              </div>

              {/* Hover effect overlay */}
              <div className="absolute inset-0 bg-primary/0 group-hover:bg-primary/2 transition-colors pointer-events-none" />
            </button>
          );
        })}
      </div>

      {/* Price Overrides Section */}
      {selectedServices.length > 0 && (
        <div className="animate-in fade-in slide-in-from-top-4 duration-500">
          <div className="flex items-center gap-2 mb-3">
             <Info size={14} className="text-slate-400" />
             <h3 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Adjust Billing Prices</h3>
          </div>
          
          <div className="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
            {selectedServices.map(s => (
              <div 
                key={s.react_key || s.id} 
                className="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl shadow-sm animate-in fade-in slide-in-from-right-2 duration-300"
              >
                <div className="flex-1 overflow-hidden">
                  <div className="text-sm font-bold text-slate-700 truncate">{s.item_name}</div>
                  <div className="text-[10px] text-slate-400 italic">Catalog: ₹{s.default_fee}</div>
                </div>
                
                <div className="flex items-center gap-2">
                  <span className="text-xs font-black text-slate-400">₹</span>
                  <input
                    type="number"
                    className="w-24 px-3 py-2 bg-slate-50 border border-slate-100 rounded-lg text-sm font-black text-slate-800 text-right focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                    value={s.billed_price ?? s.default_fee}
                    onChange={(e) => onPriceChange(s.react_key || s.id, e.target.value)}
                  />
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
