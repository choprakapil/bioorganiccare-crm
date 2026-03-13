import { useState, useEffect } from 'react';
import api from '../api/axios';
import { useAuth } from '../context/AuthContext';
import toast from 'react-hot-toast';
import { LayoutList, Search, ToggleRight, ToggleLeft, Edit2, Check, X, Stethoscope, IndianRupee, Plus } from 'lucide-react';

export default function ClinicalServices() {
    const { user } = useAuth();
    const [view, setView] = useState('my-services'); // my-services | catalog
    const [services, setServices] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [editingId, setEditingId] = useState(null);
    const [editPrice, setEditPrice] = useState('');

    // Add Service State
    const [showAddModal, setShowAddModal] = useState(false);
    const [addForm, setAddForm] = useState({ item_name: '', type: 'Treatment', default_fee: '' });

    useEffect(() => {
        if (view === 'my-services' || view === 'catalog') fetchServices();
    }, [view]);

    const fetchServices = async () => {
        setLoading(true);
        try {
            const apiView = view === 'my-services' ? 'my-services' : 'catalog';
            const res = await api.get(`/clinical-catalog?view=${apiView}`);
            setServices(res.data);
        } catch (err) {
            toast.error('Failed to load services');
        } finally {
            setLoading(false);
        }
    };



    const handleToggleActive = async (service) => {
        const newStatus = !service.is_active;
        const oldServices = [...services];
        setServices(services.map(s => s.react_key === service.react_key ? { ...s, is_active: newStatus } : s));

        try {
            await api.post('/clinical-catalog/settings', {
                id: service.id,
                type: service.is_local ? 'local' : 'global',
                custom_price: service.default_fee,
                is_active: newStatus
            });
            toast.success(newStatus ? 'Service enabled' : 'Service disabled');
        } catch (err) {
            setServices(oldServices);
            toast.error('Failed to update status');
        }
    };

    const startEditing = (service) => {
        setEditingId(service.react_key);
        setEditPrice(service.default_fee);
    };

    const savePrice = async (service) => {
        if (!editPrice || isNaN(editPrice) || Number(editPrice) < 0) {
            toast.error('Invalid price');
            return;
        }

        try {
            await api.post('/clinical-catalog/settings', {
                id: service.id,
                type: service.is_local ? 'local' : 'global',
                custom_price: editPrice,
                is_active: service.is_active
            });

            setServices(services.map(s => s.react_key === service.react_key ? { ...s, default_fee: Number(editPrice) } : s));
            setEditingId(null);
            toast.success('Price updated');
        } catch (err) {
            toast.error('Failed to update price');
        }
    };

    const handleCreateService = async (e) => {
        e.preventDefault();
        try {
            const res = await api.post('/clinical-catalog', {
                item_name: addForm.item_name,
                type: addForm.type,
                default_fee: addForm.default_fee
            });

            if (res.status === 202) {
                // New service — submitted for approval, not yet in catalog
                toast('Service submitted for approval. It will be visible once an admin approves it.', { icon: '⏳' });
                setServices(prev => [...prev, {
                    id: 'temp-' + Date.now(),
                    react_key: 'temp-' + Date.now(),
                    item_name: addForm.item_name,
                    type: addForm.type,
                    default_fee: Number(addForm.default_fee),
                    original_fee: Number(addForm.default_fee),
                    is_active: false,
                    approval_status: 'pending',
                    category: null
                }]);
            } else {
                toast.success('Service created and enabled');
                fetchServices();
            }

            setShowAddModal(false);
            setAddForm({ item_name: '', type: 'Treatment', default_fee: '' });
        } catch (err) {
            toast.error('Failed to create service');
        }
    };

    const filteredServices = services.filter(s => {
        const matchesSearch = s.item_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            s.category?.name?.toLowerCase().includes(searchTerm.toLowerCase());

        if (view === 'my-services') {
            return matchesSearch && s.is_active;
        }
        return matchesSearch; // In catalog view, show all
    });

    return (
        <div className="space-y-6">
            <header className="flex justify-between items-end">
                <div>
                    <h1 className="text-4xl font-black text-slate-800 tracking-tight">Clinical Services</h1>
                    <p className="text-slate-500 font-medium mt-1">
                        {view === 'my-services'
                            ? "Manage your private sandbox. You can create private services here; they are visible only to you unless promoted to the Global Catalogue."
                            : "Standardized clinical procedures and consultations approved by the administration."}
                    </p>
                </div>
                <div className="flex bg-white p-1 rounded-2xl border border-slate-200">
                    <button
                        onClick={() => setView('my-services')}
                        className={`px-6 py-2 rounded-xl font-bold transition-all ${view === 'my-services' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50'}`}
                    >
                        My Services
                    </button>
                    <button
                        onClick={() => setView('catalog')}
                        className={`px-6 py-2 rounded-xl font-bold transition-all ${view === 'catalog' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50'}`}
                    >
                        Browse Catalog
                    </button>
                </div>
            </header>


            <div className="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div className="flex justify-between items-center mb-6">
                    <div className="relative flex-1 max-w-lg">
                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                        <input
                            type="text"
                            placeholder={view === 'my-services' ? "Search your active services..." : "Search master catalog..."}
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full pl-12 pr-4 py-4 rounded-2xl bg-slate-50 font-bold border-none outline-none focus:ring-2 focus:ring-primary/20"
                        />
                    </div>
                    <div className="flex gap-3">
                        {view === 'catalog' && (
                            <div className="px-4 py-2 bg-slate-50 text-slate-500 text-xs font-bold uppercase rounded-lg border border-slate-100 flex items-center">
                                Total Catalog: {services.length}
                            </div>
                        )}
                        <button onClick={() => setShowAddModal(true)} className="ml-4 px-6 py-4 bg-primary text-white font-bold rounded-2xl hover:bg-primary-dark transition-all flex items-center gap-2">
                            <Plus size={20} /> Add Service
                        </button>
                    </div>
                </div>

                {loading ? (
                    <div className="text-center py-12 text-slate-400 animate-pulse">Loading service catalog...</div>
                ) : filteredServices.length === 0 ? (
                    <div className="text-center py-12">
                        <p className="text-slate-400 mb-4">No services found.</p>
                        <button
                            onClick={() => setShowAddModal(true)}
                            className="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark transition-all shadow-lg shadow-primary/20"
                        >
                            <Plus size={18} /> Add New Service
                        </button>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {filteredServices.map(service => (
                            <div key={service.react_key} className={`p-4 rounded-2xl border transition-all flex items-center justify-between ${service.is_active ? 'bg-white border-slate-100 hover:border-slate-200' : 'bg-slate-50 border-slate-100 opacity-60'}`}>
                                <div className="flex items-center gap-4">
                                    <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${service.is_active ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400'}`}>
                                        <Stethoscope size={20} />
                                    </div>
                                    <div>
                                        <div className="flex items-center">
                                            <h3 className="font-bold text-slate-800 text-lg">{service.item_name}</h3>
                                            {(() => {
                                                const isLocalService = Boolean(service.is_local);
                                                return isLocalService ? (
                                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-700 ml-2 uppercase tracking-tight">
                                                        🧪 LOCAL SERVICE
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-700 ml-2 uppercase tracking-tight">
                                                        🌍 GLOBAL SERVICE
                                                    </span>
                                                );
                                            })()}
                                        </div>
                                        <div className="flex items-center gap-2 mt-0.5">
                                            <p className="text-xs text-slate-600 font-bold uppercase">{service.type} • Base Price: ₹{service.original_fee}</p>
                                            {service.approval_status === 'pending' && (
                                                <span className="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-bold">
                                                    ⏳ Pending Approval
                                                </span>
                                            )}
                                            {Boolean(service.is_local) && (
                                                <span className="text-[10px] text-amber-600 font-bold uppercase tracking-tight">
                                                    • Pending promotion
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-6">
                                    <div className="flex flex-col items-end">
                                        <label className="text-[10px] uppercase font-black text-slate-600 mb-1">Clinic Fee</label>
                                        {editingId === service.react_key ? (
                                            <div className="flex items-center gap-2">
                                                <div className="relative w-24">
                                                    <IndianRupee size={12} className="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400" />
                                                    <input
                                                        type="number"
                                                        value={editPrice}
                                                        onChange={(e) => setEditPrice(e.target.value)}
                                                        className="w-full pl-6 pr-2 py-2 rounded-lg border border-primary outline-none text-sm font-bold"
                                                        autoFocus
                                                    />
                                                </div>
                                                <button onClick={() => savePrice(service)} className="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200"><Check size={16} /></button>
                                                <button onClick={() => setEditingId(null)} className="p-2 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200"><X size={16} /></button>
                                            </div>
                                        ) : (
                                            <button
                                                onClick={() => startEditing(service)}
                                                disabled={!service.is_active}
                                                className="flex items-center gap-2 font-black text-xl text-slate-800 hover:text-primary transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                ₹{service.default_fee} <Edit2 size={14} className="text-slate-500" />
                                            </button>
                                        )}
                                    </div>
                                    <div className="w-px h-10 bg-slate-100"></div>
                                    <button onClick={() => handleToggleActive(service)} className={`transition-all ${service.is_active ? 'text-primary' : 'text-slate-300'}`}>
                                        {service.is_active ? <ToggleRight size={40} /> : <ToggleLeft size={40} />}
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>


            {
                showAddModal && (
                    <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                        <div className="bg-white w-full max-w-lg rounded-3xl p-8 shadow-2xl">
                            <h3 className="text-2xl font-black text-slate-800 mb-6">Add New Service</h3>
                            <p className="text-slate-500 text-sm font-medium mb-6">Create a new service instantly. It will be added to your active list.</p>
                            <form onSubmit={handleCreateService} className="space-y-4">
                                <div>
                                    <label className="text-xs font-bold text-slate-400 uppercase">Service Name</label>
                                    <input
                                        value={addForm.item_name}
                                        onChange={e => setAddForm({ ...addForm, item_name: e.target.value })}
                                        className="w-full p-3 bg-slate-50 rounded-xl border-none outline-none font-bold"
                                        required
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-xs font-bold text-slate-400 uppercase">Type</label>
                                        <div className="w-full p-3 bg-slate-50 rounded-xl font-bold text-sm text-slate-800">
                                            Treatment
                                        </div>
                                        <p className="text-[10px] text-slate-400 mt-1 font-medium">
                                            💊 Medicines are managed in <a href="/inventory" className="text-primary underline font-bold">Pharmacy Inventory</a>
                                        </p>
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-slate-400 uppercase">Fee (₹)</label>
                                        <input
                                            type="number"
                                            value={addForm.default_fee}
                                            onChange={e => setAddForm({ ...addForm, default_fee: e.target.value })}
                                            className="w-full p-3 bg-slate-50 rounded-xl border-none outline-none font-bold"
                                            required
                                        />
                                    </div>
                                </div>
                                <div className="flex justify-end gap-3 mt-6">
                                    <button type="button" onClick={() => setShowAddModal(false)} className="px-6 py-3 font-bold text-slate-500 hover:bg-slate-50 rounded-xl">Cancel</button>
                                    <button type="submit" className="px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-dark">Create Service</button>
                                </div>
                            </form>
                        </div>
                    </div>
                )
            }
        </div >
    );
}
