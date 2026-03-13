import React, { useState, useEffect, useMemo } from 'react';
import {
    ArrowUpRight,
    ArrowDownRight,
    IndianRupee,
    Package,
    Wallet,
    TrendingUp,
    AlertCircle,
    Clock
} from 'lucide-react';
import api from '../../api/axios';
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import RevenueTrendChart from '../../components/finance/RevenueTrendChart';
import GrowthSignalsPanel from '../../components/finance/GrowthSignalsPanel';
import InvoiceDataGrid from '../../components/finance/InvoiceDataGrid';

function cn(...inputs) {
    return twMerge(clsx(inputs));
}

const FinanceDashboard = () => {
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchSummary = async () => {
            try {
                const response = await api.get('/finance/summary');
                setData(response.data);
            } catch (err) {
                setError('Failed to fetch financial data. Please ensure you have doctor permissions.');
            } finally {
                setLoading(false);
            }
        };
        fetchSummary();
    }, []);

    const formatCurrency = useMemo(() => (value) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0,
        }).format(value);
    }, []);

    const formatDate = useMemo(() => (dateStr) => {
        if (!dateStr) return '...';
        return new Intl.DateTimeFormat('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }).format(new Date(dateStr));
    }, []);

    if (loading) {
        return (
            <div className="p-8 max-w-[1600px] mx-auto space-y-8 animate-in fade-in duration-500">
                <div className="space-y-2">
                    <div className="h-8 w-48 bg-slate-200 animate-pulse rounded" />
                    <div className="h-4 w-64 bg-slate-100 animate-pulse rounded" />
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                    {[1, 2, 3, 4, 5].map((i) => (
                        <div key={i} className="h-40 bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
                            <div className="flex justify-between">
                                <div className="h-4 w-20 bg-slate-100 rounded" />
                                <div className="h-8 w-8 bg-slate-50 rounded" />
                            </div>
                            <div className="h-8 w-32 bg-slate-100 rounded" />
                            <div className="h-4 w-24 bg-slate-50 rounded" />
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    if (error || !data || !data.metrics) {
        return (
            <div className="p-8 flex items-center justify-center min-h-[400px]">
                <div className="bg-rose-50 border border-rose-100 rounded-2xl p-8 max-w-md text-center">
                    <div className="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <AlertCircle className="w-6 h-6 text-rose-600" />
                    </div>
                    <h3 className="text-rose-900 font-semibold mb-2">Access Restricted</h3>
                    <p className="text-rose-700 text-sm mb-6">{error || 'Unable to load financial metrics.'}</p>
                    <button
                        onClick={() => window.location.reload()}
                        className="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-medium hover:bg-rose-700 transition-colors"
                    >
                        Retry Request
                    </button>
                </div>
            </div>
        );
    }

    const { metrics, window: reportWindow } = data;

    const StatCard = ({ title, metric, icon: Icon, isCurrency = true, isSnapshot = false }) => {
        const current = metric?.current ?? metric ?? 0;
        const previous = metric?.previous ?? 0;
        const rawChange = metric?.change_percent;

        const isFiniteChange = Number.isFinite(rawChange);
        const isNew = !isFiniteChange || (previous === 0 && current > 0);
        const isNeutral = previous === 0 && current === 0;

        // Final percentage logic
        const changePercent = isFiniteChange ? parseFloat(rawChange.toFixed(1)) : 0;
        const isPositive = changePercent > 0;
        const isNegative = changePercent < 0;

        return (
            <div className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm transition-all duration-200 ease-out hover:border-slate-300 hover:-translate-y-0.5 group cursor-default">
                <div className="flex justify-between items-start mb-6">
                    <div className="space-y-1">
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">{title}</p>
                    </div>
                    <div className="p-2.5 bg-slate-50 rounded-lg group-hover:bg-primary/5 transition-colors">
                        <Icon className="w-4 h-4 text-slate-400 group-hover:text-primary transition-colors" />
                    </div>
                </div>

                <div className="space-y-3">
                    <h3 className="text-2xl font-bold text-slate-900 tracking-tight">
                        {isCurrency ? formatCurrency(current) : current}
                    </h3>

                    {!isSnapshot ? (
                        <div className="flex items-center">
                            {isNew ? (
                                <div className="flex items-center text-[11px] font-bold px-2 py-0.5 rounded-full text-blue-700 bg-blue-50">
                                    New
                                </div>
                            ) : isNeutral ? (
                                <div className="flex items-center text-[11px] font-bold px-2 py-0.5 rounded-full text-slate-500 bg-slate-50">
                                    0%
                                </div>
                            ) : (
                                <div className={cn(
                                    "flex items-center text-[11px] font-bold px-2 py-0.5 rounded-full",
                                    isPositive ? "text-emerald-700 bg-emerald-50" : "text-rose-700 bg-rose-50"
                                )}>
                                    {isPositive ? <ArrowUpRight className="w-3 h-3 mr-1" /> : <ArrowDownRight className="w-3 h-3 mr-1" />}
                                    {isPositive ? '+' : ''}{changePercent}%
                                </div>
                            )}
                            <span className="text-[10px] text-slate-400 ml-2 font-medium">vs prev. 90d</span>
                        </div>
                    ) : (
                        <div className="flex items-center text-[10px] text-slate-400 font-medium italic">
                            <Clock className="w-3 h-3 mr-1" />
                            Real-time snapshot
                        </div>
                    )}
                </div>
            </div>
        );
    };

    return (
        <div className="p-8 max-w-[1600px] mx-auto space-y-10 animate-in fade-in slide-in-from-bottom-2 duration-700">
            <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div className="space-y-1">
                    <div className="flex items-center gap-2 mb-1 text-[10px] font-bold text-primary px-2 py-0.5 bg-primary/10 rounded-full w-fit uppercase tracking-widest">
                        Enterprise Module
                    </div>
                    <h1 className="text-3xl font-black text-slate-900 tracking-tight">Financial Hub</h1>
                    <p className="text-slate-500 text-sm max-w-md">
                        Comparative performance analytics for the clinical network.
                    </p>
                </div>

                <div className="flex items-center gap-2 text-xs font-medium text-slate-400 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <Clock className="w-3.5 h-3.5" />
                    <span>Window: </span>
                    <span className="text-slate-600 font-semibold">
                        {formatDate(reportWindow?.start)} → {formatDate(reportWindow?.end)}
                    </span>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                <StatCard title="Revenue" metric={metrics.revenue} icon={TrendingUp} />
                <StatCard title="Cash Collected" metric={metrics.cash_collected} icon={Wallet} />
                <StatCard title="Outstanding" metric={metrics.outstanding_balance} icon={AlertCircle} />
                <StatCard title="Net Profit" metric={metrics.net_profit} icon={IndianRupee} />
                <StatCard
                    title="Inventory Value"
                    metric={metrics.inventory_value}
                    icon={Package}
                    isSnapshot={true}
                />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2">
                    <RevenueTrendChart />
                </div>
                <GrowthSignalsPanel />
            </div>

            <InvoiceDataGrid />
        </div>
    );
};

export default FinanceDashboard;
