import { useState, useEffect, memo } from 'react';
import { Link } from 'react-router-dom';
import api from '../api/axios';
import toast from 'react-hot-toast';
import { handleApiError } from '../utils/errorHandler';
import { Pill, Search, Plus, Trash2, AlertTriangle, Package, DollarSign, TrendingUp, Filter, RefreshCw, ShoppingCart, MessageSquarePlus, Clock, CheckCircle, XCircle, ToggleRight, ToggleLeft, Edit2 } from 'lucide-react';

const Sparkline = memo(({ data }) => {
    if (!data || data.length < 2) return null;
    const max = Math.max(...data, 1);
    const min = Math.min(...data);
    const range = max - min || 1;
    const width = 80;
    const height = 24;

    const points = data.map((val, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - ((val - min) / range) * height;
        return `${x},${y}`;
    }).join(' ');

    const strokeColor = data[data.length - 1] > data[0] ? '#10b981' : (data[data.length - 1] < data[0] ? '#ef4444' : '#94a3b8');

    return (
        <svg width={width} height={height} className="mt-2 overflow-visible">
            <polyline
                fill="none"
                stroke={strokeColor}
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                points={points}
            />
        </svg>
    );
});

const TrendIndicator = ({ delta }) => {
    if (delta === 'New') return <span className="text-[10px] font-black text-emerald-500 uppercase">↑ New</span>;
    if (delta > 0) return <span className="text-[10px] font-black text-emerald-500 uppercase">↑ +{delta}%</span>;
    if (delta < 0) return <span className="text-[10px] font-black text-red-500 uppercase">↓ {delta}%</span>;
    return <span className="text-[10px] font-black text-slate-400 uppercase">→ 0%</span>;
};

