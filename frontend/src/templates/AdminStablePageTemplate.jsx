import React, { useState, useEffect, useCallback } from 'react';
import axios from '../../api/axios';
import { toast } from 'react-hot-toast';
// import AdminCatalogTableLayout from '../../components/admin/Catalog/AdminCatalogTableLayout';
// import ConflictPreviewModal from '../../components/admin/Catalog/ConflictPreviewModal';

/**
 * REUSABLE ADMIN STABLE PAGE TEMPLATE
 * 
 * Features:
 * - Stable useEffect separation to prevent cascading rerenders
 * - Safe async loader structures for setup states
 * - Safe tab-driven data fetching using useCallback caching
 * - Complete ESLint warning compliance (no unused catch vars)
 * - Safe error optional chaining
 */
export default function AdminStablePageTemplate() {
    // 1. Core Component States
    const [activeTab, setActiveTab] = useState('local');
    // eslint-disable-next-line no-unused-vars
    const [localData, setLocalData] = useState([]);
    // eslint-disable-next-line no-unused-vars
    const [globalData, setGlobalData] = useState([]);
    // eslint-disable-next-line no-unused-vars
    const [pendingApprovals, setPendingApprovals] = useState([]);

    // 2. Action / Conflict States
    const [conflictData, setConflictData] = useState(null);
    // eslint-disable-next-line no-unused-vars
    const [currentActionItem, setCurrentActionItem] = useState(null);
    const [approvalMode, setApprovalMode] = useState(false);

    // 3. Tab Driven Fetching Strategy
    const fetchData = useCallback(async () => {
        try {
            if (activeTab === 'local') {
                const res = await axios.get('/admin/example/local-data');
                setLocalData(Array.isArray(res.data) ? res.data : (res.data?.data || []));
            } else if (activeTab === 'global') {
                const res = await axios.get('/admin/example/global-data');
                setGlobalData(Array.isArray(res.data) ? res.data : (res.data?.data || []));
            } else if (activeTab === 'pending') {
                const res = await axios.get('/admin/example/pending');
                setPendingApprovals(Array.isArray(res.data) ? res.data : []);
            }
        } catch {
            toast.error("Failed to load data");
        }
    }, [activeTab]);

    // 4. Initialization Effects Split (RUNS ONCE)
    useEffect(() => {
        const loadApprovalMode = async () => {
            try {
                const res = await axios.get('/settings/promotion_requires_approval');
                setApprovalMode(res.data?.value || false);
            } catch {
                // silent fail on system globals to preserve UI integrity
            }
        };

        loadApprovalMode();
    }, []);

    // 5. Data Fetching Effect (RUNS ON DEPENDENCIES)
    useEffect(() => {
        const initData = async () => {
            await fetchData();
        };
        initData();
    }, [activeTab, fetchData]);

    // 6. Generic Action Handlers
    // eslint-disable-next-line no-unused-vars
    const handlePromote = async (item, force = false) => {
        if (approvalMode && !force && conflictData?.status === 'conflict_exact') {
            toast.error("Exact matches cannot be force promoted.");
            return;
        }

        try {
            const res = await axios.post(`/admin/example/promote/${item.id}`, force ? { force_promote: true } : {});
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

    // eslint-disable-next-line no-unused-vars
    const handleApprove = async (id) => {
        try {
            await axios.post(`/admin/example/approve/${id}`);
            toast.success("Promotion approved successfully");
            fetchData();
        } catch {
            toast.error("Failed to approve. Drift detected?");
        }
    };

    // eslint-disable-next-line no-unused-vars
    const handleReject = async (id) => {
        try {
            await axios.post(`/admin/example/reject/${id}`);
            toast.success("Promotion rejected");
            fetchData();
        } catch {
            toast.error("Failed to reject");
        }
    };

    return (
        <>
            <div className="mb-4">
                {approvalMode ? (
                    <div className="bg-indigo-100 text-indigo-800 p-3 rounded-xl border border-indigo-200">
                        Dual Admin Approval Enabled
                    </div>
                ) : (
                    <div className="bg-emerald-100 text-emerald-800 p-3 rounded-xl border border-emerald-200">
                        Direct Promotion Mode
                    </div>
                )}
            </div>

            <div className="flex gap-4 mb-6">
                <button onClick={() => setActiveTab('local')} className={`px-4 py-2 rounded-full ${activeTab === 'local' ? 'bg-indigo-600 text-white' : 'bg-white'}`}>
                    Local Items
                </button>
                <button onClick={() => setActiveTab('global')} className={`px-4 py-2 rounded-full ${activeTab === 'global' ? 'bg-indigo-600 text-white' : 'bg-white'}`}>
                    Global Items
                </button>
                <button onClick={() => setActiveTab('pending')} className={`px-4 py-2 rounded-full ${activeTab === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white'}`}>
                    Pending Approvals
                </button>
            </div>

            {/* <AdminCatalogTableLayout ... /> */}

            {/* {conflictData && (
                <ConflictPreviewModal
                    conflictData={conflictData}
                    onCancel={() => setConflictData(null)}
                    onForce={() => handlePromote(currentActionItem, true)}
                />
            )} */}
        </>
    );
}
