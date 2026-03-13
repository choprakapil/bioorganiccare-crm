/**
 * DeletionRequestDetailsModal
 *
 * Props:
 *   open         {boolean}
 *   request      {object}  — DeletionRequest row (with requester, approver relations)
 *   onClose      {function}
 *   onAction     {function(action, requestId)} — called after approve/reject/execute
 *
 * Behaviour:
 *   - When opened with status === 'approved' → auto-runs drift check
 *   - Drift panel shows green (safe) or red (blocked) state
 *   - Execute button disabled when driftData.drift === true
 *   - Backend hard-guard still enforced independently (Phase 5)
 */
import { useState, useEffect, useCallback } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import {
    ShieldAlert, AlertTriangle, CheckCircle, RefreshCw,
    Trash2, ThumbsUp, ThumbsDown, Clock, User, CalendarCheck,
    Layers, ArrowRightCircle, XCircle, Activity
} from 'lucide-react';

// ─── Helpers ─────────────────────────────────────────────────────────────────

const STATUS_META = {
    pending: { label: 'Pending', bg: 'bg-amber-100', text: 'text-amber-700', icon: Clock },
    approved: { label: 'Approved', bg: 'bg-emerald-100', text: 'text-emerald-700', icon: CheckCircle },
    rejected: { label: 'Rejected', bg: 'bg-red-100', text: 'text-red-700', icon: XCircle },
    executed: { label: 'Executed', bg: 'bg-slate-100', text: 'text-slate-600', icon: CheckCircle },
};

