import { useState, useEffect } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import { Sliders, Microscope, Plus, Check, X, Edit, Trash2, ToggleRight, ToggleLeft, ShieldAlert, Globe, Palette, Clock, CheckCircle, Search, Edit2, Save } from 'lucide-react';
import AdminPageHeader from '../../components/layout/AdminPageHeader';

// ─── Main Component ───────────────────────────────────────────────────────────
export default function SystemSettings() {
    const [specialties, setSpecialties] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [submitting, setSubmitting] = useState(false);

    const [dualApproval, setDualApproval] = useState(true);

    const [showResetModal, setShowResetModal] = useState(false);
    const [resetConfirmText, setResetConfirmText] = useState('');
    const [resetting, setResetting] = useState(false);
    const [seedDemo, setSeedDemo] = useState(false);

    const FEATURES = [
        { key: 'patient_registry', label: 'Patient Registry' },
        { key: 'clinical_services', label: 'Clinical Services' },
        { key: 'pharmacy', label: 'Pharmacy/Inventory' },
        { key: 'billing', label: 'Billing & Invoicing' },
        { key: 'expenses', label: 'Expense Tracking' },
        { key: 'appointments', label: 'Appointment Scheduling' },
        { key: 'growth_insights', label: 'Growth Insights' }
    ];

    const CAPABILITIES = [
        { key: 'supports_teeth_chart', label: 'Teeth Chart (Dental)' },
        { key: 'supports_procedures', label: 'Procedure Support' },
        { key: 'supports_medicines', label: 'Medicine Prescribing' }
    ];

    const [formData, setFormData] = useState({
        name: '',
        is_active: true,
        features: {},
        capabilities: {}
    });

    useEffect(() => {
        fetchSpecialties();
        fetchSystemSettings();
    }, []);

    const fetchSystemSettings = async () => {
        try {
            const res = await api.get('/admin/system-settings/dual_admin_approval_enabled');
            const val = res.data?.dual_admin_approval_enabled;
            setDualApproval(val === '1' || val === true || val === 'true');
        } catch {
            console.error('Failed to load system settings');
        }
    };

    const handleToggleDualApproval = async (newVal) => {
        setDualApproval(newVal);
        try {
            await api.post('/admin/system-settings', {
                key: 'dual_admin_approval_enabled',
                value: newVal ? '1' : '0'
            });
            toast.success(newVal ? 'Dual approval strictly enforced' : 'Self-approval is now allowed');
        } catch {
            toast.error('Failed to update governance policy');
            setDualApproval(!newVal);
        }
    };

    const fetchSpecialties = async () => {
        try {
            const res = await api.get('/admin/specialties');
            setSpecialties(res.data);
        } catch {
            toast.error('Failed to load specialties');
        } finally {
            setLoading(false);
        }
    };

    // ── Active Specialty Actions ───────────────────────────────────────────────

    const handleEdit = (spec) => {
        const featuresState = {};
        FEATURES.forEach(f => featuresState[f.key] = false);
        if (spec.modules && Array.isArray(spec.modules)) {
            spec.modules.forEach(m => {
                if (m.pivot && m.pivot.enabled) {
                    featuresState[m.key] = true;
                }
            });
        }
        setFormData({
            name: spec.name,
            is_active: spec.is_active,
            features: featuresState,
            capabilities: spec.capabilities || {}
        });
        setEditingId(spec.id);
        setShowModal(true);
    };

    const handleDelete = async (spec) => {
        // Route through DeleteManagerModal — archive (soft-delete) path
        // For archive, we still use the simple confirm but surface the dependency modal
        if (!window.confirm(`Archive "${spec.name}"? Doctors using this specialty cannot log in until it is restored.`)) return;
        try {
            await api.delete(`/admin/delete/specialty/${spec.id}/archive`);
            toast.success('Specialty archived');
            setSpecialties(prev => prev.filter(s => s.id !== spec.id));
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to archive specialty');
        }
    };

    const handleCreate = () => {
        setFormData({
            name: '',
            is_active: true,
            features: FEATURES.reduce((acc, f) => ({ ...acc, [f.key]: true }), {}),
            capabilities: CAPABILITIES.reduce((acc, c) => ({ ...acc, [c.key]: true }), {})
        });
        setEditingId(null);
        setShowModal(true);
    };

    const handleToggleFeature = (key) => {
        setFormData(prev => ({
            ...prev,
            features: { ...prev.features, [key]: !prev.features[key] }
        }));
    };

    const handleToggleCapability = (key) => {
        setFormData(prev => ({
            ...prev,
            capabilities: { ...prev.capabilities, [key]: !prev.capabilities[key] }
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            const modulesPayload = FEATURES.map(f => ({
                key: f.key,
                enabled: formData.features[f.key] || false
            }));
            const payload = {
                name: formData.name,
                capabilities: formData.capabilities,
                is_active: formData.is_active,
                modules: modulesPayload
            };
            if (editingId) {
                await api.patch(`/admin/specialties/${editingId}`, payload);
                toast.success('Specialty updated');
            } else {
                await api.post('/admin/specialties', payload);
                toast.success('Specialty created');
            }
            setShowModal(false);
            fetchSpecialties();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to save specialty');
        } finally {
            setSubmitting(false);
        }
    };

    const handleSystemReset = async () => {
        if (resetConfirmText !== 'RESET') return;
        setResetting(true);
        try {
            await api.post('/admin/system-reset', { confirm: 'RESET', seed_demo: seedDemo });
            toast.success('System reset complete.');
            setShowResetModal(false);
            setResetConfirmText('');
            // Reload admin dashboard automatically
            window.location.href = '/admin';
        } catch (err) {
            toast.error(err.response?.data?.message || 'System reset failed');
        } finally {
            setResetting(false);
        }
    };

    // ── Render ─────────────────────────────────────────────────────────────────
    return (
        <div className="max-w-7xl mx-auto px-6 py-6 transition-all duration-300">
            <AdminPageHeader
                title="System Configuration"
                description="Manage platform-wide defaults, specialties and branding guidelines."
            />

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {/* Governance Policy */}
                <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm col-span-2">
                    <div className="flex justify-between items-center mb-6">
                        <div className="flex items-center gap-3">
                            <div className="p-3 bg-red-50 text-red-600 rounded-2xl">
                                <ShieldAlert size={24} />
                            </div>
                            <div>
                                <h2 className="text-xl font-black text-slate-800">Governance Policy</h2>
                                <p className="text-sm text-slate-400 font-bold">System-wide deletion governance and access rules</p>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-between items-center p-6 border border-slate-100 rounded-2xl bg-slate-50/50">
                        <div>
                            <h3 className="text-lg font-black text-slate-800">Require Dual Admin Approval</h3>
                            <p className="text-sm font-bold text-slate-500 mt-1">If enabled, the requester cannot approve their own deletion request.</p>
                        </div>
                        <button
                            onClick={() => handleToggleDualApproval(!dualApproval)}
                            className={`p-2 rounded-xl transition-all ${dualApproval ? 'text-emerald-500 bg-emerald-50 hover:bg-emerald-100' : 'text-slate-400 bg-slate-100 hover:bg-slate-200'}`}
                        >
                            {dualApproval ? <ToggleRight size={36} /> : <ToggleLeft size={36} />}
                        </button>
                    </div>
                </div>

                {/* Specialty Registry */}
                <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm col-span-2">
                    <div className="flex justify-between items-center mb-6">
                        <div className="flex items-center gap-3">
                            <div className="p-3 bg-blue-50 text-blue-600 rounded-2xl">
                                <Microscope size={24} />
                            </div>
                            <div>
                                <h2 className="text-xl font-black text-slate-800">Specialty Registry</h2>
                                <p className="text-sm text-slate-400 font-bold">Define clinical modules per specialty</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <button onClick={handleCreate} className="px-4 py-2 bg-slate-900 text-white rounded-xl font-bold flex items-center gap-2 hover:bg-slate-800">
                                <Plus size={16} /> Add Specialty
                            </button>
                        </div>
                    </div>

                    {/* Active Specialties */}
                    {loading ? (
                        <div className="animate-pulse text-center py-8 text-slate-400">Loading Configuration...</div>
                    ) : (
                        <div className="space-y-4">
                            {specialties.map(spec => (
                                <div key={spec.id} className="p-6 rounded-[2rem] border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all flex justify-between items-center group">
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <h3 className="text-lg font-black text-slate-800">{spec.name}</h3>
                                            {!spec.is_active && <span className="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold uppercase">Inactive</span>}
                                        </div>
                                        <p className="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wider">
                                            {spec.enabled_modules_count ?? 0} Modules Enabled • {spec.enabled_capabilities_count ?? 0} Tools Active
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <button onClick={() => handleEdit(spec)} className="p-3 bg-white border border-slate-200 text-slate-400 hover:text-primary hover:border-primary rounded-xl transition-all">
                                            <Edit size={18} />
                                        </button>
                                        <button onClick={() => handleDelete(spec)} className="p-3 bg-white border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-400 rounded-xl transition-all" title="Archive specialty">
                                            <Trash2 size={18} />
                                        </button>
                                    </div>
                                </div>
                            ))}
                            {specialties.length === 0 && (
                                <div className="text-center py-12 text-slate-400 font-bold">No active specialties found.</div>
                            )}
                        </div>
                    )}
                </div>

                {/* System Testing Area */}
                <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm col-span-2">
                    <div className="flex justify-between items-center mb-6">
                        <div className="flex items-center gap-3">
                            <div className="p-3 bg-red-50 text-red-600 rounded-2xl">
                                <Trash2 size={24} />
                            </div>
                            <div>
                                <h2 className="text-xl font-black text-slate-800">Testing Environment</h2>
                                <p className="text-sm text-slate-400 font-bold">Clear operational data while preserving core configuration</p>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center justify-between p-6 border border-red-100 rounded-2xl bg-red-50/50">
                        <div>
                            <h3 className="text-lg font-black text-slate-800">Clear Operational Data</h3>
                            <p className="text-sm font-bold text-slate-500 mt-1">This will delete all test data like patients, invoices, etc.</p>
                        </div>
                        <button
                            onClick={() => setShowResetModal(true)}
                            className="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl transition-all"
                        >
                            Factory Reset System
                        </button>
                    </div>
                </div>

            </div>

            {/* Create / Edit Modal */}
            {showModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-2xl rounded-[3rem] p-10 shadow-2xl overflow-y-auto max-h-[90vh]">
                        <h2 className="text-3xl font-black text-slate-800 mb-8">{editingId ? 'Edit Configuration' : 'New Specialty'}</h2>

                        <form onSubmit={handleSubmit} className="space-y-8">
                            <div>
                                <label className="block text-xs font-black text-slate-400 uppercase mb-2">Specialty Name</label>
                                <input
                                    type="text" required value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold text-lg"
                                    placeholder="e.g. Dermatology"
                                />
                            </div>

                            <div>
                                <h3 className="text-sm font-black text-slate-800 mb-4 flex items-center gap-2">
                                    <Sliders size={18} /> Module Visibility
                                </h3>
                                <div className="grid grid-cols-2 gap-3">
                                    {FEATURES.map(feat => (
                                        <button
                                            key={feat.key} type="button"
                                            onClick={() => handleToggleFeature(feat.key)}
                                            className={`p-4 rounded-2xl border text-left transition-all flex items-center justify-between ${formData.features[feat.key] ? 'bg-primary/5 border-primary/20' : 'bg-slate-50 border-slate-100 opacity-60'}`}
                                        >
                                            <span className={`text-sm font-bold ${formData.features[feat.key] ? 'text-primary' : 'text-slate-500'}`}>{feat.label}</span>
                                            {formData.features[feat.key] ? <ToggleRight className="text-primary" /> : <ToggleLeft className="text-slate-300" />}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <h3 className="text-sm font-black text-slate-800 mb-4 flex items-center gap-2">
                                    <Microscope size={18} /> Clinical Capabilities
                                </h3>
                                <div className="grid grid-cols-2 gap-3">
                                    {CAPABILITIES.map(cap => (
                                        <button
                                            key={cap.key} type="button"
                                            onClick={() => handleToggleCapability(cap.key)}
                                            className={`p-4 rounded-2xl border text-left transition-all flex items-center justify-between ${formData.capabilities[cap.key] ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-100 opacity-60'}`}
                                        >
                                            <span className={`text-sm font-bold ${formData.capabilities[cap.key] ? 'text-emerald-700' : 'text-slate-500'}`}>{cap.label}</span>
                                            {formData.capabilities[cap.key] ? <Check size={18} className="text-emerald-600" /> : <X size={18} className="text-slate-300" />}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <div className="flex justify-end gap-4 pt-6">
                                <button type="button" onClick={() => setShowModal(false)} className="px-8 py-4 font-black text-slate-400 hover:bg-slate-100 rounded-2xl">Dismiss</button>
                                <button
                                    type="submit" disabled={submitting}
                                    className="px-10 py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark shadow-2xl shadow-primary/30 transition-all disabled:opacity-50"
                                >
                                    {submitting ? 'Saving...' : 'Save Configuration'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* System Reset Modal */}
            {showResetModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-lg rounded-[3rem] p-10 shadow-2xl">
                        <div className="mb-6">
                            <h2 className="text-3xl font-black text-red-600 mb-2">Warning</h2>
                            <p className="font-bold text-slate-600">
                                ⚠️ This will permanently delete ALL operational data including:
                            </p>
                            <ul className="list-disc ml-6 mt-2 mb-4 text-slate-500 font-medium">
                                <li>Patients</li>
                                <li>Treatments</li>
                                <li>Invoices</li>
                                <li>Inventory</li>
                                <li>Ledger</li>
                                <li>Catalog entries</li>
                            </ul>
                            <p className="font-bold text-slate-600">
                                Doctors and Specialties will remain.
                            </p>
                        </div>

                        <div className="mb-6 flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <input
                                type="checkbox"
                                id="seedDemoCheck"
                                checked={seedDemo}
                                onChange={(e) => setSeedDemo(e.target.checked)}
                                className="w-5 h-5 accent-primary rounded cursor-pointer"
                            />
                            <label htmlFor="seedDemoCheck" className="text-sm font-bold text-slate-700 cursor-pointer">
                                Seed Demo Data (10 patients, 5 medicines, 20 treatments, 10 invoices, inventory stock)
                            </label>
                        </div>

                        <div className="mb-6">
                            <label className="block text-sm font-bold text-slate-500 mb-2">Type RESET to confirm:</label>
                            <input
                                type="text"
                                value={resetConfirmText}
                                onChange={(e) => setResetConfirmText(e.target.value)}
                                className="w-full px-5 py-3 rounded-xl border border-slate-200 outline-none focus:ring-4 focus:ring-red-500/10 focus:border-red-500 font-bold"
                            />
                        </div>

                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => { setShowResetModal(false); setResetConfirmText(''); }}
                                className="px-6 py-3 font-bold text-slate-500 hover:bg-slate-100 rounded-xl"
                            >
                                Cancel
                            </button>
                            <button
                                disabled={resetConfirmText !== 'RESET' || resetting}
                                onClick={handleSystemReset}
                                className="px-6 py-3 font-bold bg-red-600 text-white hover:bg-red-700 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {resetting ? 'Resetting...' : 'Confirm Reset'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
