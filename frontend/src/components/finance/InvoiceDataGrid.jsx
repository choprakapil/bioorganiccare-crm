import React, { useState, useEffect, useMemo, useCallback } from 'react';
import {
    ChevronLeft,
    ChevronRight,
    ExternalLink,
    FileText,
    Loader2
} from 'lucide-react';
import { List } from 'react-window/dist/react-window.js';
import api from '../../api/axios';
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

function cn(...inputs) {
    return twMerge(clsx(inputs));
}

// 1. Memoized Row Component (Converted to Flex/Grid for Virtualization)
const InvoiceRow = React.memo(({ index, style, invoices, formatCurrency, formatDate, getStatusStyle }) => {
    const invoice = invoices[index];

    if (!invoice) return null;

    return (
        <div
            style={style}
            className="group hover:bg-slate-50 transition-colors cursor-pointer border-b border-slate-50 flex items-center px-6"
            onClick={() => window.open(`/billing/invoices/${invoice.uuid || invoice.id}`, '_blank')}
        >
            <div className="grid grid-cols-7 w-full items-center gap-4">
                <div className="col-span-1">
                    <code className="text-[10px] font-bold text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100 tracking-tighter uppercase">
                        {invoice.uuid?.substring(0, 8) ?? 'N/A'}
                    </code>
                </div>
                <div className="col-span-1 overflow-hidden text-ellipsis whitespace-nowrap">
                    <p className="text-xs font-bold text-slate-900">{invoice.patient?.name || 'Anonymous'}</p>
                </div>
                <div className="col-span-1 text-right">
                    <p className="text-xs font-bold text-slate-900">{formatCurrency(invoice.total_amount)}</p>
                </div>
                <div className="col-span-1 text-right">
                    <p className="text-xs font-bold text-emerald-600">{formatCurrency(invoice.paid_amount)}</p>
                </div>
                <div className="col-span-1 text-right">
                    <p className={cn(
                        "text-xs font-bold",
                        parseFloat(invoice.balance_due) > 0 ? "text-rose-600" : "text-slate-400"
                    )}>
                        {formatCurrency(invoice.balance_due)}
                    </p>
                </div>
                <div className="col-span-1">
                    <span className={cn(
                        "inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-tight",
                        getStatusStyle(invoice.status)
                    )}>
                        {invoice.status}
                    </span>
                </div>
                <div className="col-span-1 flex items-center justify-between text-slate-500">
                    <p className="text-[11px] font-medium">{formatDate(invoice.created_at)}</p>
                    <ExternalLink className="w-3.5 h-3.5 text-slate-300 group-hover:text-primary transition-colors opacity-0 group-hover:opacity-100" />
                </div>
            </div>
        </div>
    );
});

