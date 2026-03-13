import React from 'react';
import QuantityController from './QuantityController';
import { AlertTriangle, CheckCircle2 } from 'lucide-react';

/**
 * Shared Medicine Selector with Stock Management
 */
export default function MedicineSelector({ 
  inventoryItems = [], 
  selectedMedicine, 
  onSelectMedicine, 
  quantity, 
  onQuantityChange,
  unitPrice,
  onPriceChange
}) {
  const selectedInventory = inventoryItems.find(i => i.id == selectedMedicine);

  return (
    <div className="space-y-6">
      {/* Medicine Dropdown */}
      <div>
        <label className="block text-sm font-black text-slate-700 mb-2">Select Medicine from Inventory</label>
        <select
          required
          className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-green-500/10 transition-all bg-slate-50 font-bold text-slate-800"
          value={selectedMedicine === 'pending' ? '' : (selectedMedicine || '')}
          onChange={(e) => onSelectMedicine(e.target.value)}
        >
          <option value="">-- Choose Medicine --</option>
          {inventoryItems.map(i => (
            <option key={i.id} value={i.id} disabled={i.stock === 0}>
              {i.item_name} — ₹{i.sale_price} ({i.stock} avail)
            </option>
          ))}
        </select>

        {selectedInventory && (
          <div className={`mt-3 p-4 rounded-2xl border flex items-center justify-between transition-all ${
            selectedInventory.stock === 0 ? 'bg-red-50 border-red-200 text-red-700' :
            selectedInventory.stock <= selectedInventory.reorder_level ? 'bg-orange-50 border-orange-100 text-orange-700' :
            'bg-green-50 border-green-100 text-green-700'
          }`}>
            <div className="flex items-center gap-3">
              {selectedInventory.stock === 0 ? <AlertTriangle size={18} /> :
               selectedInventory.stock <= selectedInventory.reorder_level ? <AlertTriangle size={18} /> :
               <CheckCircle2 size={18} />}
              <span className="text-xs font-black uppercase tracking-widest leading-none">
                {selectedInventory.stock === 0 ? 'OUT OF STOCK' : `Stock: ${selectedInventory.stock} Units`}
              </span>
            </div>
          </div>
        )}
      </div>

      {/* Quantity & Price Controller (Only shown if selected) */}
      {selectedInventory && (
        <div className="animate-in fade-in slide-in-from-top-2 duration-300 space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">Quantity</label>
              <QuantityController
                value={quantity || 1}
                max={selectedInventory.stock}
                onChange={onQuantityChange}
              />
            </div>
            <div>
              <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">Unit Price (₹)</label>
              <input 
                type="number"
                value={unitPrice || selectedInventory.sale_price}
                onChange={(e) => onPriceChange(e.target.value)}
                className="w-full h-[60px] px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-green-500/10 transition-all bg-white font-black text-slate-800 text-lg"
              />
            </div>
          </div>
          
          <div className="bg-slate-900 p-4 rounded-2xl flex justify-between items-center text-white">
            <span className="text-[10px] font-black uppercase tracking-widest opacity-60">Total Estimated</span>
            <span className="text-xl font-black">₹{( (unitPrice || selectedInventory.sale_price) * (quantity || 1) ).toLocaleString()}</span>
          </div>

          {quantity > selectedInventory.stock && (
            <p className="text-xs font-bold text-red-600 mt-2 flex items-center gap-1">
              <AlertTriangle size={12} /> Cannot exceed available stock ({selectedInventory.stock} units)
            </p>
          )}
        </div>
      )}
    </div>
  );
}
