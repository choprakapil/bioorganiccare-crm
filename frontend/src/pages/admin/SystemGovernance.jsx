import { useState, useEffect } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import {
    ShieldAlert, Clock, CheckCircle, XCircle, Filter,
    ArchiveRestore, Trash2, Search, Activity, AlertTriangle,
    RefreshCw, Database, FileText, Package, CreditCard
} from 'lucide-react';
import DeletionRequestDetailsModal from '../../components/admin/DeletionRequestDetailsModal';
import { useAuth } from '../../context/AuthContext';
import AdminPageHeader from '../../components/layout/AdminPageHeader';

const STATUS_META = {
    pending: { label: 'Pending', bg: 'bg-amber-100', text: 'text-amber-700', icon: Clock },
    approved: { label: 'Approved', bg: 'bg-emerald-100', text: 'text-emerald-700', icon: CheckCircle },
    rejected: { label: 'Rejected', bg: 'bg-red-100', text: 'text-red-700', icon: XCircle },
    executed: { label: 'Executed', bg: 'bg-slate-100', text: 'text-slate-600', icon: CheckCircle },
};

export default function SystemGovernance() {
    const { user } = useAuth();
    const [activeTab, setActiveTab] = useState('health');

    // Health Dashboard State
    const [healthData, setHealthData] = useState(null);
    const [loadingHealth, setLoadingHealth] = useState(false);
    const [repairModal, setRepairModal] = useState({ open: false, type: '', label: '', count: 0 });
    const [repairConfirm, setRepairConfirm] = useState('');
    const [repairing, setRepairing] = useState(false);

    // Queue State
    const [requests, setRequests] = useState([]);
    const [loadingQueue, setLoadingQueue] = useState(false);
    const [statusFilter, setStatusFilter] = useState('');
    const [selectedRequest, setSelectedRequest] = useState(null);
    const [isRequestModalOpen, setIsRequestModalOpen] = useState(false);

    // Archived State
    const [archivedSpecialties, setArchivedSpecialties] = useState([]);
    const [archivedServices, setArchivedServices] = useState([]);
    const [archivedMedicines, setArchivedMedicines] = useState([]);
    const [loadingSpecialties, setLoadingSpecialties] = useState(false);
    const [loadingServices, setLoadingServices] = useState(false);
    const [loadingMedicines, setLoadingMedicines] = useState(false);

    // Search and Bulk Actions
    const [search, setSearch] = useState('');

    // Delete Modal (Archive/Force)
    const [deleteModal, setDeleteModal] = useState({ open: false, ids: [], names: [], endpoint: '', type: '' });
    const [cascadePreview, setCascadePreview] = useState(null);
    const [loadingPreview, setLoadingPreview] = useState(false);
    const [isCascadeModalOpen, setIsCascadeModalOpen] = useState(false);

    useEffect(() => {
        setSearch('');
    }, [activeTab]);

    useEffect(() => {
        if (activeTab === 'health') {
            fetchHealthData();
        } else if (activeTab === 'queue') {
            fetchRequests();
        } else if (activeTab === 'specialties') {
            fetchArchivedSpecialties();
        } else if (activeTab === 'services') {
            fetchArchivedServices();
        } else if (activeTab === 'medicines') {
            fetchArchivedMedicines();
        }
    }, [activeTab, statusFilter]);

    const fetchHealthData = async () => {
        setLoadingHealth(true);
        try {
            const res = await api.get('/admin/governance/health');
            setHealthData(res.data);
        } catch {
            toast.error('Failed to load system health report');
        } finally {
            setLoadingHealth(false);
        }
    };

    const fetchRequests = async () => {
        setLoadingQueue(true);
        try {
            const params = {};
            if (statusFilter) params.status = statusFilter;
            const res = await api.get('/admin/delete/requests', { params });
            setRequests(res.data.data || res.data);
        } catch {
            toast.error('Failed to load governance requests');
        } finally {
            setLoadingQueue(false);
        }
    };

    const fetchArchivedSpecialties = async () => {
        setLoadingSpecialties(true);
        try {
            const res = await api.get('/admin/specialties/archived');
            setArchivedSpecialties(res.data);
        } catch {
            toast.error('Failed to load archived specialties');
        } finally {
            setLoadingSpecialties(false);
        }
    };

    const fetchArchivedServices = async () => {
        setLoadingServices(true);
        try {
            const res = await api.get('/admin/services/archived');
            setArchivedServices(res.data);
        } catch {
            toast.error('Failed to load archived services');
        } finally {
            setLoadingServices(false);
        }
    };

    const fetchArchivedMedicines = async () => {
        setLoadingMedicines(true);
        try {
            const res = await api.get('/admin/medicines/archived');
            setArchivedMedicines(res.data);
        } catch {
            toast.error('Failed to load archived medicines');
        } finally {
            setLoadingMedicines(false);
        }
    };

    const handleForceDeleteClick = async (entity, id, name) => {
        if (entity === 'specialty') {
            setLoadingPreview(true);
            try {
                const res = await api.get(`/admin/delete/specialty/${id}/cascade-preview`);
                if (res.data.total_rows > 0) {
                    setCascadePreview(res.data);
                    setDeleteModal({ open: false, ids: [id], names: [name], endpoint: `/admin/delete/specialty/${id}/force-cascade`, type: 'specialty' });
                    setIsCascadeModalOpen(true);
                    return;
                }
            } catch (err) {
                console.error("Preview failed", err);
            } finally {
                setLoadingPreview(false);
            }
        }

        // Default to standard force delete modal if no preview or not specialty
        setDeleteModal({
            open: true,
            ids: [id],
            names: [name],
            endpoint: `/admin/delete/${entity}/${id}/force`,
            type: entity
        });
    };

    const handleCascadeDelete = async () => {
        try {
            setRepairing(true); // Reuse loading state
            await api.delete(deleteModal.endpoint);
            toast.success('Cascade erasure complete');
            setIsCascadeModalOpen(false);
            fetchArchivedSpecialties();
        } catch (err) {
            toast.error(err.response?.data?.error || 'Cascade operation failed');
        } finally {
            setRepairing(false);
            setDeleteModal({ open: false, ids: [], names: [], endpoint: '', type: '' });
        }
    };

    const handleRepair = async () => {
        if (repairConfirm !== 'REPAIR') {
            toast.error('Please type REPAIR to confirm');
            return;
        }

        setRepairing(true);
        try {
            let endpoint = '';
            switch (repairModal.type) {
                case 'duplicate_services': endpoint = '/admin/governance/repair/duplicate-services'; break;
                case 'duplicate_medicines': endpoint = '/admin/governance/repair/duplicate-medicines'; break;
                case 'inventory_batches': endpoint = '/admin/governance/repair/inventory-batches'; break;
                case 'orphan_treatments': endpoint = '/admin/governance/repair/orphan-treatments'; break;
                case 'negative_inventory': endpoint = '/admin/governance/repair/negative-inventory'; break;
                case 'medicine_drift': endpoint = '/admin/governance/repair/medicine-drift'; break;
                case 'floating_ledger_entries': endpoint = '/admin/governance/repair/floating-ledger'; break;
                default: break;
            }

            if (endpoint) {
                const res = await api.post(endpoint, { confirm: 'REPAIR' });
                toast.success(`Repair complete. ${res.data.affected} records affected.`);
                fetchHealthData();
            }
        } catch (err) {
            toast.error(err.response?.data?.message || 'Repair operation failed');
        } finally {
            setRepairing(false);
            setRepairModal({ open: false, type: '', label: '', count: 0 });
            setRepairConfirm('');
        }
    };

    const formatDate = (dateStr) => {
        if (!dateStr) return '—';
        return new Date(dateStr).toLocaleDateString();
    };

    const isSuperAdmin = user?.role === 'super_admin';

    const getHealthColor = (status) => {
        switch (status) {
            case 'healthy': return 'bg-emerald-500';
            case 'warning': return 'bg-amber-500';
            case 'critical': return 'bg-rose-500';
            default: return 'bg-slate-300';
        }
    };

    const getIssueLabel = (key) => {
        const labels = {
            duplicate_services: 'Duplicate Services',
            duplicate_medicines: 'Duplicate Medicines',
            orphan_treatments: 'Orphan Treatments',
            inventory_without_batches: 'Inventory Without Batches',
            ledger_mismatch: 'Ledger Mismatch',
            negative_inventory: 'Negative Inventory Stock',
            floating_ledger_entries: 'Floating Ledger Entries',
            invoice_medicine_drift: 'Invoice Medicine Drift'
        };
        return labels[key] || key;
    };

    const getIssueIcon = (key) => {
        switch (key) {
            case 'duplicate_services': return <Database size={20} />;
            case 'duplicate_medicines': return <Package size={20} />;
            case 'orphan_treatments': return <FileText size={20} />;
            case 'inventory_without_batches': return <Package size={20} />;
            case 'ledger_mismatch': return <CreditCard size={20} />;
            case 'negative_inventory': return <AlertTriangle size={20} />;
            case 'floating_ledger_entries': return <CreditCard size={20} />;
            case 'invoice_medicine_drift': return <Activity size={20} />;
            default: return <Activity size={20} />;
        }
    };

    return (
        <div className="max-w-7xl mx-auto px-6 py-6 space-y-6 transition-all duration-300">
            <AdminPageHeader
                title="System Governance"
                description="Centralized health monitoring, data integrity audits and erasures."
                actions={
                    activeTab === 'health' && (
                        <button
                            onClick={fetchHealthData}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg flex items-center gap-2 transition-all shadow-sm"
                        >
                            <RefreshCw size={18} className={loadingHealth ? 'animate-spin' : ''} />
                            Refresh Status
                        </button>
                    )
                }
            />

            <div className="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                {/* Tabs Layer */}
                <div className="flex flex-wrap border-b border-slate-200 mb-8 pb-4 gap-2">
                    <button
                        onClick={() => setActiveTab('health')}
                        className={`px-5 py-3 rounded-xl font-black transition-all duration-200 whitespace-nowrap flex items-center gap-2 ${activeTab === 'health' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'}`}
                    >
                        <Activity size={18} />
                        Health Dashboard
                    </button>
                    <button
                        onClick={() => setActiveTab('queue')}
                        className={`px-5 py-3 rounded-xl font-black transition-all duration-200 whitespace-nowrap flex items-center gap-2 ${activeTab === 'queue' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'}`}
                    >
                        <ShieldAlert size={18} />
                        Deletion Queue
                    </button>
                    <button
                        onClick={() => setActiveTab('specialties')}
                        className={`px-5 py-3 rounded-xl font-black transition-all duration-200 whitespace-nowrap flex items-center gap-2 ${activeTab === 'specialties' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'}`}
                    >
                        <ArchiveRestore size={18} />
                        Archived Specialties
                        <span className="bg-slate-900/10 text-slate-900 text-[10px] px-2 py-0.5 rounded-full ml-1">
                            {archivedSpecialties.length}
                        </span>
                    </button>
                    <button
                        onClick={() => setActiveTab('services')}
                        className={`px-5 py-3 rounded-xl font-black transition-all duration-200 whitespace-nowrap flex items-center gap-2 ${activeTab === 'services' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'}`}
                    >
                        <Database size={18} />
                        Archived Services
                        <span className="bg-slate-900/10 text-slate-900 text-[10px] px-2 py-0.5 rounded-full ml-1">
                            {archivedServices.length}
                        </span>
                    </button>
                    <button
                        onClick={() => setActiveTab('medicines')}
                        className={`px-5 py-3 rounded-xl font-black transition-all duration-200 whitespace-nowrap flex items-center gap-2 ${activeTab === 'medicines' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'}`}
                    >
                        <Package size={18} />
                        Archived Medicines
                        <span className="bg-slate-900/10 text-slate-900 text-[10px] px-2 py-0.5 rounded-full ml-1">
                            {archivedMedicines.length}
                        </span>
                    </button>
                </div>

                {/* Health Tab Content */}
                {activeTab === 'health' && (
                    <div className="space-y-8">
                        {/* Status Card */}
                        <div className={`p-8 rounded-3xl border-2 transition-all duration-500 ${healthData?.status === 'healthy' ? 'bg-emerald-50 border-emerald-100' :
                            healthData?.status === 'warning' ? 'bg-amber-50 border-amber-100' :
                                healthData?.status === 'critical' ? 'bg-rose-50 border-rose-100' :
                                    'bg-slate-50 border-slate-100'
                            }`}>
                            <div className="flex flex-col md:flex-row items-center gap-8">
                                <div className={`w-24 h-24 rounded-3xl flex items-center justify-center text-white shadow-2xl ${getHealthColor(healthData?.status)}`}>
                                    {healthData?.status === 'healthy' ? <CheckCircle size={48} /> :
                                        healthData?.status === 'warning' ? <AlertTriangle size={48} /> :
                                            <XCircle size={48} />}
                                </div>
                                <div className="text-center md:text-left">
                                    <h2 className="text-4xl font-black text-slate-800 capitalize leading-none mb-3">
                                        System Status: {healthData?.status || 'Calculating...'}
                                    </h2>
                                    <p className="text-lg text-slate-500 font-bold max-w-xl break-words">
                                        {healthData?.status === 'healthy' ? 'Your CRM data is currently in perfect sync. No repairs needed.' :
                                            healthData?.status === 'warning' ? 'Minor data drift detected. Automated repairs are recommended.' :
                                                'Critical integrity failure. Financial ledger or stock levels are inconsistent.'}
                                    </p>
                                </div>
                                <div className="ml-auto flex flex-wrap justify-center md:justify-end gap-3">
                                    <div className="px-8 py-6 bg-white/80 rounded-3xl border border-white shadow-sm text-center">
                                        <div className="text-3xl font-black text-slate-900 leading-none">{healthData?.issues_found || 0}</div>
                                        <div className="text-[10px] font-black uppercase text-slate-400 tracking-widest mt-1">Issues Found</div>
                                    </div>
                                    <div className="px-8 py-6 bg-white/80 rounded-3xl border border-white shadow-sm text-center">
                                        <div className="text-3xl font-black text-slate-900 leading-none">{healthData?.repair_actions_available || 0}</div>
                                        <div className="text-[10px] font-black uppercase text-slate-400 tracking-widest mt-1">Ready Repairs</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Issue Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            {healthData ? Object.entries(healthData.checks).map(([key, items]) => (
                                <div key={key} className={`group p-8 bg-white rounded-3xl border-2 transition-all duration-300 hover:shadow-2xl hover:shadow-slate-200/50 ${items.length > 0 ? 'border-amber-200 bg-amber-50/20' : 'border-slate-50'
                                    }`}>
                                    <div className="flex justify-between items-start mb-6">
                                        <div className={`p-4 rounded-2xl ${items.length > 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400'}`}>
                                            {getIssueIcon(key)}
                                        </div>
                                        {items.length > 0 && (
                                            <span className="px-4 py-1.5 bg-amber-500 text-white text-[10px] font-black rounded-full uppercase tracking-widest animate-pulse">
                                                {items.length} Faults
                                            </span>
                                        )}
                                    </div>
                                    <h3 className="text-lg font-black text-slate-800 mb-2 truncate">{getIssueLabel(key)}</h3>
                                    <p className="text-sm text-slate-400 font-bold mb-8 leading-relaxed break-words">
                                        {items.length === 0 ? 'Scan complete. Integrity is verified for this data segment.' : 'Data mismatch detected that requires automated normalization.'}
                                    </p>

                                    <div className="flex gap-2">
                                        {items.length > 0 ? (
                                            <>
                                                <button
                                                    onClick={() => toast.success('Detail snapshot generation coming soon')}
                                                    className="flex-1 py-3 text-xs font-black bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition-all uppercase tracking-widest"
                                                >
                                                    Audit
                                                </button>
                                                {['duplicate_services', 'duplicate_medicines', 'inventory_batches', 'orphan_treatments', 'negative_inventory', 'medicine_drift', 'floating_ledger_entries'].includes(key) && (
                                                    <button
                                                        onClick={() => setRepairModal({ open: true, type: key, label: getIssueLabel(key), count: items.length })}
                                                        className="flex-1 py-3 text-xs font-black bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition-all shadow-lg shadow-slate-200 uppercase tracking-widest"
                                                    >
                                                        Repair
                                                    </button>
                                                )}
                                            </>
                                        ) : (
                                            <div className="flex items-center gap-2 text-emerald-500 text-[10px] font-black uppercase tracking-widest">
                                                <CheckCircle size={14} /> Verified Healthy
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )) : (
                                [...Array(6)].map((_, i) => (
                                    <div key={i} className="h-64 bg-slate-50 animate-pulse rounded-3xl border border-slate-100"></div>
                                ))
                            )}
                        </div>
                    </div>
                )}

                {/* Queue Tab Content */}
                {activeTab === 'queue' && (
                    <div className="animate-in slide-in-from-right-8 duration-500">
                        <div className="flex flex-wrap justify-between items-center mb-8 gap-4">
                            <div className="flex items-center gap-4">
                                <div className="p-4 bg-red-50 text-red-600 rounded-2xl shadow-sm">
                                    <ShieldAlert size={28} />
                                </div>
                                <div className="space-y-1">
                                    <h2 className="text-2xl font-black text-slate-800">Governance Queue</h2>
                                    <p className="text-sm text-slate-400 font-bold uppercase tracking-widest">Pending Structural Erasures</p>
                                </div>
                            </div>
                            <div>
                                <select
                                    value={statusFilter}
                                    onChange={(e) => setStatusFilter(e.target.value)}
                                    className="px-6 py-3 rounded-2xl border-2 border-slate-100 outline-none focus:border-slate-900 font-black text-xs text-slate-600 appearance-none bg-slate-50 cursor-pointer transition-all"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="executed">Executed</option>
                                </select>
                            </div>
                        </div>

                        {loadingQueue ? (
                            <div className="space-y-4">
                                {[...Array(5)].map((_, i) => (
                                    <div key={i} className="h-20 bg-slate-50 rounded-2xl w-full border border-slate-100"></div>
                                ))}
                            </div>
                        ) : requests.length === 0 ? (
                            <div className="py-24 text-center text-slate-400 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-100">
                                <ShieldAlert size={48} className="mx-auto mb-6 opacity-20" />
                                <p className="text-lg font-bold">The governance queue is currently empty.</p>
                            </div>
                        ) : (
                            <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="bg-slate-50/50 text-left">
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Reference</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Entity Module</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Workflow State</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Originator</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Age</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-50 bg-white">
                                            {requests.map(req => {
                                                const meta = STATUS_META[req.status] || STATUS_META.pending;
                                                const Icon = meta.icon;
                                                return (
                                                    <tr key={req.id} onClick={() => { setSelectedRequest(req); setIsRequestModalOpen(true); }} className="hover:bg-slate-50/80 transition-all cursor-pointer group">
                                                        <td className="px-8 py-6 font-black text-slate-900">#DL-{req.id}</td>
                                                        <td className="px-8 py-6">
                                                            <div className="font-black text-slate-800 capitalize leading-none mb-1">{req.entity_type}</div>
                                                            <div className="text-[10px] text-slate-400 font-black tracking-widest">ID: {req.entity_id}</div>
                                                        </td>
                                                        <td className="px-8 py-6">
                                                            <span className={`inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest ${meta.bg} ${meta.text}`}>
                                                                <Icon size={12} /> {meta.label}
                                                            </span>
                                                        </td>
                                                        <td className="px-8 py-6 font-bold text-slate-600">
                                                            {req.requester?.name || `System User #${req.requested_by}`}
                                                        </td>
                                                        <td className="px-8 py-6 text-xs text-slate-400 font-bold text-right">
                                                            {formatDate(req.created_at)}
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Specialties Tab Content */}
                {activeTab === 'specialties' && (
                    <div className="animate-in slide-in-from-right-8 duration-500">
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-6">
                            <div className="flex items-center gap-4">
                                <div className="p-4 bg-amber-50 text-amber-600 rounded-2xl shadow-sm">
                                    <ArchiveRestore size={28} />
                                </div>
                                <div className="space-y-1">
                                    <h2 className="text-2xl font-black text-slate-800">Archived Specialties</h2>
                                    <p className="text-sm text-slate-400 font-bold uppercase tracking-widest">Restoration Management</p>
                                </div>
                            </div>
                            <div className="relative w-full sm:w-auto overflow-hidden">
                                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" size={18} />
                                <input
                                    type="text"
                                    placeholder="Filter by name..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full sm:w-80 border-2 border-slate-100 rounded-2xl pl-12 pr-6 py-3.5 text-sm font-black outline-none focus:border-slate-900 transition-all bg-slate-50/50"
                                />
                            </div>
                        </div>

                        {loadingSpecialties ? (
                            <div className="space-y-4">
                                {[...Array(5)].map((_, i) => (
                                    <div key={i} className="h-20 bg-slate-50 rounded-2xl border border-slate-100 animate-pulse"></div>
                                ))}
                            </div>
                        ) : archivedSpecialties.length === 0 ? (
                            <div className="py-24 text-center text-slate-300 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-100">
                                <ArchiveRestore size={48} className="mx-auto mb-6 opacity-30" />
                                <p className="text-lg font-bold">No soft-deleted specialties discovered.</p>
                            </div>
                        ) : (
                            <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="bg-slate-50/50 text-left">
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Specialty Label</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Deletion Date</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Workflow</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-50 bg-white">
                                            {archivedSpecialties.filter(s => s.name.toLowerCase().includes(search.toLowerCase())).map(spec => (
                                                <tr key={spec.id} className="hover:bg-slate-50 transition-all group">
                                                    <td className="px-8 py-6 font-black text-slate-900">#{spec.id}</td>
                                                    <td className="px-8 py-6 font-black text-slate-800 text-base">{spec.name}</td>
                                                    <td className="px-8 py-6 text-slate-400 font-bold text-xs text-center">{formatDate(spec.deleted_at)}</td>
                                                    <td className="px-8 py-6">
                                                        <div className="flex flex-wrap items-center justify-end gap-3 translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-300">
                                                            <button
                                                                onClick={async () => {
                                                                    await api.post(`/admin/delete/specialty/${spec.id}/restore`);
                                                                    toast.success('Specialty restored successfully');
                                                                    fetchArchivedSpecialties();
                                                                }}
                                                                className="flex items-center gap-2 px-5 py-2.5 text-[10px] font-black bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl transition-all shadow-sm uppercase tracking-widest"
                                                            >
                                                                <RefreshCw size={14} /> Restore
                                                            </button>
                                                            {isSuperAdmin && (
                                                                <button
                                                                    onClick={() => handleForceDeleteClick('specialty', spec.id, spec.name)}
                                                                    className="flex items-center gap-2 px-5 py-2.5 text-[10px] font-black bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all shadow-sm uppercase tracking-widest"
                                                                >
                                                                    {loadingPreview ? <RefreshCw size={14} className="animate-spin" /> : <Trash2 size={14} />}
                                                                    Force Clear
                                                                </button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Services Tab Content */}
                {activeTab === 'services' && (
                    <div className="animate-in slide-in-from-right-8 duration-500">
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-6">
                            <div className="flex items-center gap-4">
                                <div className="p-4 bg-indigo-50 text-indigo-600 rounded-2xl shadow-sm">
                                    <Database size={28} />
                                </div>
                                <div className="space-y-1">
                                    <h2 className="text-2xl font-black text-slate-800">Archived Clinical Services</h2>
                                    <p className="text-sm text-slate-400 font-bold uppercase tracking-widest">Master Catalog Restoration</p>
                                </div>
                            </div>
                            <div className="relative w-full sm:w-auto overflow-hidden">
                                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" size={18} />
                                <input
                                    type="text"
                                    placeholder="Filter by name..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full sm:w-80 border-2 border-slate-100 rounded-2xl pl-12 pr-6 py-3.5 text-sm font-black outline-none focus:border-slate-900 transition-all bg-slate-50/50"
                                />
                            </div>
                        </div>

                        {loadingServices ? (
                            <div className="space-y-4">
                                {[...Array(5)].map((_, i) => (
                                    <div key={i} className="h-20 bg-slate-50 rounded-2xl border border-slate-100 animate-pulse"></div>
                                ))}
                            </div>
                        ) : archivedServices.length === 0 ? (
                            <div className="py-24 text-center text-slate-300 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-100">
                                <Database size={48} className="mx-auto mb-6 opacity-30" />
                                <p className="text-lg font-bold">No soft-deleted services discovered.</p>
                            </div>
                        ) : (
                            <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="bg-slate-50/50 text-left">
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Service Item</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Specialty Context</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Deletion Date</th>
                                                <th className="px-8 py-5 text-[10px) font-black text-slate-400 uppercase tracking-widest text-right">Workflow</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-50 bg-white">
                                            {archivedServices.filter(s => s.item_name.toLowerCase().includes(search.toLowerCase())).map(service => (
                                                <tr key={service.react_key || service.id} className="hover:bg-slate-50 transition-all group">
                                                    <td className="px-8 py-6 font-black text-slate-900">#{service.id}</td>
                                                    <td className="px-8 py-6 font-black text-slate-800 text-base">{service.item_name}</td>
                                                    <td className="px-8 py-6">
                                                        <span className="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                            {service.specialty?.name || 'Unlinked'}
                                                        </span>
                                                    </td>
                                                    <td className="px-8 py-6 text-slate-400 font-bold text-xs text-center">{formatDate(service.deleted_at)}</td>
                                                    <td className="px-8 py-6">
                                                        <div className="flex flex-wrap items-center justify-end gap-3 translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-300">
                                                            <button
                                                                onClick={async () => {
                                                                    const entityType = service.type === 'local' ? 'local_service' : 'service';
                                                                    await api.post(`/admin/delete/${entityType}/${service.id}/restore`);
                                                                    toast.success('Service restored successfully');
                                                                    fetchArchivedServices();
                                                                }}
                                                                className="flex items-center gap-2 px-5 py-2.5 text-[10px] font-black bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl transition-all shadow-sm uppercase tracking-widest"
                                                            >
                                                                <RefreshCw size={14} /> Restore
                                                            </button>
                                                            {isSuperAdmin && (
                                                                <button
                                                                    onClick={() => {
                                                                        const entityType = service.type === 'local' ? 'local_service' : 'service';
                                                                        handleForceDeleteClick(entityType, service.id, service.item_name);
                                                                    }}
                                                                    className="flex items-center gap-2 px-5 py-2.5 text-[10px] font-black bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all shadow-sm uppercase tracking-widest"
                                                                >
                                                                    <Trash2 size={14} /> Force Clear
                                                                </button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Medicines Tab Content */}
                {activeTab === 'medicines' && (
                    <div className="animate-in slide-in-from-right-8 duration-500">
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-6">
                            <div className="flex items-center gap-4">
                                <div className="p-4 bg-emerald-50 text-emerald-600 rounded-2xl shadow-sm">
                                    <Package size={28} />
                                </div>
                                <div className="space-y-1">
                                    <h2 className="text-2xl font-black text-slate-800">Archived Medicines</h2>
                                    <p className="text-sm text-slate-400 font-bold uppercase tracking-widest">Master Pharmacy Restoration</p>
                                </div>
                            </div>
                            <div className="relative w-full sm:w-auto overflow-hidden">
                                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" size={18} />
                                <input
                                    type="text"
                                    placeholder="Filter by name..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-full sm:w-80 border-2 border-slate-100 rounded-2xl pl-12 pr-6 py-3.5 text-sm font-black outline-none focus:border-slate-900 transition-all bg-slate-50/50"
                                />
                            </div>
                        </div>

                        {loadingMedicines ? (
                            <div className="space-y-4">
                                {[...Array(5)].map((_, i) => (
                                    <div key={i} className="h-20 bg-slate-50 rounded-2xl border border-slate-100 animate-pulse"></div>
                                ))}
                            </div>
                        ) : archivedMedicines.length === 0 ? (
                            <div className="py-24 text-center text-slate-300 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-100">
                                <Package size={48} className="mx-auto mb-6 opacity-30" />
                                <p className="text-lg font-bold">No soft-deleted medicines discovered.</p>
                            </div>
                        ) : (
                            <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="bg-slate-50/50 text-left">
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Medicine Name</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Specialty Context</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Deletion Date</th>
                                                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Workflow</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-50 bg-white">
                                            {archivedMedicines.filter(m => m.name.toLowerCase().includes(search.toLowerCase())).map(med => (
                                                <tr key={med.id} className="hover:bg-slate-50 transition-all group">
                                                    <td className="px-8 py-6 font-black text-slate-900">#{med.id}</td>
                                                    <td className="px-8 py-6 font-black text-slate-800 text-base">{med.name}</td>
                                                    <td className="px-8 py-6">
                                                        <span className="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                            {med.specialty?.name || 'Unlinked'}
                                                        </span>
                                                    </td>
                                                    <td className="px-8 py-6 text-slate-400 font-bold text-xs text-center">{formatDate(med.deleted_at)}</td>
                                                    <td className="px-8 py-6">
                                                        <div className="flex flex-wrap items-center justify-end gap-3 translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-300">
                                                            <button
                                                                onClick={async () => {
                                                                    await api.post(`/admin/delete/medicine/${med.id}/restore`);
                                                                    toast.success('Medicine restored successfully');
                                                                    fetchArchivedMedicines();
                                                                }}
                                                                className="flex items-center gap-2 px-5 py-2.5 text-[10px] font-black bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl transition-all shadow-sm uppercase tracking-widest"
                                                            >
                                                                <RefreshCw size={14} /> Restore
                                                            </button>
                                                            {isSuperAdmin && (
                                                                <button
                                                                    onClick={() => handleForceDeleteClick('medicine', med.id, med.name)}
                                                                    className="flex items-center gap-2 px-5 py-2.5 text-[10px] font-black bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all shadow-sm uppercase tracking-widest"
                                                                >
                                                                    <Trash2 size={14} /> Force Clear
                                                                </button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Modals outside the primary tab container */}
            {repairModal.open && (
                <div className="fixed inset-0 bg-slate-900/80 backdrop-blur-xl z-[100] flex items-center justify-center p-6 animate-in fade-in duration-300">
                    <div className="bg-white max-w-lg w-full rounded-3xl p-12 shadow-3xl relative overflow-hidden">
                        <div className="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full -translate-y-16 translate-x-16 opacity-50"></div>
                        <div className="w-20 h-20 bg-amber-100 text-amber-600 rounded-3xl flex items-center justify-center mb-8 shadow-inner">
                            <RefreshCw size={40} className={repairing ? 'animate-spin' : ''} />
                        </div>
                        <h3 className="text-3xl font-black text-slate-900 mb-3 tracking-tight">Authorize Repair</h3>
                        <p className="text-base font-bold text-slate-500 mb-10 leading-relaxed break-words">
                            Authorized personnel only. You are about to normalize <span className="text-slate-900 font-black border-b-2 border-amber-400">{repairModal.count} affected entries</span> for <span className="text-slate-900 font-black">{repairModal.label}</span>.
                            This changes database parity and structural logs.
                        </p>
                        <div className="space-y-3 mb-10">
                            <label className="text-[10px] font-black uppercase text-slate-400 ml-1 tracking-widest">Type REPAIR to bypass safety lock</label>
                            <input
                                type="text"
                                value={repairConfirm}
                                onChange={(e) => setRepairConfirm(e.target.value.toUpperCase())}
                                placeholder="Authorization Phrase"
                                className="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none focus:border-amber-400 font-black tracking-[0.5em] text-center text-lg placeholder:tracking-normal placeholder:font-bold transition-all"
                            />
                        </div>
                        <div className="flex flex-wrap gap-4">
                            <button onClick={() => { setRepairModal({ open: false, type: '', label: '', count: 0 }); setRepairConfirm(''); }} className="flex-1 py-5 bg-slate-50 hover:bg-slate-100 text-slate-500 font-black rounded-2xl transition-all uppercase tracking-widest text-xs">Abort</button>
                            <button
                                onClick={handleRepair}
                                disabled={repairConfirm !== 'REPAIR' || repairing}
                                className={`flex-1 py-5 font-black rounded-2xl transition-all shadow-2xl text-xs uppercase tracking-widest text-white ${repairConfirm === 'REPAIR' ? 'bg-slate-900 hover:bg-slate-800 shadow-slate-200 active:scale-95' : 'bg-slate-100 text-slate-300 cursor-not-allowed'}`}
                            >
                                {repairing ? 'Synchronizing...' : 'Authorize Execution'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {deleteModal.open && (
                <div className="fixed inset-0 bg-slate-900/80 backdrop-blur-xl z-[100] flex items-center justify-center p-6 animate-in fade-in duration-300">
                    <div className="bg-white max-w-md rounded-3xl p-12 shadow-3xl text-center relative overflow-hidden">
                        <div className="absolute top-0 left-0 w-32 h-32 bg-rose-50 rounded-full -translate-y-16 -translate-x-16 opacity-50"></div>
                        <div className="w-20 h-20 bg-rose-100 text-rose-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-inner">
                            <Trash2 size={40} />
                        </div>
                        <h3 className="text-3xl font-black text-slate-900 mb-4 tracking-tight">Targeted Erasure</h3>
                        <p className="text-base font-bold text-slate-500 mb-10 leading-relaxed">
                            Confirm permanent data erasure for <span className="text-slate-900 font-black">{deleteModal.names.join(', ')}</span>? This step cannot be reverted.
                        </p>
                        <div className="flex flex-wrap gap-4">
                            <button onClick={() => setDeleteModal({ open: false, ids: [], names: [], endpoint: '' })} className="flex-1 py-5 bg-slate-50 hover:bg-slate-100 text-slate-500 font-black rounded-2xl transition-all uppercase tracking-widest text-xs">Cancel</button>
                            <button
                                onClick={async () => {
                                    try {
                                        await api.delete(deleteModal.endpoint);
                                        toast.success('Erasure complete');
                                        if (deleteModal.type === 'specialty') fetchArchivedSpecialties();
                                        if (deleteModal.type === 'service') fetchArchivedServices();
                                        if (deleteModal.type === 'medicine') fetchArchivedMedicines();
                                    } catch {
                                        toast.error('System blocked erasure: active dependencies');
                                    } finally {
                                        setDeleteModal({ open: false, ids: [], names: [], endpoint: '', type: '' });
                                    }
                                }}
                                className="flex-1 py-5 bg-rose-600 hover:bg-rose-500 text-white font-black rounded-2xl transition-all shadow-2xl shadow-rose-200 uppercase tracking-widest text-xs active:scale-95"
                            >
                                Finalize
                            </button>
                        </div>
                    </div>
                </div>
            )}

            <DeletionRequestDetailsModal
                open={isRequestModalOpen}
                request={selectedRequest}
                onClose={() => {
                    setIsRequestModalOpen(false);
                    setSelectedRequest(null);
                }}
                onAction={() => fetchRequests()}
            />

            {isCascadeModalOpen && cascadePreview && (
                <div className="fixed inset-0 bg-slate-900/90 backdrop-blur-2xl z-[110] flex items-center justify-center p-6 animate-in zoom-in-95 duration-300">
                    <div className="bg-white max-w-2xl w-full rounded-3xl p-12 shadow-[0_35px_60px_-15px_rgba(0,0,0,0.3)] relative overflow-hidden">
                        <div className="absolute top-0 right-0 w-64 h-64 bg-rose-50 rounded-full -translate-y-32 translate-x-32 opacity-50 blur-3xl"></div>
                        <div className="flex items-center gap-6 mb-10">
                            <div className="w-20 h-20 bg-rose-600 text-white rounded-3xl flex items-center justify-center shadow-2xl shadow-rose-200">
                                <ShieldAlert size={40} />
                            </div>
                            <div>
                                <h3 className="text-3xl font-black text-slate-900 tracking-tight">System Safety Block</h3>
                                <p className="text-rose-600 font-bold uppercase tracking-widest text-xs mt-1">Found {cascadePreview.total_rows} active dependencies</p>
                            </div>
                        </div>
                        <div className="bg-slate-50 rounded-2xl p-8 border border-slate-100 mb-10 overflow-auto max-h-[40vh]">
                            <p className="text-slate-500 font-bold mb-6 text-sm leading-relaxed">
                                Standard erasure for <span className="text-slate-900 font-black">"{deleteModal.names[0]}"</span> was rejected by the integrity engine.
                                The following records will be permanently lost if you proceed with a cascade purge:
                            </p>
                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                {Object.entries(cascadePreview.will_delete).map(([key, count]) => count > 0 && (
                                    <div key={key} className="bg-white p-4 rounded-2xl border border-slate-100 flex flex-col items-center text-center">
                                        <div className="text-xl font-black text-slate-800 leading-none mb-1">{count}</div>
                                        <div className="text-[10px] font-black uppercase text-slate-400 tracking-widest truncate w-full">{key.replace('_', ' ')}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-4">
                            <button onClick={() => { setIsCascadeModalOpen(false); setCascadePreview(null); }} className="flex-1 py-6 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black rounded-3xl transition-all uppercase tracking-widest text-xs">Abort Operation</button>
                            <button
                                onClick={handleCascadeDelete}
                                className="flex-[1.5] py-6 bg-slate-900 hover:bg-slate-800 text-white font-black rounded-3xl transition-all shadow-2xl shadow-slate-300 uppercase tracking-widest text-xs active:scale-95 flex items-center justify-center gap-2"
                            >
                                {repairing ? <RefreshCw size={16} className="animate-spin" /> : null}
                                Force Cascade Purge
                            </button>
                        </div>
                        <p className="text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-8">This action is audited and irreversible. Transaction logging is active.</p>
                    </div>
                </div>
            )}
        </div>
    );
}