function StatusBadge({ status }) {
    const meta = STATUS_META[status] ?? STATUS_META.pending;
    const Icon = meta.icon;
    return (
        <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider ${meta.bg} ${meta.text}`}>
            <Icon size={11} /> {meta.label}
        </span>
    );
}

function MetaRow({ icon: Icon, label, value }) {
    return (
        <div className="flex items-start gap-3 py-2 border-b border-slate-100 last:border-0">
            <Icon size={14} className="text-slate-400 mt-0.5 shrink-0" />
            <span className="text-xs font-bold text-slate-400 w-28 shrink-0">{label}</span>
            <span className="text-xs font-bold text-slate-700 break-all">{value ?? '—'}</span>
        </div>
    );
}

// ─── Drift Panel ─────────────────────────────────────────────────────────────

function DriftPanel({ loading, driftData, onRecheck }) {
    if (loading) {
        return (
            <div className="mt-4 flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                <RefreshCw size={16} className="animate-spin text-slate-400 shrink-0" />
                <span className="text-sm font-bold text-slate-500">Running drift check…</span>
            </div>
        );
    }

    if (!driftData) return null;

    if (!driftData.drift) {
        return (
            <div className="mt-4 flex items-center justify-between gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                <div className="flex items-center gap-3">
                    <CheckCircle size={18} className="text-emerald-600 shrink-0" />
                    <div>
                        <p className="text-sm font-black text-emerald-700">No drift detected. Safe to execute.</p>
                        <p className="text-xs font-medium text-emerald-600 mt-0.5">
                            Live total: {driftData.live_total} rows — matches approval snapshot.
                        </p>
                    </div>
                </div>
                <button
                    onClick={onRecheck}
                    className="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 transition-colors shrink-0"
                >
                    <RefreshCw size={12} /> Recheck
                </button>
            </div>
        );
    }

    return (
        <div className="mt-4 space-y-3">
            {/* Warning header */}
            <div className="flex items-start justify-between gap-3 p-4 bg-red-50 border-2 border-red-300 rounded-2xl">
                <div className="flex items-start gap-3">
                    <AlertTriangle size={18} className="text-red-600 mt-0.5 shrink-0" />
                    <div>
                        <p className="text-sm font-black text-red-700">⚠ Data changed since approval.</p>
                        <p className="text-xs font-bold text-red-600 mt-0.5">
                            Re-request and re-approve required before execution.
                        </p>
                        <p className="text-xs text-red-500 mt-1">
                            Stored total: {driftData.stored_total} rows → Live total: {driftData.live_total} rows
                        </p>
                    </div>
                </div>
                <button
                    onClick={onRecheck}
                    className="text-xs font-bold text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors shrink-0"
                >
                    <RefreshCw size={12} /> Recheck
                </button>
            </div>

            {/* Differences table */}
            {Object.keys(driftData.differences).length > 0 && (
                <div className="border border-red-200 rounded-2xl overflow-hidden">
                    <div className="grid grid-cols-3 bg-red-100 px-4 py-2 text-xs font-black text-red-700 uppercase tracking-wider">
                        <span>Entity</span>
                        <span className="text-center">Stored</span>
                        <span className="text-center">Live</span>
                    </div>
                    {Object.entries(driftData.differences).map(([key, { stored, live }]) => (
                        <div
                            key={key}
                            className="grid grid-cols-3 px-4 py-2.5 border-t border-red-100 text-xs font-bold"
                        >
                            <span className="text-slate-700 capitalize">{key.replace(/_/g, ' ')}</span>
                            <span className="text-center text-slate-500">{stored}</span>
                            <span className={`text-center font-black ${live > stored ? 'text-red-600' : 'text-amber-600'}`}>
                                {live}
                                {live > stored ? ' ▲' : ' ▼'}
                            </span>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

// ─── Main Modal ───────────────────────────────────────────────────────────────

export default function DeletionRequestDetailsModal({ open, request, onClose, onAction }) {
    const [acting, setActing] = useState(false);
    const [rejectReason, setRejectReason] = useState('');
    const [showReject, setShowReject] = useState(false);
    const [driftData, setDriftData] = useState(null);
    const [driftLoading, setDriftLoading] = useState(false);

    const checkDrift = useCallback(async () => {
        if (!request?.id) return;
        setDriftLoading(true);
        setDriftData(null);
        try {
            const res = await api.get(`/admin/delete/requests/${request.id}/drift-check`);
            setDriftData(res.data);
        } catch (err) {
            toast.error(err.response?.data?.error || 'Drift check failed');
        } finally {
            setDriftLoading(false);
        }
    }, [request?.id]);

    // Auto-run drift check when modal opens and request is approved
    useEffect(() => {
        if (!open || !request) return;
        setActing(false);
        setRejectReason('');
        setShowReject(false);
        setDriftData(null);

        if (request.status === 'approved') {
            checkDrift();
        }
    }, [open, request?.id, request?.status]);

    if (!open || !request) return null;

    const isApproved = request.status === 'approved';
    const executionBlocked = driftData?.drift === true;

    const handleApprove = async () => {
        setActing(true);
        try {
            await api.post(`/admin/delete/requests/${request.id}/approve`);
            toast.success('Request approved');
            onAction?.('approved', request.id);
            onClose();
        } catch (err) {
            toast.error(err.response?.data?.error || 'Approve failed');
        } finally {
            setActing(false);
        }
    };

    const handleReject = async () => {
        if (!rejectReason.trim() || rejectReason.trim().length < 5) {
            toast.error('Rejection reason must be at least 5 characters');
            return;
        }
        setActing(true);
        try {
            await api.post(`/admin/delete/requests/${request.id}/reject`, { reason: rejectReason.trim() });
            toast.success('Request rejected');
            onAction?.('rejected', request.id);
            onClose();
        } catch (err) {
            toast.error(err.response?.data?.error || 'Reject failed');
        } finally {
            setActing(false);
        }
    };

    const handleExecute = async () => {
        if (executionBlocked) return;
        setActing(true);
        try {
            const res = await api.post(`/admin/delete/requests/${request.id}/execute`);
            toast.success(`Cascade executed — ${res.data?.counts?.specialty ?? 1} specialty + all linked data destroyed`);
            onAction?.('executed', request.id);
            onClose();
        } catch (err) {
            const msg = err.response?.data?.error || 'Execute failed';
            if (msg.includes('SNAPSHOT MISMATCH')) {
                toast.error('Snapshot mismatch — run a fresh drift check');
                checkDrift();
            } else {
                toast.error(msg);
            }
        } finally {
            setActing(false);
        }
    };

    const storedPreview = request.cascade_preview_json?.will_delete ?? {};
    const storedTotal = request.cascade_preview_json?.total_rows ?? Object.values(storedPreview).reduce((a, b) => a + (Number(b) || 0), 0);

    return (
        <div className="fixed inset-0 bg-slate-900/75 backdrop-blur-md z-[80] flex items-center justify-center p-4">
            <div className="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden">

                {/* Header */}
                <div className="px-10 pt-10 pb-6 border-b border-slate-100">
                    <div className="flex items-start justify-between gap-4">
                        <div className="flex items-center gap-4">
                            <div className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 ${request.status === 'rejected' ? 'bg-red-100'
                                : request.status === 'executed' ? 'bg-slate-100'
                                    : request.status === 'approved' ? 'bg-emerald-50'
                                        : 'bg-amber-50'
                                }`}>
                                <ShieldAlert size={22} className={
                                    request.status === 'rejected' ? 'text-red-600'
                                        : request.status === 'executed' ? 'text-slate-500'
                                            : request.status === 'approved' ? 'text-emerald-600'
                                                : 'text-amber-600'
                                } />
                            </div>
                            <div>
                                <h3 className="text-xl font-black text-slate-800">
                                    Deletion Request #{request.id}
                                </h3>
                                <p className="text-sm text-slate-500 font-medium mt-0.5">
                                    {request.entity_type} / ID {request.entity_id}
                                </p>
                            </div>
                        </div>
                        <StatusBadge status={request.status} />
                    </div>
                </div>

                {/* Body */}
                <div className="px-10 py-6 max-h-[60vh] overflow-y-auto space-y-5">

                    {/* Metadata */}
                    <div className="bg-slate-50 rounded-2xl px-5 py-3">
                        <MetaRow icon={User} label="Requested by" value={request.requester?.name ?? `User #${request.requested_by}`} />
                        <MetaRow icon={CalendarCheck} label="Requested at" value={request.created_at ? new Date(request.created_at).toLocaleString() : null} />
                        <MetaRow icon={User} label="Approved by" value={request.approver?.name ?? (request.approved_by ? `User #${request.approved_by}` : null)} />
                        <MetaRow icon={CalendarCheck} label="Approved at" value={request.approved_at ? new Date(request.approved_at).toLocaleString() : null} />
                        <MetaRow icon={Layers} label="Entity" value={`${request.entity_type} #${request.entity_id}`} />
                        {request.reason && (
                            <MetaRow icon={XCircle} label="Reason" value={request.reason} />
                        )}
                        {request.executed_at && (
                            <MetaRow icon={Activity} label="Executed at" value={new Date(request.executed_at).toLocaleString()} />
                        )}
                    </div>

                    {/* Stored cascade snapshot */}
                    {Object.keys(storedPreview).length > 0 && (
                        <div>
                            <p className="text-xs font-black text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <Layers size={12} /> Stored Cascade Snapshot — {storedTotal} rows
                            </p>
                            <div className="border border-slate-100 rounded-2xl overflow-hidden">
                                <div className="grid grid-cols-2 gap-px bg-slate-100">
                                    {[
                                        { key: 'plans', label: 'Plans' },
                                        { key: 'clinical_items', label: 'Clinical Items' },
                                        { key: 'master_medicines', label: 'Master Medicines' },
                                        { key: 'doctors', label: 'Doctors' },
                                        { key: 'patients', label: 'Patients' }
                                    ].map(({ key, label }) => {
                                        const count = storedPreview[key] || 0;
                                        return (
                                            <div key={key} className="flex justify-between items-center px-5 py-3 bg-white">
                                                <span className="text-xs font-bold text-slate-600">{label}</span>
                                                <span className={`text-xs font-black px-2.5 py-0.5 rounded-full ${count > 0 ? 'bg-red-50 text-red-700' : 'bg-slate-50 text-slate-400'}`}>
                                                    {count}
                                                </span>
                                            </div>
                                        );
                                    })}
                                    <div className="flex justify-between items-center px-5 py-3 bg-slate-50 col-span-2">
                                        <span className="text-sm font-black text-slate-700">Total Impact</span>
                                        <span className="text-sm font-black text-red-600">{storedTotal} Rows</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Drift Panel — only for approved status */}
                    {isApproved && (
                        <div>
                            <div className="flex items-center justify-between mb-1">
                                <p className="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                    <Activity size={12} /> Live Drift Check
                                </p>
                            </div>
                            <DriftPanel
                                loading={driftLoading}
                                driftData={driftData}
                                onRecheck={checkDrift}
                            />
                        </div>
                    )}

                    {/* Reject form */}
                    {showReject && (
                        <div className="space-y-2">
                            <p className="text-xs font-bold text-slate-500 uppercase tracking-wider">Rejection Reason</p>
                            <textarea
                                rows={3}
                                value={rejectReason}
                                onChange={e => setRejectReason(e.target.value)}
                                placeholder="Explain why this request is being rejected…"
                                className="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-medium text-slate-700 focus:outline-none focus:ring-4 focus:ring-red-100 focus:border-red-300 resize-none transition-all"
                            />
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="px-10 pb-10 pt-4 border-t border-slate-100 flex flex-wrap justify-between items-center gap-3">

                    {/* Left: status-sensitive action buttons */}
                    <div className="flex items-center gap-2">

                        {/* Approve — only for pending */}
                        {request.status === 'pending' && (
                            <button
                                disabled={acting}
                                onClick={handleApprove}
                                className="flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-sm font-black rounded-2xl hover:bg-emerald-700 transition-all disabled:opacity-50"
                            >
                                {acting ? <RefreshCw size={14} className="animate-spin" /> : <ThumbsUp size={14} />}
                                Approve
                            </button>
                        )}

                        {/* Reject — for pending or approved */}
                        {(request.status === 'pending' || request.status === 'approved') && !showReject && (
                            <button
                                disabled={acting}
                                onClick={() => setShowReject(true)}
                                className="flex items-center gap-2 px-6 py-2.5 bg-slate-100 text-slate-600 text-sm font-black rounded-2xl hover:bg-slate-200 transition-all"
                            >
                                <ThumbsDown size={14} /> Reject
                            </button>
                        )}

                        {/* Confirm reject */}
                        {showReject && (
                            <>
                                <button
                                    disabled={acting || rejectReason.trim().length < 5}
                                    onClick={handleReject}
                                    className="flex items-center gap-2 px-6 py-2.5 bg-red-600 text-white text-sm font-black rounded-2xl hover:bg-red-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {acting ? <RefreshCw size={14} className="animate-spin" /> : <XCircle size={14} />}
                                    Confirm Reject
                                </button>
                                <button
                                    onClick={() => { setShowReject(false); setRejectReason(''); }}
                                    className="text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors"
                                >
                                    Cancel
                                </button>
                            </>
                        )}

                        {/* Execute — only for approved, blocked by drift */}
                        {isApproved && !showReject && (
                            <button
                                disabled={acting || driftLoading || executionBlocked}
                                onClick={handleExecute}
                                title={
                                    driftLoading ? 'Drift check in progress…'
                                        : executionBlocked ? 'Drift detected — re-request and re-approve required'
                                            : !driftData ? 'Drift check not yet completed'
                                                : 'Execute cascade delete'
                                }
                                className={`flex items-center gap-2 px-6 py-2.5 text-sm font-black rounded-2xl transition-all ${!acting && !driftLoading && !executionBlocked && driftData
                                    ? 'bg-red-700 text-white hover:bg-red-800 shadow-lg shadow-red-200'
                                    : 'bg-slate-200 text-slate-400 cursor-not-allowed'
                                    }`}
                            >
                                {acting
                                    ? <><RefreshCw size={14} className="animate-spin" /> Executing…</>
                                    : <><Trash2 size={14} /> Execute Cascade</>
                                }
                            </button>
                        )}
                    </div>

                    {/* Right: close */}
                    <button
                        onClick={onClose}
                        disabled={acting}
                        className="px-7 py-2.5 font-bold text-slate-500 hover:bg-slate-100 rounded-2xl transition-all text-sm"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    );
}