const InvoiceDataGrid = () => {
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState(null);
    const [perPage, setPerPage] = useState(20);

    const fetchInvoices = useCallback(async (targetCursor = null) => {
        setLoading(true);
        try {
            const params = { per_page: perPage };
            if (targetCursor) params.cursor = targetCursor;

            const response = await api.get('/invoices', { params });
            setData(response.data);
        } catch (err) {
            console.error('Invoice grid fetch error:', err);
        } finally {
            setLoading(false);
        }
    }, [perPage]);

    useEffect(() => {
        fetchInvoices();
    }, [fetchInvoices]);

    const currencyFormatter = useMemo(() =>
        new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0,
        }),
        []);

    const formatCurrency = useCallback((value) => currencyFormatter.format(value), [currencyFormatter]);

    const formatDate = useCallback((dateStr) => {
        if (!dateStr) return '...';
        return new Intl.DateTimeFormat('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }).format(new Date(dateStr));
    }, []);

    const getStatusStyle = useCallback((status) => {
        const s = status?.toLowerCase();
        if (s === 'paid') return "bg-emerald-50 text-emerald-700 border-emerald-100";
        if (s === 'partial') return "bg-amber-50 text-amber-700 border-amber-100";
        if (s === 'unpaid' || s === 'overdue') return "bg-rose-50 text-rose-700 border-rose-100";
        if (s === 'cancelled') return "bg-slate-100 text-slate-600 border-slate-200";
        return "bg-slate-50 text-slate-500 border-slate-100";
    }, []);

    const invoices = useMemo(() => data?.data ?? [], [data?.data]);

    // Data object for react-window itemData
    const itemData = useMemo(() => ({
        invoices,
        formatCurrency,
        formatDate,
        getStatusStyle
    }), [invoices, formatCurrency, formatDate, getStatusStyle]);

    return (
        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6 animate-in fade-in slide-in-from-bottom-4 duration-500 relative">
            {/* Optimistic Progress Bar */}
            {loading && (
                <div className="absolute top-0 left-0 right-0 h-0.5 bg-slate-100 overflow-hidden z-20">
                    <div className="h-full bg-primary animate-pulse w-full"></div>
                </div>
            )}

            {/* Header */}
            <div className="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div className="space-y-1">
                    <h3 className="text-lg font-black text-slate-900 tracking-tight">Recent Invoices</h3>
                    <p className="text-xs text-slate-500 font-medium tracking-tight">Enterprise operational revenue records</p>
                </div>

                <div className="flex items-center gap-4">
                    <div className="flex items-center bg-slate-50 rounded-lg p-1 border border-slate-100">
                        {[10, 20, 50, 100].map((v) => (
                            <button
                                key={v}
                                onClick={() => setPerPage(v)}
                                className={cn(
                                    "px-3 py-1 text-[10px] font-black uppercase rounded-md transition-all",
                                    perPage === v ? "bg-white text-primary shadow-sm border border-slate-200" : "text-slate-400 hover:text-slate-600"
                                )}
                            >
                                {v}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            {/* Table Area (Virtualised) */}
            <div className="min-h-[400px] flex flex-col">
                {/* Static Header */}
                <div className="bg-slate-50/50 border-b border-slate-100 px-6 py-4">
                    <div className="grid grid-cols-7 w-full gap-4">
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Invoice UUID</div>
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Patient</div>
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Total</div>
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Paid</div>
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Balance</div>
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</div>
                        <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date</div>
                    </div>
                </div>

                <div className={cn("flex-1 transition-opacity duration-200", loading ? "opacity-50" : "opacity-100")}>
                    {invoices.length === 0 && !loading ? (
                        <div className="flex flex-col items-center justify-center text-slate-400 h-[400px]">
                            <FileText className="w-12 h-12 opacity-10 mb-2 text-slate-300" />
                            <p className="text-sm font-medium">No records found.</p>
                        </div>
                    ) : (
                        <List
                            rowCount={invoices.length}
                            rowHeight={56}
                            rowComponent={InvoiceRow}
                            rowProps={itemData}
                            style={{ height: 400, width: "100%" }}
                            className="custom-scrollbar"
                        />
                    )}
                </div>
            </div>

            {/* Pagination */}
            <div className="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">
                    Ledger View
                </div>

                <div className="flex items-center gap-2">
                    <button
                        onClick={() => fetchInvoices(data?.prev_cursor)}
                        disabled={!data?.prev_cursor || loading}
                        className="p-2 rounded-xl border border-slate-200 bg-white text-slate-400 hover:text-slate-900 disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-sm active:scale-95"
                    >
                        <ChevronLeft className="w-4 h-4" />
                    </button>
                    <button
                        onClick={() => fetchInvoices(data?.next_cursor)}
                        disabled={!data?.next_cursor || loading}
                        className="p-2 rounded-xl border border-slate-200 bg-white text-slate-400 hover:text-slate-900 disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-sm active:scale-95"
                    >
                        <ChevronRight className="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    );
};

export default InvoiceDataGrid;
