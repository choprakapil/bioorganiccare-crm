import { useState, useEffect } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import {
    ShieldCheck, Zap, Edit3, Check, X, Info, Layers,
    CheckCircle, XCircle, AlertTriangle, Plus, Save, Trash2
} from 'lucide-react';
import AdminPageHeader from '../../components/layout/AdminPageHeader';

export default function PlanManagement() {
    const [specialties, setSpecialties] = useState([]);
    const [selectedSpecialtyId, setSelectedSpecialtyId] = useState(null);
    const [plans, setPlans] = useState([]);
    const [loading, setLoading] = useState(false);
    const [editingPlan, setEditingPlan] = useState(null);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    const [formData, setFormData] = useState({
        name: '',
        price: '',
        max_patients: 100,
        max_appointments_monthly: 300,
        max_staff: 5,
        tier: 'basic',
        is_active: true,
        modules: {}
    });

    const TIERS = ['basic', 'starter', 'growth', 'pro', 'enterprise'];

    // ─── Data Loading ──────────────────────────────────────────────────────────

    useEffect(() => {
        fetchSpecialties();
    }, []);

    useEffect(() => {
        if (selectedSpecialtyId) {
            fetchPlans(selectedSpecialtyId);
        }
    }, [selectedSpecialtyId]);

    const fetchSpecialties = async () => {
        try {
            const res = await api.get('/admin/specialties');
            if (res.data.length > 0) {
                setSpecialties(res.data);
                if (!selectedSpecialtyId) {
                    setSelectedSpecialtyId(res.data[0].id);
                }
            }
        } catch (err) {
            toast.error('Failed to load specialties');
        }
    };

    const fetchPlans = async (specId) => {
        setLoading(true);
        try {
            const res = await api.get(`/admin/plans?specialty_id=${specId}`);
            setPlans(res.data || []);
        } catch (err) {
            toast.error('Failed to load plan policies');
        } finally {
            setLoading(false);
        }
    };

    // ─── Computed Helpers ──────────────────────────────────────────────────────

    const currentSpec = specialties.find(s => s.id === selectedSpecialtyId);
    const availableModules = currentSpec?.modules?.filter(m => m.pivot?.enabled) || [];
    const usedTiers = plans.map(p => p.tier);
    const allTiersUsed = TIERS.every(t => usedTiers.includes(t));

    // ─── Actions ───────────────────────────────────────────────────────────────

    const resetForm = () => {
        const firstAvailable = TIERS.find(t => !usedTiers.includes(t)) || 'basic';

        setFormData({
            name: '',
            price: '',
            max_patients: 100,
            max_appointments_monthly: 300,
            max_staff: 5,
            tier: firstAvailable,
            is_active: true,
            modules: {}
        });
        setEditingPlan(null);
    };

    const handleOpenCreate = () => {
        if (allTiersUsed) {
            toast.error('All tiers already configured for this specialty');
            return;
        }
        resetForm();
        setShowCreateModal(true);
    };

    const handleEdit = (plan) => {
        setEditingPlan(plan);
        const moduleMap = {};

        availableModules.forEach(m => {
            const planMod = plan.modules?.find(pm => pm.id === m.id);
            // Auto-sync logic: if plan doesn't have it, default to true (active) based on specialty availability
            moduleMap[m.id] = planMod ? (planMod.pivot.enabled === 1 || planMod.pivot.enabled === true) : true;
        });

        setFormData({
            name: plan.name,
            price: plan.price,
            max_patients: plan.max_patients || 0,
            max_appointments_monthly: plan.max_appointments_monthly || 0,
            max_staff: plan.max_staff || 0,
            tier: plan.tier || 'basic',
            is_active: plan.is_active === 1 || plan.is_active === true,
            modules: moduleMap
        });
    };

    const handleCreate = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            const payload = {
                ...formData,
                specialty_id: selectedSpecialtyId
                // Backend store() handles the module provisioning automatically now for zero-trust
            };

            await api.post('/admin/plans', payload);
            toast.success('Subscription plan created and provisioned');
            setShowCreateModal(false);
            fetchPlans(selectedSpecialtyId);
        } catch (err) {
            const msg = err.response?.data?.errors?.tier?.[0] || err.response?.data?.message || 'Failed to create plan';
            toast.error(msg);
        } finally {
            setSubmitting(false);
        }
    };

    const handleUpdate = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            const modulesPayload = Object.entries(formData.modules).map(([id, enabled]) => ({
                id: parseInt(id),
                enabled: enabled
            }));

            const payload = {
                ...formData,
                modules: modulesPayload
            };

            await api.patch(`/admin/plans/${editingPlan.id}`, payload);
            toast.success('Plan policies updated');
            setEditingPlan(null);
            fetchPlans(selectedSpecialtyId);
        } catch (err) {
            const msg = err.response?.data?.errors?.tier?.[0] || err.response?.data?.message || 'Failed to update plan';
            toast.error(msg);
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (planId) => {
        if (!confirm("Are you sure? This will remove the subscription policy entirely. This cannot be undone.")) return;

        try {
            await api.delete(`/admin/delete/plan/${planId}/archive`);
            toast.success('Plan deleted successfully');
            fetchPlans(selectedSpecialtyId);
        } catch (err) {
            const msg = err.response?.data?.message || 'Failed to delete plan';
            toast.error(msg);
        }
    };

    const toggleModule = (moduleId) => {
        setFormData(prev => ({
            ...prev,
            modules: {
                ...prev.modules,
                [moduleId]: !prev.modules[moduleId]
            }
        }));
    };

    const handleInputChange = (field, value) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));
    };

    // ─── Render Helpers ────────────────────────────────────────────────────────

    if (specialties.length === 0 && !loading) {
        return (
            <div className="p-20 text-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">
                <AlertTriangle size={48} className="mx-auto text-amber-500 mb-4" />
                <h2 className="text-2xl font-black text-slate-800">No Active Specialties</h2>
                <p className="text-slate-500 font-medium mt-2">Configure specialties first before managing plans.</p>
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto px-6 py-6 transition-all duration-300">
            <AdminPageHeader
                title="Plan Control"
                description="Configure SaaS tiers specific to each clinical specialty."
                actions={
                    <button
                        onClick={handleOpenCreate}
                        disabled={allTiersUsed}
                        className={`px-4 py-2 rounded-lg font-bold flex items-center gap-2 transition-all shadow-sm ${allTiersUsed ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700'}`}
                    >
                        {allTiersUsed ? 'All Tiers Fixed' : <><Plus size={20} /> Create Plan</>}
                    </button>
                }
            />

            {/* Specialty Tabs */}
            <div className="flex gap-3 overflow-x-auto pb-4 no-scrollbar">
                {specialties.map(spec => (
                    <button
                        key={spec.id}
                        onClick={() => setSelectedSpecialtyId(spec.id)}
                        className={`flex items-center gap-2 px-6 py-3 rounded-2xl font-bold whitespace-nowrap transition-all border ${selectedSpecialtyId === spec.id
                            ? 'bg-slate-900 text-white border-slate-900 shadow-lg shadow-slate-900/20'
                            : 'bg-white text-slate-500 hover:bg-slate-50 border-slate-200'
                            }`}
                    >
                        <Layers size={18} />
                        {spec.name}
                    </button>
                ))}
            </div>

            {loading ? (
                <div className="p-12 text-center text-slate-400 font-medium animate-pulse">Retrieving policy configuration...</div>
            ) : (
                <>
                    {plans.length === 0 ? (
                        <div className="p-16 text-center bg-slate-50 rounded-[3rem] border-2 border-dashed border-slate-200">
                            <Zap size={32} className="mx-auto text-slate-300 mb-4" />
                            <p className="text-slate-500 font-black text-lg text-uppercase">No plans defined for {currentSpec?.name}</p>
                            <button onClick={handleOpenCreate} className="mt-4 text-primary font-bold hover:underline">Click here to create the first one.</button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {plans.map(plan => (
                                <div key={plan.id} className={`bg-white p-8 rounded-[3rem] border transition-all hover:shadow-xl relative overflow-hidden flex flex-col ${!plan.is_active ? 'opacity-60 grayscale border-slate-100 bg-slate-50' : 'border-slate-100 shadow-sm'}`}>
                                    <div className="flex justify-between items-start mb-6">
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <h2 className="text-2xl font-black text-slate-800">{plan.name}</h2>
                                                {!plan.is_active && <span className="text-[10px] bg-slate-200 text-slate-600 px-2 py-1 rounded-full uppercase font-black">Inactive</span>}
                                            </div>
                                            <p className="text-slate-400 font-bold text-sm uppercase tracking-widest">{plan.tier} Tier • ₹{plan.price}/month</p>
                                        </div>
                                        <div className="flex gap-2">
                                            <button onClick={() => handleEdit(plan)} className="p-3 bg-slate-50 text-slate-400 hover:bg-primary hover:text-white rounded-2xl transition-all">
                                                <Edit3 size={20} />
                                            </button>
                                            <button onClick={() => handleDelete(plan.id)} className="p-3 bg-slate-50 text-slate-400 hover:bg-red-500 hover:text-white rounded-2xl transition-all">
                                                <Trash2 size={20} />
                                            </button>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-2 gap-4 mb-8">
                                        <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                            <p className="text-[10px] font-black text-slate-400 uppercase mb-1">Max Patients</p>
                                            <p className="text-xl font-black text-slate-800">{plan.max_patients === -1 ? 'Unlimited' : plan.max_patients}</p>
                                        </div>
                                        <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                            <p className="text-[10px] font-black text-slate-400 uppercase mb-1">Monthly Visits</p>
                                            <p className="text-xl font-black text-slate-800">{plan.max_appointments_monthly === -1 ? 'Unlimited' : plan.max_appointments_monthly}</p>
                                        </div>
                                        <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100 col-span-2">
                                            <p className="text-[10px] font-black text-slate-400 uppercase mb-1">Max Team Members</p>
                                            <p className="text-xl font-black text-slate-800">{plan.max_staff === -1 ? 'Unlimited' : plan.max_staff}</p>
                                        </div>
                                    </div>

                                    <div className="space-y-2 mt-auto">
                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Active Modules</p>
                                        {plan.modules?.filter(m => m.pivot.enabled).map(m => (
                                            <div key={m.id} className="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold capitalize bg-green-50 text-green-700">
                                                <CheckCircle size={12} /> {m.name}
                                            </div>
                                        ))}
                                        {plan.modules?.filter(m => !m.pivot.enabled).map(m => (
                                            <div key={m.id} className="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold capitalize bg-slate-50 text-slate-400 opacity-60">
                                                <XCircle size={12} /> {m.name}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </>
            )}

            {/* ── Create/Edit Modal ────────────────────────────────────────────── */}
            {(showCreateModal || editingPlan) && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-xl rounded-[3rem] p-10 shadow-2xl max-h-[90vh] overflow-y-auto">
                        <div className="flex justify-between items-start mb-6">
                            <div>
                                <h2 className="text-3xl font-black text-slate-800">
                                    {showCreateModal ? 'New Plan' : 'Update Policy'}
                                </h2>
                                <p className="text-slate-500 font-medium text-sm mt-1">
                                    For {currentSpec?.name} specialty
                                </p>
                            </div>
                            <button onClick={() => { setShowCreateModal(false); setEditingPlan(null); }} className="p-3 hover:bg-slate-100 rounded-full text-slate-400">
                                <X size={24} />
                            </button>
                        </div>

                        <form onSubmit={showCreateModal ? handleCreate : handleUpdate} className="space-y-6">
                            {showCreateModal && (
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Internal Plan Name</label>
                                    <input
                                        type="text" required
                                        placeholder="e.g. Standard, Premium Boost"
                                        value={formData.name}
                                        onChange={(e) => handleInputChange('name', e.target.value)}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    />
                                </div>
                            )}

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Price (₹/month)</label>
                                    <input
                                        type="number" required
                                        value={formData.price}
                                        onChange={(e) => handleInputChange('price', e.target.value)}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">SaaS Tier</label>
                                    <select
                                        value={formData.tier}
                                        onChange={(e) => handleInputChange('tier', e.target.value)}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    >
                                        {TIERS.map(t => (
                                            <option
                                                key={t}
                                                value={t}
                                                disabled={showCreateModal && usedTiers.includes(t)}
                                                className="capitalize"
                                            >
                                                {t} {showCreateModal && usedTiers.includes(t) ? '(Configured)' : ''}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Total Patients</label>
                                    <input
                                        type="number" required
                                        value={formData.max_patients}
                                        onChange={(e) => handleInputChange('max_patients', e.target.value)}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Staff Seats</label>
                                    <input
                                        type="number" required
                                        value={formData.max_staff}
                                        onChange={(e) => handleInputChange('max_staff', e.target.value)}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-black text-slate-400 uppercase mb-2">Monthly Appointment Limit</label>
                                <input
                                    type="number" required
                                    value={formData.max_appointments_monthly}
                                    onChange={(e) => handleInputChange('max_appointments_monthly', e.target.value)}
                                    className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                />
                                <p className="text-[10px] text-slate-400 mt-2 ml-1">Tip: Use <b>-1</b> for unlimited seats/patients.</p>
                            </div>

                            {!showCreateModal && (
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-4 tracking-widest">Global Module Access</label>
                                    <div className="grid grid-cols-1 gap-3">
                                        {availableModules.map(mod => (
                                            <button
                                                key={mod.id}
                                                type="button"
                                                onClick={() => toggleModule(mod.id)}
                                                className={`p-4 rounded-2xl border-2 transition-all flex items-center justify-between text-left 
                                                        ${formData.modules[mod.id] ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 bg-slate-50 text-slate-400'}`}
                                            >
                                                <span className="font-bold flex items-center gap-2 capitalize">
                                                    {mod.name}
                                                </span>
                                                {formData.modules[mod.id] ? <Check size={18} className="bg-primary text-white rounded-full p-0.5" /> : <div className="w-5 h-5 rounded-full border-2 border-slate-300" />}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}

                            <div className="flex justify-end gap-4 pt-6">
                                <button type="button" onClick={() => { setShowCreateModal(false); setEditingPlan(null); }} className="px-8 py-4 font-black text-slate-400 hover:bg-slate-100 rounded-2xl transition-all">Cancel</button>
                                <button type="submit" disabled={submitting} className="px-10 py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark shadow-2xl shadow-primary/30 flex items-center gap-2 disabled:opacity-50">
                                    {submitting ? 'Processing...' : <><Save size={18} /> {showCreateModal ? 'Create & Auto-Provision' : 'Apply Policies'}</>}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
