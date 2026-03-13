/**
 * DeleteManagerModal — Enterprise Delete UI with Cascade Preview
 *
 * Props:
 *   open          {boolean}   — Controls visibility
 *   entity        {string}    — 'specialty' | 'service' | 'medicine' | 'doctor'
 *   ids           {number[]}  — Single [id] or multiple for bulk
 *   entityNames   {string[]}  — Display names matching ids[]
 *   onClose       {function}  — Dismiss callback
 *   onSuccess     {function}  — Post-delete callback (refresh parent)
 *
 * Cascade preview:
 *   - Fetched separately when cascade checkbox is enabled (specialty only)
 *   - Renders will_delete breakdown with per-domain counts
 *   - Required confirm text: DELETE {NAME} FOREVER
 *   - Execute button disabled until exact match
 */
import { useState, useEffect, useCallback } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import {
    AlertTriangle, ShieldAlert, Trash2, RefreshCw, Check,
    Users, CreditCard, FileText, FolderOpen, Package, Pill,
    CheckSquare, Square, Eye, Layers, Calendar, ClipboardList,
    Boxes, Stethoscope
} from 'lucide-react';

// ─── Counts by domain ────────────────────────────────────────────────────────

const WILL_DELETE_META = [
    { key: 'patients', label: 'Patients', icon: Users },
    { key: 'treatments', label: 'Treatments', icon: Stethoscope },
    { key: 'invoices', label: 'Invoices', icon: FileText },
    { key: 'invoice_items', label: 'Invoice Line Items', icon: ClipboardList },
    { key: 'appointments', label: 'Appointments', icon: Calendar },
    { key: 'inventory', label: 'Inventory Rows', icon: Boxes },
    { key: 'doctor_settings', label: 'Doctor Service Settings', icon: Stethoscope },
    { key: 'clinical_items', label: 'Clinical Catalog Items', icon: FileText },
    { key: 'clinical_categories', label: 'Clinical Categories', icon: FolderOpen },
    { key: 'medicines', label: 'Master Medicines', icon: Pill },
    { key: 'pharmacy_categories', label: 'Pharmacy Categories', icon: Package },
    { key: 'staff', label: 'Staff Members', icon: Users },
    { key: 'doctors', label: 'Doctors', icon: Users },
    { key: 'plans', label: 'Subscription Plans', icon: CreditCard },
    { key: 'specialty_modules', label: 'Module Pivot Rows', icon: Layers },
    { key: 'specialty', label: 'Specialty Record', icon: Layers },
];

// ─── Dependency row ──────────────────────────────────────────────────────────

function DepRow({ icon: Icon, label, count, variant = 'dependency' }) {
    const isBlocked = variant === 'dependency' && count > 0;
    const isWillDel = variant === 'preview';
    const zeroBg = 'bg-white';
    const blockedBg = isBlocked ? 'bg-red-50/60' : '';
    const previewBg = isWillDel && count > 0 ? 'bg-orange-50/70' : '';
    const bg = blockedBg || previewBg || zeroBg;

    const badgeClass = isWillDel
        ? count > 0
            ? 'bg-orange-100 text-orange-800'
            : 'bg-emerald-100 text-emerald-700'
        : count > 0
            ? 'bg-red-100 text-red-700'
            : 'bg-emerald-100 text-emerald-700';

    const labelClass = isWillDel
        ? count > 0 ? 'text-orange-800' : 'text-slate-400'
        : count > 0 ? 'text-red-700' : 'text-slate-500';

    const iconClass = isWillDel
        ? count > 0 ? 'text-orange-400' : 'text-slate-200'
        : count > 0 ? 'text-red-400' : 'text-slate-300';

    return (
        <div className={`flex items-center justify-between px-5 py-3 border-b border-slate-100 last:border-0 ${bg}`}>
            <div className="flex items-center gap-3">
                <Icon size={14} className={iconClass} />
                <span className={`text-sm font-bold ${labelClass}`}>{label}</span>
            </div>
            <span className={`text-xs font-black px-3 py-0.5 rounded-full ${badgeClass}`}>
                {isWillDel
                    ? count > 0 ? `${count} will be deleted` : 'none'
                    : count > 0 ? `${count} blocking` : 'clear'}
            </span>
        </div>
    );
}