export default function Inventory() {
    const [view, setView] = useState('my-stock'); // my-stock | catalog
    const [inventory, setInventory] = useState([]);
    const [catalog, setCatalog] = useState([]);
    const [loading, setLoading] = useState(true);
    const [analytics, setAnalytics] = useState(null);
    const [analyticsLoading, setAnalyticsLoading] = useState(true);
    const [analyticsMode, setAnalyticsMode] = useState('accrued'); // 'accrued' | 'realized'

    // Modals
    const [showAddModal, setShowAddModal] = useState(false);
    const [replenishModal, setReplenishModal] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');

    // Selected Master Item to Add (null = Custom Item)
    const [selectedMasterItem, setSelectedMasterItem] = useState(null);

    // Live Search State
    const [medicineQuery, setMedicineQuery] = useState('');
    const [suggestedMedicines, setSuggestedMedicines] = useState([]);
    const [showSuggestions, setShowSuggestions] = useState(false);

    // Form State (Internal Inventory)
    const [formData, setFormData] = useState({
        item_name: '',
        stock: 0,
        reorder_level: 5,
        purchase_cost: 0,
        sale_price: 0
    });

    const [replenishQty, setReplenishQty] = useState(1);

    useEffect(() => {
        if (view === 'my-stock') {
            fetchInventory();
            fetchAnalytics();
        }
        if (view === 'catalog') fetchCatalog();
    }, [view, analyticsMode]);

    const fetchAnalytics = async () => {
        setAnalyticsLoading(true);
        try {
            const res = await api.get(`/inventory/analytics?mode=${analyticsMode}`);
            setAnalytics(res.data);
        } catch (err) {
            console.error('Failed to fetch analytics:', err);
        } finally {
            setAnalyticsLoading(false);
        }
    };

    const fetchInventory = async () => {
        setLoading(true);
        try {
            const res = await api.get('/inventory');
            setInventory(Array.isArray(res.data) ? res.data : res.data?.data ?? []);
        } catch (err) {
            toast.error('Failed to load inventory');
        } finally {
            setLoading(false);
        }
    };

    const fetchCatalog = async () => {
        setLoading(true);
        try {
            const res = await api.get('/pharmacy/catalog');
            setCatalog(res.data);
        } catch (err) {
            toast.error('Failed to load catalog');
        } finally {
            setLoading(false);
        }
    };

    const handleToggleCatalogItem = (item) => {
        if (item.in_inventory) {
            // If already in inventory, open Manage options
            setManageItem(item);
        } else {
            // If not, open Add modal
            openAddModal(item);
        }
    };

    const openAddModal = (masterItem) => {
        setSelectedMasterItem(masterItem);
        setMedicineQuery(masterItem ? '' : (searchQuery || ''));
        setSuggestedMedicines([]);
        setShowSuggestions(false);

        // If masterItem is null, we are creating a NEW custom item
        if (!masterItem) {
            setFormData({
                item_name: searchQuery || '', // Pre-fill with search query if available
                stock: 0,
                reorder_level: 5,
                purchase_cost: 0,
                sale_price: 0
            });
            setShowAddModal(true);
            setManageItem(null);
            return;
        }

        // If we exist, populate logic
        const existing = masterItem.inventory_item;

        setFormData({
            item_name: masterItem.name,
            stock: existing?.stock || 0,
            reorder_level: existing?.reorder_level || 5,
            purchase_cost: Number(existing?.purchase_cost || masterItem.default_purchase_price || 0),
            sale_price: Number(existing?.sale_price || masterItem.default_selling_price || 0)
        });

        setShowAddModal(true);
        setManageItem(null); // Close manage modal if open
    };

    const handleRemoveCatalogItem = async () => {
        if (!manageItem || !manageItem.inventory_item?.id) return;

        if (!confirm(`Are you sure you want to remove ${manageItem.name} from your inventory? Stock data will be lost.`)) return;

        try {
            await api.delete(`/admin/delete/inventory/${manageItem.inventory_item.id}/archive`);

            // Optimistic Update for instant feel
            setCatalog(catalog.map(c => c.id === manageItem.id ? { ...c, in_inventory: false, inventory_item: null } : c));
            setInventory(inventory.filter(i => i.id !== manageItem.inventory_item.id));

            toast.success('Removed from inventory');
            setManageItem(null);
            fetchAnalytics(); // Refresh analytics after removal

            // HARDENING: Re-fetch to ensure sync
            await Promise.all([fetchCatalog(), fetchInventory()]);

        } catch (err) {
            toast.error('Failed to remove item');
            // Revert on error
            await Promise.all([fetchCatalog(), fetchInventory()]);
        }
    };

    const searchMedicines = async (query) => {
        if (!query || query.length < 2) {
            setSuggestedMedicines([]);
            return;
        }

        try {
            const res = await api.get(`/inventory/search-medicines?q=${query}`);
            setSuggestedMedicines(res.data);
            setShowSuggestions(true);
        } catch (err) {
            return;
        }
    };

    const handleAddToInventory = async (e) => {
        e.preventDefault();
        try {
            // Construct payload: IF master item selected, send ID. ELSE send name (backend creates master).
            const payload = { ...formData };
            if (selectedMasterItem) {
                payload.master_medicine_id = selectedMasterItem.id;
            }
            // If custom, just formData.item_name is used by backend

            const res = await api.post('/inventory', payload);

            const isUpdate = selectedMasterItem && selectedMasterItem.in_inventory;
            toast.success(isUpdate ? 'Inventory updated' : 'Added to inventory');
            setShowAddModal(false);

            // HARDENING: Re-fetch to ensure sync (Single Source of Truth)
            // This guarantees the toggle state is driven by the backend.
            await Promise.all([fetchCatalog(), fetchInventory(), fetchAnalytics()]);

        } catch (err) {
            if (err.response?.status === 409 && err.response?.data?.similar) {
                toast.error('Similar medicines already exist. Please review duplicates.');
                return;
            }
            return;
        }
    };



    // Delete Confirmation State
    const [deleteConfirmation, setDeleteConfirmation] = useState(null);

    const handleReplenish = async (e) => {
        e.preventDefault();
        try {
            const purchaseReference = crypto.randomUUID();
            await api.post(`/inventory/${replenishModal.id}/replenish`, {
                quantity: replenishQty,
                purchase_cost: replenishModal.purchase_cost,
                sale_price: replenishModal.sale_price,
                purchase_reference: purchaseReference
            });
            toast.success('Stock & pricing updated');
            setReplenishModal(null);
            setReplenishQty(1);
            fetchInventory();
            fetchAnalytics(); // Refresh analytics after replenish
        } catch (err) {
            toast.error('Replenishment failed');
        }
    };

    const handleDelete = (item) => {
        setDeleteConfirmation(item);
    };

    const confirmDeleteAction = async () => {
        if (!deleteConfirmation) return;
        try {
            await api.delete(`/admin/delete/inventory/${deleteConfirmation.id}/archive`);
            toast.success('Item removed');
            fetchInventory();
            setDeleteConfirmation(null); // Close modal
        } catch (err) {
            toast.error('Deletion failed');
        }
    };

    const handleToggleSelling = async (item) => {
        try {
            await api.put(`/inventory/${item.id}`, {
                is_selling: !item.is_selling
            });
            toast.success(item.is_selling ? 'Medicine hidden from sales' : 'Medicine available for sale');
            fetchInventory();
            fetchAnalytics(); // Status change might affect prospective analytics logic (though currently status-filtered)
        } catch (err) {
            toast.error('Failed to update selling status');
        }
    };

    const filteredInventory = inventory.filter(item =>
        item.item_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        item.sku?.toLowerCase().includes(searchQuery.toLowerCase())
    );

    const filteredCatalog = catalog.filter(item =>
        item.name.toLowerCase().includes(searchQuery.toLowerCase())
    );

    const totalInventoryValue = inventory.reduce((acc, curr) => acc + (parseFloat(curr.purchase_cost) * curr.stock), 0);
    const itemsBelowReorder = inventory.filter(i => i.stock <= i.reorder_level).length;

    return (
        <div className="space-y-6">
            <header className="flex justify-between items-end">
                <div>
                    <h1 className="text-4xl font-black text-slate-800 tracking-tight">Pharmacy Inventory</h1>
                    <p className="text-slate-500 font-medium mt-1">Manage medicine stock, pricing, and replenishment.</p>
                </div>
                <div className="flex bg-white p-1 rounded-2xl border border-slate-200">
                    <button
                        onClick={() => setView('my-stock')}
                        className={`px-6 py-2 rounded-xl font-bold transition-all ${view === 'my-stock' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50'}`}
                    >
                        My Stock
                    </button>
                    <button
                        onClick={() => setView('catalog')}
                        className={`px-6 py-2 rounded-xl font-bold transition-all ${view === 'catalog' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50'}`}
                    >
                        Browse Catalog
                    </button>
                </div>
            </header>

            {/* MY STOCK VIEW (Table) */}
            {view === 'my-stock' && (
                <>
                    <div className="flex justify-between items-center mb-2">
                        <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Financial Perspective
                        </div>
                        <div className="flex bg-slate-100 p-1 rounded-full border border-slate-200/50">
                            {['accrued', 'realized'].map(m => (
                                <button
                                    key={m}
                                    onClick={() => setAnalyticsMode(m)}
                                    className={`px-3 py-1 text-[10px] font-black uppercase tracking-tight rounded-full transition-all ${analyticsMode === m ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'}`}
                                >
                                    {m}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Alert Layer */}
                    {!analyticsLoading && analytics && (
                        <div className="flex flex-col gap-2 mb-6">
                            {analytics.gross_profit < 0 && (
                                <div className="bg-red-50 text-red-700 text-xs font-medium px-4 py-2 rounded-xl flex items-center gap-2 animate-in slide-in-from-top-1 duration-300">
                                    <AlertTriangle size={14} />
                                    <span>Medicine sales are operating at a net loss in <b>{analyticsMode}</b> mode. Review purchase costs.</span>
                                </div>
                            )}
                            {analytics.gross_profit >= 0 && analytics.margin_percent < 15 && (
                                <div className="bg-amber-50 text-amber-700 text-xs font-medium px-4 py-2 rounded-xl flex items-center gap-2 animate-in slide-in-from-top-1 duration-300">
                                    <AlertTriangle size={14} />
                                    <span>Alert: Low profit margins detected ({analytics.margin_percent}%). Review medicine pricing strategy.</span>
                                </div>
                            )}
                            {analytics.low_stock_count > 0 && (
                                <button
                                    onClick={() => document.getElementById('inventory-table')?.scrollIntoView({ behavior: 'smooth' })}
                                    className="w-full bg-slate-50 text-slate-600 hover:bg-slate-100 text-xs font-medium px-4 py-2 rounded-xl flex items-center justify-between group transition-all"
                                >
                                    <div className="flex items-center gap-2">
                                        <Package size={14} />
                                        <span>Pharmacy Alert: {analytics.low_stock_count} medicines are below threshold and require replenishment.</span>
                                    </div>
                                    <span className="text-[10px] uppercase font-black opacity-40 group-hover:opacity-100 transition-all flex items-center gap-1">Manage Stock <TrendingUp size={10} /></span>
                                </button>
                            )}
                        </div>
                    )}

                    {/* Analytics Strip */}
                    <div className="flex flex-wrap items-center gap-x-16 gap-y-6 py-6 border-b border-slate-100 mb-8 overflow-hidden transition-all">
                        {analyticsLoading ? (
                            // Skeleton UI
                            [...Array(6)].map((_, i) => (
                                <div key={i} className="animate-pulse space-y-2">
                                    <div className="h-3 w-16 bg-slate-100 rounded-full"></div>
                                    <div className="h-6 w-24 bg-slate-50 rounded-lg"></div>
                                </div>
                            ))
                        ) : analytics && (
                            <>
                                <div>
                                    <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Floor Value</p>
                                    <p className="text-xl font-bold text-slate-900">₹{analytics.floor_value.toLocaleString()}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Monthly Revenue</p>
                                    <div className="flex flex-col">
                                        <div className="flex items-center gap-4">
                                            <p className="text-xl font-bold text-slate-900">₹{analytics.monthly_revenue.toLocaleString()}</p>
                                            <Sparkline data={analytics.sparkline} />
                                        </div>
                                        <TrendIndicator delta={analytics.trend.revenue_delta} />
                                    </div>
                                </div>
                                <div>
                                    <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Monthly COGS</p>
                                    <p className="text-xl font-bold text-slate-900">₹{analytics.monthly_cogs.toLocaleString()}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gross Profit</p>
                                    <div className="flex flex-col">
                                        <p className={`text-xl font-black ${analytics.gross_profit >= 0 ? 'text-emerald-600' : 'text-red-500'}`}>
                                            ₹{analytics.gross_profit.toLocaleString()}
                                        </p>
                                        <TrendIndicator delta={analytics.trend.profit_delta} />
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <div>
                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Margin %</p>
                                        <div className="flex items-center gap-2">
                                            <p className="text-xl font-bold text-slate-900">{analytics.margin_percent}%</p>
                                            {analytics.margin_percent > 40 && <div className="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200" title="High Efficiency"></div>}
                                            {analytics.margin_percent < 10 && analytics.margin_percent > 0 && <div className="w-2 h-2 rounded-full bg-orange-400 animate-pulse" title="Thin Margin"></div>}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Low Stock</p>
                                    <p className={`text-xl font-bold ${analytics.low_stock_count > 0 ? 'text-orange-500' : 'text-slate-900'}`}>{analytics.low_stock_count} SKUs</p>
                                </div>
                            </>
                        )}
                    </div>

                    <div id="inventory-table" className="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
                        {!Array.isArray(inventory) ? null : (
                            <>
                                <div className="flex justify-between items-center mb-6">
                                    <div className="relative flex-1 max-w-lg">
                                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                                        <input
                                            type="text"
                                            placeholder="Search My Stock..."
                                            className="w-full pl-12 pr-4 py-3 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-primary/10 font-bold"
                                            value={searchQuery}
                                            onChange={(e) => setSearchQuery(e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="overflow-x-auto">
                                    <table className="w-full text-left">
                                        <thead>
                                            <tr className="border-b border-slate-100">
                                                <th className="px-6 py-5 font-black text-slate-400 text-[10px] uppercase tracking-widest">Medicine & SKU</th>
                                                <th className="px-6 py-5 font-black text-slate-400 text-[10px] uppercase tracking-widest">Stock Level</th>
                                                <th className="px-6 py-5 font-black text-slate-400 text-[10px] uppercase tracking-widest">Pricing</th>
                                                <th className="px-6 py-5 font-black text-slate-400 text-[10px] uppercase tracking-widest">Selling</th>
                                                <th className="px-6 py-5 font-black text-slate-400 text-[10px] uppercase tracking-widest text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-50">
                                            {loading ? (
                                                <tr><td colSpan="5" className="px-6 py-8 text-center text-slate-400">Loading stock...</td></tr>
                                            ) : filteredInventory.length === 0 ? (
                                                <tr>
                                                    <td colSpan="5" className="px-6 py-12 text-center">
                                                        <div className="flex flex-col items-center gap-4">
                                                            <p className="text-slate-400 font-medium italic">Empty inventory.</p>
                                                            <button
                                                                onClick={() => setView('catalog')}
                                                                className="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20"
                                                            >
                                                                <Plus size={18} /> Add Medicine to Inventory
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ) : (
                                                filteredInventory.map(item => (
                                                    <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                                                        <td className="px-6 py-5">
                                                            {(() => {
                                                                const isGlobalMedicine = Boolean(item.master_medicine_id);
                                                                return (
                                                                    <>
                                                                        <div className="flex items-center">
                                                                            <div className="font-black text-slate-800">{item.item_name}</div>
                                                                            {isGlobalMedicine ? (
                                                                                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-700 ml-2 uppercase tracking-tight">
                                                                                    🌍 GLOBAL MEDICINE
                                                                                </span>
                                                                            ) : (
                                                                                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-700 ml-2 uppercase tracking-tight">
                                                                                    🏥 LOCAL MEDICINE
                                                                                </span>
                                                                            )}
                                                                        </div>
                                                                        <div className="flex flex-col">
                                                                            <div className="text-[10px] font-bold text-slate-600 uppercase tracking-tighter">SKU: {item.sku || 'N/A'}</div>
                                                                            {isGlobalMedicine ? (
                                                                                <span className="text-[10px] text-emerald-600 font-semibold mt-0.5">
                                                                                    Standard medicine from global registry
                                                                                </span>
                                                                            ) : (
                                                                                <span className="text-[10px] text-amber-600 font-semibold mt-0.5">
                                                                                    Private medicine • Only visible in your clinic
                                                                                </span>
                                                                            )}
                                                                        </div>
                                                                    </>
                                                                );
                                                            })()}
                                                        </td>
                                                        <td className="px-6 py-5">
                                                            <div className="flex flex-col">
                                                                <div className={`font-black text-lg ${item.stock <= item.reorder_level ? 'text-orange-600' : 'text-slate-800'}`}>{item.stock} Units</div>
                                                                <div className="text-[10px] font-bold text-slate-600 uppercase">Reorder @ {item.reorder_level}</div>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-5">
                                                            <div className="text-xs font-bold text-slate-600 uppercase tracking-tight">Cost: ₹{item.purchase_cost}</div>
                                                            <div className="text-sm font-black text-primary">SRP: ₹{item.sale_price}</div>
                                                        </td>
                                                        <td className="px-6 py-5">
                                                            <button
                                                                onClick={() => handleToggleSelling(item)}
                                                                className={`flex items-center gap-2 px-3 py-1.5 rounded-lg font-bold text-xs transition-all ${item.is_selling
                                                                    ? 'bg-green-50 text-green-600 hover:bg-green-100'
                                                                    : 'bg-slate-100 text-slate-400 hover:bg-slate-200'
                                                                    }`}
                                                            >
                                                                {item.is_selling ? <CheckCircle size={14} /> : <XCircle size={14} />}
                                                                {item.is_selling ? 'Selling' : 'Not Selling'}
                                                            </button>
                                                        </td>
                                                        <td className="px-6 py-5 text-right">
                                                            <div className="flex justify-end gap-2">
                                                                <button onClick={() => setReplenishModal(item)} className="p-2 bg-green-50 text-green-600 rounded-xl hover:bg-green-100 transition-all"><RefreshCw size={20} /></button>
                                                                <button onClick={() => handleDelete(item)} className="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-all"><Trash2 size={20} /></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </>
                        )}
                    </div>
                </>
            )}

            {/* CATALOG VIEW (Cards with Toggles) */}
            {view === 'catalog' && (
                <div className="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <div className="flex justify-between items-center mb-6">
                        <div className="relative flex-1 max-w-lg">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                            <input
                                type="text"
                                placeholder="Search master catalog..."
                                className="w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-50 font-bold border-none outline-none focus:ring-2 focus:ring-primary/20"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                            />
                        </div>
                        <div className="flex gap-3">
                            <div className="px-4 py-2 bg-slate-50 text-slate-500 text-xs font-bold uppercase rounded-lg border border-slate-100 flex items-center">
                                Total Catalog: {catalog.length}
                            </div>
                            <button onClick={() => openAddModal(null)} className="ml-4 px-6 py-4 bg-primary text-white font-bold rounded-2xl hover:bg-primary-dark transition-all flex items-center gap-2">
                                <Plus size={20} /> Add Custom Item
                            </button>
                        </div>
                    </div>

                    {loading ? (
                        <div className="text-center py-12 text-slate-400 animate-pulse">Loading catalog...</div>
                    ) : filteredCatalog.length === 0 ? (
                        <div className="text-center py-12 text-slate-400">No medicines found match your search.</div>
                    ) : (
                        <div className="space-y-3">
                            {filteredCatalog.map(item => (
                                <div key={item.id} className={`p-4 rounded-2xl border transition-all flex items-center justify-between ${item.in_inventory ? 'bg-white border-slate-100 hover:border-slate-200' : 'bg-slate-50 border-slate-100 opacity-60'}`}>
                                    <div className="flex items-center gap-4">
                                        <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${item.in_inventory ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400'}`}>
                                            <Pill size={20} />
                                        </div>
                                        <div>
                                            <h3 className="font-bold text-slate-800 text-lg">{item.name}</h3>
                                            <p className="text-xs text-slate-600 font-bold uppercase">{item.category} • {item.unit}</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-6">
                                        <div className="flex flex-col items-end">
                                            <label className="text-[10px] uppercase font-black text-slate-400 mb-1">Status</label>
                                            {item.in_inventory ? (
                                                <span className="font-bold text-green-600 text-sm">In Stock ({item.inventory_item?.stock} units)</span>
                                            ) : (
                                                <span className="font-bold text-slate-400 text-sm">Not Stocked</span>
                                            )}
                                        </div>
                                        <div className="w-px h-10 bg-slate-100"></div>
                                        <button
                                            onClick={() => handleToggleCatalogItem(item)}
                                            className={`transition-all ${item.in_inventory ? 'text-primary' : 'text-slate-300'}`}
                                        >
                                            {item.in_inventory ? <ToggleRight size={40} /> : <ToggleLeft size={40} />}
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}



            {/* ADD TO INVENTORY MODAL */}
            {showAddModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-lg rounded-[2.5rem] p-8 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                        <h2 className="text-2xl font-black text-slate-800 mb-2">
                            {selectedMasterItem ? 'Add to Inventory' : 'Add Custom Item'}
                        </h2>
                        <p className="text-slate-500 font-medium mb-6">
                            {selectedMasterItem ?
                                <span>Set your pricing and stock for <span className="text-primary font-bold underline">{selectedMasterItem.name}</span></span> :
                                'Create a new medicine entry in your inventory.'
                            }
                        </p>

                        <form onSubmit={handleAddToInventory} className="space-y-4">
                            {!selectedMasterItem && (
                                <div className="relative">
                                    <label className="text-xs font-black text-slate-400 uppercase block mb-2">Medicine Name</label>
                                    <input
                                        type="text"
                                        value={medicineQuery}
                                        onChange={(e) => {
                                            const val = e.target.value;
                                            setMedicineQuery(val);
                                            setFormData({ ...formData, item_name: val });
                                            searchMedicines(val);
                                        }}
                                        className="w-full px-4 py-3 border rounded-xl font-bold bg-slate-50 outline-none focus:ring-2 focus:ring-primary"
                                        placeholder="Type medicine name..."
                                        autoComplete="off"
                                    />
                                    {/* Suggestions Dropdown */}
                                    {showSuggestions && suggestedMedicines.length > 0 && (
                                        <div className="absolute top-full left-0 right-0 mt-2 bg-white border border-slate-100 rounded-xl shadow-xl max-h-48 overflow-y-auto z-50">
                                            {suggestedMedicines.map((med) => (
                                                <div
                                                    key={med.id}
                                                    className="px-4 py-3 hover:bg-slate-50 cursor-pointer font-bold text-slate-700 flex justify-between items-center group"
                                                    onClick={() => {
                                                        setSelectedMasterItem(med);
                                                        setMedicineQuery(med.name);
                                                        setFormData({ ...formData, item_name: med.name });
                                                        setShowSuggestions(false);
                                                    }}
                                                >
                                                    {med.name}
                                                    <span className="text-xs font-normal text-slate-400 group-hover:text-primary">Select</span>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="text-xs font-black text-slate-400 uppercase block mb-2">Initial Stock</label>
                                    <input type="number" required min="0" value={formData.stock} onChange={e => setFormData({ ...formData, stock: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 font-bold outline-none focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label className="text-xs font-black text-slate-400 uppercase block mb-2">Reorder Alert Level</label>
                                    <input type="number" required min="0" value={formData.reorder_level} onChange={e => setFormData({ ...formData, reorder_level: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 font-bold outline-none focus:ring-2 focus:ring-primary" />
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="text-xs font-black text-slate-400 uppercase block mb-2">Your Cost (Buy)</label>
                                    <input type="number" step="0.01" required min="0" value={formData.purchase_cost} onChange={e => setFormData({ ...formData, purchase_cost: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 font-bold outline-none focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label className="text-xs font-black text-slate-400 uppercase block mb-2">Your Price (Sell)</label>
                                    <input type="number" step="0.01" required min="0" value={formData.sale_price} onChange={e => setFormData({ ...formData, sale_price: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 font-bold outline-none focus:ring-2 focus:ring-primary" />
                                </div>
                            </div>
                            {Number(formData.sale_price) > 0 && Number(formData.purchase_cost) > 0 && Number(formData.sale_price) < Number(formData.purchase_cost) && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2 text-red-700">
                                    <AlertTriangle size={16} />
                                    <span className="text-xs font-bold">Selling price cannot be less than buying price</span>
                                </div>
                            )}
                            <div className="flex gap-4 pt-4">
                                <button type="button" onClick={() => setShowAddModal(false)} className="flex-1 py-3 font-bold text-slate-400 hover:bg-slate-50 rounded-xl">Cancel</button>
                                <button
                                    type="submit"
                                    disabled={Number(formData.sale_price) > 0 && Number(formData.purchase_cost) > 0 && Number(formData.sale_price) < Number(formData.purchase_cost)}
                                    className="flex-1 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary"
                                >
                                    Add Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}



            {/* REPLENISH MODAL */}
            {replenishModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl">
                        <h2 className="text-2xl font-black text-slate-800 mb-2">Restock Medicine</h2>
                        <p className="text-slate-500 text-sm mb-6 font-medium">Update stock and pricing for <span className="text-primary font-black">{replenishModal.item_name}</span></p>

                        <form onSubmit={handleReplenish} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-[10px] font-black text-slate-600 uppercase mb-1">Add Units</label>
                                    <input
                                        type="number" min="1" required
                                        value={replenishQty}
                                        onChange={(e) => setReplenishQty(e.target.value)}
                                        className="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary/20 font-black text-xl text-center"
                                    />
                                </div>
                                <div className="flex flex-col justify-end pb-3">
                                    <div className="text-right">
                                        <p className="text-[10px] font-black text-slate-600 uppercase">New Stock Level</p>
                                        <p className="text-xl font-black text-green-600">{(parseInt(replenishModal.stock) + parseInt(replenishQty || 0))} Units</p>
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 bg-slate-50 rounded-2xl space-y-3">
                                <div className="flex justify-between items-center">
                                    <label className="text-xs font-bold text-slate-600 uppercase">Unit Cost (Buy)</label>
                                    <input
                                        type="number" step="0.01" required
                                        value={replenishModal.purchase_cost}
                                        onChange={(e) => setReplenishModal({ ...replenishModal, purchase_cost: e.target.value })}
                                        className="w-24 text-right bg-white px-2 py-1 rounded-lg border border-slate-200 font-bold text-sm outline-none focus:border-primary"
                                    />
                                </div>
                                <div className="flex justify-between items-center">
                                    <label className="text-xs font-bold text-slate-600 uppercase">Unit Price (Sell)</label>
                                    <input
                                        type="number" step="0.01" required
                                        value={replenishModal.sale_price}
                                        onChange={(e) => setReplenishModal({ ...replenishModal, sale_price: e.target.value })}
                                        className="w-24 text-right bg-white px-2 py-1 rounded-lg border border-slate-200 font-bold text-sm outline-none focus:border-primary"
                                    />
                                </div>
                                <div className="border-t border-slate-200 my-2"></div>
                                <div className="flex justify-between items-center">
                                    <label className="text-xs font-black text-slate-600 uppercase">Total Stock Value</label>
                                    <span className="font-black text-slate-800">₹{((Number(replenishModal.stock || 0) + Number(replenishQty || 0)) * Number(replenishModal.purchase_cost || 0)).toFixed(2)}</span>
                                </div>
                            </div>

                            {Number(replenishModal.sale_price) > 0 && Number(replenishModal.purchase_cost) > 0 && Number(replenishModal.sale_price) < Number(replenishModal.purchase_cost) && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2 text-red-700 mt-4">
                                    <AlertTriangle size={16} />
                                    <span className="text-xs font-bold">Selling price cannot be less than buying price</span>
                                </div>
                            )}

                            <div className="flex gap-3 mt-6">
                                <button type="button" onClick={() => setReplenishModal(null)} className="flex-1 py-3 font-bold text-slate-400 hover:bg-slate-50 rounded-xl transition-all">Cancel</button>
                                <button
                                    type="submit"
                                    disabled={Number(replenishModal.sale_price) > 0 && Number(replenishModal.purchase_cost) > 0 && Number(replenishModal.sale_price) < Number(replenishModal.purchase_cost)}
                                    className="flex-1 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary"
                                >
                                    Confirm Stock
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* DELETE MODAL */}
            {deleteConfirmation && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-sm rounded-[2.5rem] p-8 shadow-2xl text-center">
                        <div className="w-16 h-16 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <Trash2 size={32} />
                        </div>
                        <h2 className="text-xl font-black text-slate-800 mb-2">Remove Item?</h2>
                        <p className="text-slate-500 font-medium text-sm mb-6">
                            Are you sure you want to remove <span className="text-slate-800 font-bold">{deleteConfirmation.item_name}</span>?
                            This action cannot be undone.
                        </p>
                        <div className="flex gap-3">
                            <button onClick={() => setDeleteConfirmation(null)} className="flex-1 py-3 font-bold text-slate-400 hover:bg-slate-50 rounded-xl transition-all">Cancel</button>
                            <button onClick={confirmDeleteAction} className="flex-1 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 shadow-lg shadow-red-200 transition-all">Yes, Remove</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
