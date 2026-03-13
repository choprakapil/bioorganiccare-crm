import React from 'react';
import { History, Plus, Calendar, CreditCard } from 'lucide-react';

/**
 * ToothHistoryPanel Component
 * Displays clinical history for a specific tooth.
 */
export default function ToothHistoryPanel({ 
  toothId, 
  history = [], 
  onAddTreatment 
}) {
  if (!toothId) return null;

  return (
    <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden animate-in slide-in-from-right-4 duration-500">
      <div className="bg-slate-50 p-6 border-b border-slate-100 flex justify-between items-center">
        <div>
          <h3 className="text-lg font-black text-slate-800 flex items-center gap-2">
            <History size={20} className="text-primary" />
            History for Tooth #{toothId}
          </h3>
          <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
            {history.length} Previous Procedures
          </p>
        </div>
        <button
          onClick={() => onAddTreatment(toothId)}
          className="p-2 bg-primary text-white rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20"
          title="Add Treatment"
        >
          <Plus size={20} />
        </button>
      </div>

      <div className="p-6 max-h-[400px] overflow-y-auto space-y-4 custom-scrollbar">
        {history.length === 0 ? (
          <div className="text-center py-12">
            <div className="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
              <History size={24} className="text-slate-300" />
            </div>
            <p className="text-sm font-bold text-slate-400">No history found for this tooth.</p>
          </div>
        ) : (
          history.map((item, index) => (
            <div 
              key={item.id || index} 
              className="relative pl-6 pb-4 border-l-2 border-slate-100 last:border-0 last:pb-0"
            >
              {/* Timeline Dot */}
              <div className="absolute left-[-9px] top-1 w-4 h-4 rounded-full bg-white border-2 border-primary shadow-sm" />
              
              <div className="bg-slate-50/50 p-4 rounded-2xl border border-slate-100 hover:border-slate-200 transition-all group">
                <div className="flex justify-between items-start mb-2">
                  <span className="text-sm font-black text-slate-800 group-hover:text-primary transition-colors">
                    {item.procedure_name}
                  </span>
                  <div className="flex items-center gap-1 text-[10px] font-bold text-slate-400">
                    <Calendar size={12} />
                    {new Date(item.created_at).toLocaleDateString('en-IN', {
                      day: '2-digit',
                      month: 'short',
                      year: 'numeric'
                    })}
                  </div>
                </div>
                
                <div className="flex justify-between items-center mt-3">
                  <div className="flex items-center gap-1.5 text-xs font-black text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-100 shadow-sm">
                    <CreditCard size={12} className="text-slate-400" />
                    ₹{Number(item.fee).toLocaleString()}
                  </div>
                  <span className={`text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-md ${
                    item.status === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'
                  }`}>
                    {item.status}
                  </span>
                </div>
                
                {item.notes && (
                  <p className="mt-3 text-[10px] text-slate-500 font-medium italic border-t border-slate-100 pt-2 line-clamp-2">
                    "{item.notes}"
                  </p>
                )}
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