// ─── Specialty dependency rows ────────────────────────────────────────────────

function SpecialtyDepRows({ summary }) {
    return (
        <div className="border border-slate-100 rounded-2xl overflow-hidden">
            <DepRow icon={Users} label="Doctors / Staff" count={summary.doctors ?? 0} />
            <DepRow icon={CreditCard} label="Subscription Plans" count={summary.plans ?? 0} />
            <DepRow icon={FileText} label="Clinical Catalog Items" count={summary.clinical_items ?? 0} />
            <DepRow icon={FolderOpen} label="Clinical Categories" count={summary.clinical_categories ?? 0} />
            <DepRow icon={Package} label="Pharmacy Categories" count={summary.pharmacy_categories ?? 0} />
            <DepRow icon={Pill} label="Master Medicines" count={summary.medicines ?? 0} />
        </div>
    );
}

// ─── Generic dependency rows ──────────────────────────────────────────────────

function GenericDepRows({ summary, entity }) {
    if (entity === 'service') return (
        <div className="border border-slate-100 rounded-2xl overflow-hidden">
            <DepRow icon={FileText} label="Treatment References" count={summary.treatments ?? 0} />
            <DepRow icon={Users} label="Doctor Settings" count={summary.doctor_settings ?? 0} />
        </div>
    );
    if (entity === 'medicine') return (
        <div className="border border-slate-100 rounded-2xl overflow-hidden">
            <DepRow icon={Package} label="Inventory Batches" count={summary.inventory_batches ?? 0} />
        </div>
    );
    if (entity === 'doctor') return (
        <div className="border border-slate-100 rounded-2xl overflow-hidden">
            <DepRow icon={Users} label="Staff Members" count={summary.staff ?? 0} />
            <DepRow icon={FileText} label="Patients" count={summary.patients ?? 0} />
        </div>
    );
    return null;
}

// ─── Cascade Preview Panel ────────────────────────────────────────────────────

function CascadePreviewPanel({ preview, loading }) {
    if (loading) {
        return (
            <div className="mt-4 animate-pulse space-y-2">
                {[...Array(6)].map((_, i) => (
                    <div key={i} className="h-9 bg-orange-100/60 rounded-xl" />
                ))}
            </div>
        );
    }

    if (!preview?.will_delete) return null;

    const nonZero = WILL_DELETE_META.filter(m => (preview.will_delete[m.key] ?? 0) > 0);
    const zero = WILL_DELETE_META.filter(m => (preview.will_delete[m.key] ?? 0) === 0);

    return (
        <div className="mt-4">
            {/* Impact header */}
            <div className="flex items-center justify-between mb-2">
                <span className="text-xs font-black text-orange-700 uppercase tracking-wider flex items-center gap-1.5">
                    <Eye size={12} /> Cascade Impact — {preview.total_rows} rows destroyed
                </span>
                <span className="text-[10px] font-black text-red-500 uppercase tracking-wider">IRREVERSIBLE</span>
            </div>

            {/* All rows */}
            <div className="border border-orange-200 rounded-2xl overflow-hidden">
                {WILL_DELETE_META.map(({ key, label, icon }) => (
                    <DepRow
                        key={key}
                        icon={icon}
                        label={label}
                        count={preview.will_delete[key] ?? 0}
                        variant="preview"
                    />
                ))}
            </div>

            <div className="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2">
                <ShieldAlert size={14} className="text-red-600 mt-0.5 shrink-0" />
                <p className="text-xs font-bold text-red-700 leading-relaxed">
                    This action permanently destroys <strong>{preview.total_rows} rows</strong> across all domains.
                    There is no undo, no recycle bin, no recovery.
                </p>
            </div>
        </div>
    );
}

