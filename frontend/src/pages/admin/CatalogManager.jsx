import React, { useState, useEffect, useCallback } from 'react';
import AdminCatalogTableLayout from '../../components/admin/Catalog/AdminCatalogTableLayout';
import ConflictPreviewModal from '../../components/admin/Catalog/ConflictPreviewModal';
import axios from '../../api/axios';
import { toast } from 'react-hot-toast';
import AdminPageHeader from '../../components/layout/AdminPageHeader';

export default function CatalogManager() {
    const [activeTab, setActiveTab] = useState('local');
    const [localServices, setLocalServices] = useState([]);
    const [globalServices, setGlobalServices] = useState([]);
    const [pendingApprovals, setPendingApprovals] = useState([]);
    const [conflictData, setConflictData] = useState(null);
    const [currentActionItem, setCurrentActionItem] = useState(null);
    const [approvalMode, setApprovalMode] = useState(false);
    const [loading, setLoading] = useState(false);
    const [specialties, setSpecialties] = useState([]);
    const [selectedSpecialty, setSelectedSpecialty] = useState('');

    useEffect(() => {
        const loadSpecialties = async () => {
            try {
                const res = await axios.get('/admin/specialties');
                setSpecialties(res.data);
                if (res.data.length > 0) {
                    setSelectedSpecialty(res.data[0].id);
                }
            } catch {
                toast.error("Failed to load specialties");
            }
        };
        loadSpecialties();
    }, []);

    const fetchData = useCallback(async () => {
        setLoading(true);
        try {
            if (activeTab === 'local') {
                setGlobalServices([]);
                setPendingApprovals([]);
                const res = await axios.get('/admin/hybrid-suggestions/services');
                setLocalServices(res.data);
            } else if (activeTab === 'global') {
                setLocalServices([]);
                setPendingApprovals([]);
                if (!selectedSpecialty) return;
                const res = await axios.get(`/admin/specialties/${selectedSpecialty}/catalog`);
                // Merge active and archived for compatibility with existing UI
                const active = Array.isArray(res.data?.active) ? res.data.active : [];
                const archived = Array.isArray(res.data?.archived) ? res.data.archived.map(s => ({ ...s, is_archived: true })) : [];
                setGlobalServices([...active, ...archived]);
            } else if (activeTab === 'pending') {
                setLocalServices([]);
                setGlobalServices([]);
                const res = await axios.get('/admin/hybrid-promotions/pending');
                setPendingApprovals(res.data.filter(i => i.entity_type === 'clinical'));
            }
        } catch {
            toast.error("Failed to load data");
        } finally {
            setLoading(false);
        }
    }, [activeTab]);

    useEffect(() => {
        const loadApprovalMode = async () => {
            try {
                const res = await axios.get('/admin/system-settings/promotion_requires_approval');
                setApprovalMode(res.data?.value === 'true' || res.data?.value === true);
            } catch {
                // silent fail
            }
        };

        loadApprovalMode();
    }, []);

    useEffect(() => {
        const initData = async () => {
            if (activeTab === 'global' && !selectedSpecialty) return;
            await fetchData();
        };
        initData();
    }, [activeTab, fetchData, selectedSpecialty]);

    const handlePromote = async (item, force = false) => {
        if (approvalMode && !force && conflictData?.status === 'conflict_exact') {
            toast.error("Exact matches cannot be force promoted.");
            return;
        }

        try {
            const res = await axios.post(`/admin/hybrid-promotions/service/${item.id}`, force ? { force_promote: true } : {});
            if (res.status === 202) {
                toast.success("Promotion requested successfully.");
            } else {
                toast.success("Promoted successfully!");
            }
            setConflictData(null);
            fetchData();
        } catch (error) {
            if (error?.response?.status === 409) {
                setCurrentActionItem(item);
                setConflictData(error.response.data);
            } else {
                toast.error(error?.response?.data?.error || "Failed to promote");
            }
        }
    };

    const handleApprove = async (id) => {
        try {
            await axios.post(`/admin/hybrid-promotions/approve/${id}`);
            toast.success("Promotion approved successfully");
            fetchData();
        } catch {
            toast.error("Failed to approve. Drift detected?");
        }
    };

    const handleReject = async (id) => {
        try {
            await axios.post(`/admin/hybrid-promotions/reject/${id}`);
            toast.success("Promotion rejected");
            fetchData();
        } catch {
            toast.error("Failed to reject");
        }
    };

    return (
        <div className="max-w-7xl mx-auto px-6 py-6 transition-all duration-300">
            <AdminPageHeader
                title="Clinical Catalog"
                description="Manage local services and global catalog configurations."
            />

            <div className="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

                <div className="mb-6">
                    {approvalMode ? (
                        <div className="bg-indigo-50 text-indigo-800 p-4 rounded-xl border border-indigo-100 flex items-center gap-3">
                            <span className="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold">✓</span>
                            <div>
                                <h3 className="font-semibold text-sm">Dual Admin Approval Enabled</h3>
                                <p className="text-xs text-indigo-600 opacity-80 mt-0.5">Promotions match exact conflicts require secondary approval.</p>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-emerald-50 text-emerald-800 p-4 rounded-xl border border-emerald-100 flex items-center gap-3">
                            <span className="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 font-bold">⚡</span>
                            <div>
                                <h3 className="font-semibold text-sm">Direct Promotion Mode</h3>
                                <p className="text-xs text-emerald-600 opacity-80 mt-0.5">Promotions will immediately commit to the global master ledger.</p>
                            </div>
                        </div>
                    )}
                </div>

                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-200 pb-4">
                    <div className="flex gap-2 overflow-x-auto">
                        <button disabled={loading} onClick={() => setActiveTab('local')} className={`whitespace-nowrap px-4 py-2 rounded-lg font-medium transition-colors ${activeTab === 'local' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>Local Suggestions</button>
                        <button disabled={loading} onClick={() => setActiveTab('global')} className={`whitespace-nowrap px-4 py-2 rounded-lg font-medium transition-colors ${activeTab === 'global' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>Global Catalog</button>
                        {approvalMode && (
                            <button disabled={loading} onClick={() => setActiveTab('pending')} className={`whitespace-nowrap px-4 py-2 rounded-lg font-medium transition-colors ${activeTab === 'pending' ? 'bg-slate-800 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>Pending Approvals</button>
                        )}
                    </div>

                    {specialties.length > 0 && (
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-bold text-slate-400 uppercase tracking-widest">Specialty:</span>
                            <select
                                value={selectedSpecialty}
                                onChange={(e) => setSelectedSpecialty(e.target.value)}
                                className="bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2"
                            >
                                {specialties.map(s => (
                                    <option key={s.id} value={s.id}>{s.name}</option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                {loading ? (
                    <div className="py-12 flex justify-center">
                        <div className="w-8 h-8 border-4 border-slate-300 border-t-slate-800 rounded-full animate-spin"></div>
                    </div>
                ) : (
                    <AdminCatalogTableLayout
                        tableContent={
                            <div className="overflow-x-auto">
                                <table className="w-full text-left whitespace-nowrap">
                                    <thead className="bg-slate-50 text-xs uppercase text-slate-500 border-y border-slate-200">
                                        <tr>
                                            {activeTab === 'pending' ? (
                                                <>
                                                    <th className="px-4 py-4 font-semibold">Req ID</th>
                                                    <th className="px-4 py-4 font-semibold">Snapshot Item</th>
                                                    <th className="px-4 py-4 font-semibold">Conflict</th>
                                                    <th className="px-4 py-4 font-semibold">Requested At</th>
                                                    <th className="px-4 py-4 font-semibold text-right">Actions</th>
                                                </>
                                            ) : activeTab === 'local' ? (
                                                <>
                                                    <th className="px-4 py-4 font-semibold">ID</th>
                                                    <th className="px-4 py-4 font-semibold">Service Name</th>
                                                    <th className="px-4 py-4 font-semibold">Type</th>
                                                    <th className="px-4 py-4 font-semibold text-right">Fee</th>
                                                    <th className="px-4 py-4 font-semibold">Doctor</th>
                                                    <th className="px-4 py-4 font-semibold text-right">Actions</th>
                                                </>
                                            ) : (
                                                <>
                                                    <th className="px-4 py-4 font-semibold">Master ID</th>
                                                    <th className="px-4 py-4 font-semibold">Service Name</th>
                                                    <th className="px-4 py-4 font-semibold">Type</th>
                                                    <th className="px-4 py-4 font-semibold text-right">Default Fee</th>
                                                    <th className="px-4 py-4 font-semibold text-center">Status</th>
                                                </>
                                            )}
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 text-sm">
                                        {activeTab === 'pending' && (pendingApprovals.length === 0 ? (
                                            <tr><td colSpan="5" className="px-4 py-8 text-center text-slate-500 italic">No pending approvals found.</td></tr>
                                        ) : pendingApprovals.map(p => {
                                            const snap = JSON.parse(p.snapshot_json || '{}');
                                            return (
                                                <tr key={p.id} className="hover:bg-slate-50/50 transition-colors">
                                                    <td className="px-4 py-4 text-slate-600">#{p.id}</td>
                                                    <td className="px-4 py-4 font-medium text-slate-800">{snap.item_name || 'Unknown'}</td>
                                                    <td className="px-4 py-4">
                                                        <span className={`px-2 py-1 text-[11px] uppercase tracking-wider font-bold rounded-lg ${p.conflict_status === 'none' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700'}`}>
                                                            {p.conflict_status?.replace('conflict_', '') || 'NONE'}
                                                        </span>
                                                    </td>
                                                    <td className="px-4 py-4 text-slate-500">{new Date(p.created_at).toLocaleDateString()}</td>
                                                    <td className="px-4 py-4 flex items-center justify-end gap-2">
                                                        <button onClick={() => handleApprove(p.id)} className="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Approve</button>
                                                        <button onClick={() => handleReject(p.id)} className="bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">Reject</button>
                                                    </td>
                                                </tr>
                                            );
                                        }))}

                                        {activeTab === 'local' && (localServices.length === 0 ? (
                                            <tr><td colSpan="6" className="px-4 py-8 text-center text-slate-500 italic">No local suggestions available.</td></tr>
                                        ) : localServices.map(s => (
                                            <tr key={s.id} className="hover:bg-slate-50/50 transition-colors">
                                                <td className="px-4 py-4 text-slate-500">#{s.id}</td>
                                                <td className="px-4 py-4 font-medium text-slate-800">{s.item_name}</td>
                                                <td className="px-4 py-4 text-slate-600">{s.type}</td>
                                                <td className="px-4 py-4 text-slate-800 font-medium text-right">${s.default_fee}</td>
                                                <td className="px-4 py-4 text-slate-500 text-xs">{s.doctor_name || 'N/A'}</td>
                                                <td className="px-4 py-4 text-right">
                                                    <button onClick={() => handlePromote(s)} className="text-xs bg-slate-900 text-white hover:bg-slate-800 font-semibold px-3 py-1.5 rounded-lg transition-colors">Promote To Master</button>
                                                </td>
                                            </tr>
                                        )))}

                                        {activeTab === 'global' && (globalServices.length === 0 ? (
                                            <tr><td colSpan="5" className="px-4 py-8 text-center text-slate-500 italic">Global catalog is empty for this specialty.</td></tr>
                                        ) : globalServices.map(s => (
                                            <tr key={s.react_key} className="hover:bg-slate-50/50 transition-colors">
                                                <td className="px-4 py-4 text-slate-500">#{s.id}</td>
                                                <td className="px-4 py-4 font-medium text-slate-800">{s.name || s.item_name}</td>
                                                <td className="px-4 py-4 text-slate-600">{s.type || s.category}</td>
                                                <td className="px-4 py-4 text-slate-800 font-medium text-right">${s.default_fee || s.default_selling_price || '0.00'}</td>
                                                <td className="px-4 py-4 text-center">
                                                    <span className={`px-2 py-1 text-[11px] uppercase tracking-wider font-bold rounded-lg ${s.is_active || s.is_active === undefined ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'}`}>
                                                        {s.is_active || s.is_active === undefined ? 'ACTIVE' : 'ARCHIVED'}
                                                    </span>
                                                </td>
                                            </tr>
                                        )))}
                                    </tbody>
                                </table>
                            </div>
                        }
                    />
                )}
            </div>

            {conflictData && (
                <ConflictPreviewModal
                    conflictData={conflictData}
                    onCancel={() => setConflictData(null)}
                    onForce={() => handlePromote(currentActionItem, true)}
                />
            )}
        </div>
    );
}