// ─── Main Modal ───────────────────────────────────────────────────────────────

export default function DeleteManagerModal({ open, entity, ids = [], entityNames = [], onClose, onSuccess }) {
    const isBulk = ids.length > 1;
    const singleId = ids[0];
    const singleName = entityNames[0] ?? '';

    const [loading, setLoading] = useState(false);
    const [summaries, setSummaries] = useState({});
    const [cascade, setCascade] = useState(false);
    const [confirmInput, setConfirmInput] = useState('');
    const [acting, setActing] = useState(false);

    // Cascade preview state — fetched lazily when cascade is toggled ON
    const [previewData, setPreviewData] = useState(null);
    const [previewLoading, setPreviewLoading] = useState(false);

    // Cascade confirm text: DELETE {NAME} FOREVER
    const cascadeConfirmText = `DELETE ${singleName.toUpperCase()} FOREVER`;
    const normalConfirmText = singleName;
    const bulkNormalText = 'DELETE SELECTED';

    const requiredText = isBulk
        ? (cascade ? `DELETE ${ids.length} SPECIALTIES FOREVER` : bulkNormalText)
        : (cascade ? cascadeConfirmText : normalConfirmText);

    const isMatch = confirmInput === requiredText;

    const canCascade = entity === 'specialty';
    const safeIds = ids.filter(id => summaries[id]?.force_delete_safe === true);
    const blockedIds = ids.filter(id => summaries[id] && !summaries[id]?.force_delete_safe);
    const allSafe = ids.length > 0 && ids.every(id => summaries[id]?.force_delete_safe === true);
    const canExecute = !acting && !loading && isMatch && (cascade || allSafe);

    // ── Fetch dependency summaries ────────────────────────────────────────────

    useEffect(() => {
        if (!open || ids.length === 0) return;
        setSummaries({});
        setConfirmInput('');
        setCascade(false);
        setPreviewData(null);
        setLoading(true);

        Promise.all(
            ids.map(id =>
                api.get(`/admin/delete/${entity}/${id}/summary`)
                    .then(res => ({ id, data: res.data }))
                    .catch(() => ({ id, data: null }))
            )
        ).then(results => {
            const map = {};
            results.forEach(({ id, data }) => { map[id] = data; });
            setSummaries(map);
            setLoading(false);
        });
    }, [open, ids.join(','), entity]);

    // ── Fetch cascade preview when cascade is toggled ON ─────────────────────

    useEffect(() => {
        if (!cascade || !canCascade || isBulk) {
            setPreviewData(null);
            return;
        }
        setPreviewLoading(true);
        setPreviewData(null);
        api.get(`/admin/delete/${entity}/${singleId}/cascade-preview`)
            .then(res => setPreviewData(res.data))
            .catch(() => toast.error('Failed to load cascade preview'))
            .finally(() => setPreviewLoading(false));
    }, [cascade, singleId, entity]);

    if (!open) return null;

    const handleClose = () => {
        setConfirmInput('');
        setCascade(false);
        setPreviewData(null);
        onClose();
    };

    const handleAction = async () => {
        if (!canExecute) return;
        setActing(true);
        try {
            if (isBulk) {
                const res = await api.delete(`/admin/delete/${entity}/bulk`, {
                    data: { ids: cascade ? ids : safeIds, cascade }
                });
                const { deleted, blocked, errors } = res.data;
                if (deleted.length) toast.success(`${deleted.length} ${entity}(s) permanently deleted`);
                if (blocked.length) toast.error(`${blocked.length} blocked by dependencies`);
                if (errors.length) toast.error(`${errors.length} error(s) occurred`);
            } else {
                if (cascade) {
                    await api.delete(`/admin/delete/${entity}/${singleId}/force-cascade`);
                    toast.success(`${singleName} and all linked data permanently destroyed`);
                } else {
                    await api.delete(`/admin/delete/${entity}/${singleId}/force`);
                    toast.success(`${singleName} permanently deleted`);
                }
            }
            handleClose();
            onSuccess?.();
        } catch (err) {
            toast.error(err.response?.data?.message || err.response?.data?.error || 'Delete failed');
        } finally {
            setActing(false);
        }
    };

    // ── Icon & subtitle helpers ───────────────────────────────────────────────

    const headerIcon = loading
        ? null
        : cascade
            ? <ShieldAlert size={22} className="text-red-600" />
            : allSafe
                ? <Check size={22} className="text-emerald-600" />
                : <AlertTriangle size={22} className="text-amber-600" />;

    const headerBg = loading ? 'bg-slate-100'
        : cascade ? 'bg-red-100'
            : allSafe ? 'bg-emerald-50'
                : 'bg-amber-50';

    const subtitle = loading
        ? 'Scanning dependencies…'
        : cascade
            ? 'FULL CASCADE: all linked data will be permanently destroyed'
            : allSafe
                ? 'No dependencies. Safe to permanently delete.'
                : 'Dependencies exist — deletion is blocked';

    return (
        <div className="fixed inset-0 bg-slate-900/75 backdrop-blur-md z-[70] flex items-center justify-center p-4">
            <div className="bg-white w-full max-w-xl rounded-[2.5rem] shadow-2xl overflow-hidden">

                {/* Header */}
                <div className="px-10 pt-10 pb-5">
                    <div className="flex items-center gap-4 mb-2">
                        <div className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 ${headerBg}`}>
                            {loading
                                ? <RefreshCw size={20} className="animate-spin text-slate-400" />
                                : headerIcon}
                        </div>
                        <div>
                            <h3 className="text-xl font-black text-slate-800">
                                {isBulk
                                    ? `Bulk Delete — ${ids.length} ${entity}s`
                                    : cascade
                                        ? `Full Cascade Delete`
                                        : 'Dependency Summary'}
                            </h3>
                            <p className="text-sm text-slate-500 font-medium">{subtitle}</p>
                        </div>
                    </div>
                </div>

                {/* Scrollable body */}
                <div className="px-10 pb-4 max-h-[58vh] overflow-y-auto space-y-4">

                    {/* Loading skeleton */}
                    {loading && (
                        <div className="animate-pulse space-y-3">
                            {[...Array(5)].map((_, i) => (
                                <div key={i} className="h-10 bg-slate-100 rounded-xl" />
                            ))}
                        </div>
                    )}

                    {/* Single entity dependency table */}
                    {!loading && !isBulk && summaries[singleId] && !cascade && (
                        <div>
                            <p className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Entity: <span className="text-slate-700">{summaries[singleId]?.entity_name}</span>
                            </p>
                            {entity === 'specialty'
                                ? <SpecialtyDepRows summary={summaries[singleId]} />
                                : <GenericDepRows summary={summaries[singleId]} entity={entity} />
                            }
                        </div>
                    )}

                    {/* Bulk summary */}
                    {!loading && isBulk && (
                        <div className="space-y-2">
                            {safeIds.length > 0 && (
                                <div className="flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-700">
                                    <Check size={14} /> {safeIds.length} safe to delete
                                </div>
                            )}
                            {blockedIds.length > 0 && (
                                <div className="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl text-xs font-bold text-red-700">
                                    <AlertTriangle size={14} />
                                    {blockedIds.length} blocked by dependencies
                                    {cascade ? ' (will force cascade)' : ' (will be skipped)'}
                                </div>
                            )}
                        </div>
                    )}

                    {/* Non-cascade blocker warning */}
                    {!loading && !allSafe && !cascade && (
                        <div className="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                            <AlertTriangle size={14} className="text-amber-600 mt-0.5 shrink-0" />
                            <p className="text-xs font-bold text-amber-700 leading-relaxed">
                                {isBulk
                                    ? `${blockedIds.length} ${entity}(s) have dependencies and will be skipped. Only ${safeIds.length} safe item(s) will be deleted.`
                                    : 'Cannot permanently delete until all dependencies are cleared.'}
                            </p>
                        </div>
                    )}

                    {/* Cascade Preview Panel — shown after cascade checkbox enabled */}
                    {!loading && cascade && !isBulk && (
                        <CascadePreviewPanel preview={previewData} loading={previewLoading} />
                    )}

                    {/* Cascade toggle (specialty only) */}
                    {!loading && canCascade && (
                        <button
                            onClick={() => { setCascade(v => !v); setConfirmInput(''); }}
                            className={`w-full flex items-center gap-3 p-4 rounded-2xl border-2 transition-all ${cascade
                                    ? 'bg-red-50 border-red-400 text-red-700'
                                    : 'bg-slate-50 border-slate-200 text-slate-600 hover:border-slate-300'
                                }`}
                        >
                            {cascade
                                ? <CheckSquare size={18} className="text-red-600 shrink-0" />
                                : <Square size={18} className="shrink-0" />}
                            <div className="text-left">
                                <div className="text-sm font-black">Enable Full Cascade Delete</div>
                                <div className="text-xs font-medium opacity-70">
                                    Permanently destroys all linked doctors, patients, treatments, invoices, inventory and catalog data.
                                    A preview will load automatically.
                                </div>
                            </div>
                        </button>
                    )}

                    {/* Type-to-confirm — shown when safe OR cascade enabled */}
                    {!loading && !previewLoading && (allSafe || cascade) && (
                        <div>
                            <div className="border-t border-dashed border-slate-200 mb-4" />
                            <p className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">
                                Type to confirm
                            </p>
                            <p className="text-sm font-bold text-slate-700 mb-3">
                                <span className="font-black text-red-600 uppercase tracking-wide">
                                    {requiredText}
                                </span>
                            </p>
                            <input
                                type="text"
                                autoFocus
                                value={confirmInput}
                                onChange={e => setConfirmInput(e.target.value)}
                                onPaste={e => e.preventDefault()}
                                placeholder={`Type: ${requiredText}`}
                                className="w-full px-5 py-3.5 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-red-100 focus:border-red-300 font-bold text-slate-700 transition-all"
                            />
                            {confirmInput.length > 0 && !isMatch && (
                                <p className="text-xs font-bold text-red-500 mt-1.5 ml-1">
                                    Text does not match — button will remain disabled
                                </p>
                            )}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="px-10 pb-10 pt-3 flex justify-end gap-3 border-t border-slate-100">
                    <button
                        onClick={handleClose}
                        disabled={acting}
                        className="px-7 py-3 font-bold text-slate-500 hover:bg-slate-100 rounded-2xl transition-all"
                    >
                        Cancel
                    </button>
                    <button
                        disabled={!canExecute}
                        onClick={handleAction}
                        title={
                            !allSafe && !cascade
                                ? 'Blocked by dependencies'
                                : !isMatch
                                    ? 'Type the exact confirmation text above'
                                    : cascade
                                        ? 'Full cascade delete — irreversible'
                                        : 'Permanently delete'
                        }
                        className={`px-8 py-3 rounded-2xl font-black transition-all flex items-center gap-2 ${canExecute
                                ? cascade
                                    ? 'bg-red-700 text-white hover:bg-red-800 shadow-lg shadow-red-300'
                                    : 'bg-red-600 text-white hover:bg-red-700 shadow-lg shadow-red-200'
                                : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                            }`}
                    >
                        {acting
                            ? <><RefreshCw size={15} className="animate-spin" /> Deleting…</>
                            : <><Trash2 size={15} /> {
                                cascade
                                    ? 'Cascade Delete'
                                    : isBulk
                                        ? `Delete ${safeIds.length} Safe`
                                        : 'Permanently Delete'
                            }</>
                        }
                    </button>
                </div>
            </div>
        </div>
    );
}
