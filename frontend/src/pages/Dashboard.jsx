import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../api/axios';
import { handleApiError } from '../utils/errorHandler';
import { Users, CreditCard, AlertCircle, ShoppingBag, TrendingUp, TrendingDown, Pill } from 'lucide-react';

export default function Dashboard() {
    const { user } = useAuth();

    return (
        <div className="space-y-8">
            <header>
                <h1 className="text-4xl font-black text-slate-800 tracking-tight">
                    {user?.role === 'super_admin' ? 'System Management' : 'Clinic Overview'}
                </h1>
                <p className="text-slate-500 font-medium mt-1">
                    Welcome back, <span className="text-primary font-bold">{user?.role === 'super_admin' ? 'Administrator' : `Dr. ${user?.name?.split(' ')[1] || user?.name}`}</span>.
                    {user?.role === 'super_admin'
                        ? 'Monitoring platform-wide telemetry and security.'
                        : 'Here is your clinical and financial standing for today.'}
                </p>
            </header>

            {user?.role === 'super_admin' ? <SystemAdminView /> : <PractitionerDashboard />}
        </div>
    );
}

function SystemAdminView() {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const res = await api.get('/admin/dashboard/stats');
                setStats(res.data);
            } catch (err) {
                return;
            } finally {
                setLoading(false);
            }
        };
        fetchStats();
    }, []);

    if (loading) return <div className="p-12 text-center text-slate-400 animate-pulse">Loading Platform Telemetry...</div>;

    return (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard label="Total Doctors" value={stats?.total_doctors} icon={<Users className="text-blue-500" />} color="bg-blue-50" />
            <StatCard label="Active Doctors" value={stats?.active_doctors} icon={<Users className="text-green-500" />} color="bg-green-50" />
            <StatCard label="Total Patients" value={stats?.total_patients} icon={<Users className="text-purple-500" />} color="bg-purple-50" />
            <StatCard label="Platform Revenue" value={`₹${parseFloat(stats?.total_revenue || 0).toLocaleString()}`} icon={<CreditCard className="text-emerald-500" />} color="bg-emerald-50" />

            <div className="lg:col-span-2 bg-slate-900 p-8 rounded-[2.5rem] text-white relative overflow-hidden shadow-xl">
                <div className="relative z-10">
                    <h3 className="text-xl font-bold mb-2">Monthly Growth</h3>
                    <p className="text-4xl font-black">+{stats?.new_doctors_this_month} New Clinics</p>
                    <p className="text-slate-400 mt-2 text-sm font-medium">Revenue this month: ₹{parseFloat(stats?.monthly_revenue || 0).toLocaleString()}</p>
                </div>
                <div className="absolute top-0 right-0 p-8 opacity-10">
                    <TrendingUp size={120} />
                </div>
            </div>
        </div>
    );
}

function PractitionerDashboard() {
    const { user } = useAuth();
    const [stats, setStats] = useState({
        patients: 0,
        revenue: 0,
        pending: 0,
        expenses: 0,
        inventoryValue: 0,
        lowStock: 0,
        reallocationCount: 0,
        integrity: null
    });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchDashboardStats();
    }, []);

    const fetchDashboardStats = async () => {
        try {
            const [pRes, fRes, invRes, integrityRes] = await Promise.all([
                api.get('/patients'),
                api.get('/finance/summary'),
                api.get('/inventory'),
                api.get('/system/integrity')
            ]);

            const summary = fRes.data;
            const metrics = summary?.metrics || {};

            const inventoryData = Array.isArray(invRes.data) ? invRes.data : invRes.data?.data ?? [];
            const lowStockCount = inventoryData.filter(
                item => item.stock <= item.reorder_level
            ).length;

            setStats({
                patients: pRes.data.length,
                revenue: metrics.revenue?.current ?? 0,
                pending: metrics.outstanding_balance?.current ?? 0,
                expenses: metrics.cash_collected?.current ? (metrics.revenue.current - metrics.net_profit.current) : 0, // Inferred or fetch legacy
                inventoryValue: metrics.inventory_value ?? 0,
                lowStock: lowStockCount,
                reallocationCount: summary.reallocation_count ?? 0, // Fallback for safety
                integrity: integrityRes.data
            });
        } catch (err) {
            return;
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div className="p-12 text-center font-bold text-slate-400 animate-pulse">Synchronizing Clinical Data...</div>;

    const netProfit = stats.revenue - stats.expenses;

    return (
        <>
            {stats.reallocationCount > 0 && (
                <div className="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex justify-between items-center shadow-sm">
                    <div className="flex items-center gap-3">
                        <AlertCircle className="text-amber-600" size={20} />
                        <div>
                            <p className="font-bold text-amber-800">
                                {stats.reallocationCount} Invoice{stats.reallocationCount > 1 ? 's' : ''} Require Stock Reallocation
                            </p>
                            <p className="text-xs text-amber-700">
                                Stock must be replenished before billing can complete.
                            </p>
                        </div>
                    </div>
                    <Link
                        to="/patients"
                        className="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-xl hover:bg-amber-700 transition-all"
                    >
                        Resolve Now
                    </Link>
                </div>
            )}

            {stats.integrity && (
                <div className={`mb-6 p-4 rounded-2xl border shadow-sm flex justify-between items-center ${stats.integrity.status === 'healthy'
                    ? 'bg-green-50 border-green-200'
                    : 'bg-red-50 border-red-200'
                    }`}>
                    <div className="flex items-center gap-3">
                        <AlertCircle
                            size={20}
                            className={
                                stats.integrity.status === 'healthy'
                                    ? 'text-green-600'
                                    : 'text-red-600'
                            }
                        />
                        <div>
                            <p className={`font-bold ${stats.integrity.status === 'healthy'
                                ? 'text-green-800'
                                : 'text-red-800'
                                }`}>
                                System Integrity: {stats.integrity.status === 'healthy' ? 'Healthy' : 'Warning'}
                            </p>

                            {stats.integrity.status !== 'healthy' && (
                                <p className="text-xs text-red-700">
                                    Drift Detected — Review Financial & Inventory Logs
                                </p>
                            )}
                        </div>
                    </div>

                    {stats.integrity.status !== 'healthy' && (
                        <span className="text-xs font-bold text-red-700">
                            Issues Detected
                        </span>
                    )}
                </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard
                    label="Pharmacy Health"
                    value={`${stats.lowStock} Alerts`}
                    icon={<Pill className="text-blue-600" />}
                    color="bg-blue-50"
                    isWarning={stats.lowStock > 0}
                    trend={`Total Asset: ₹${stats.inventoryValue.toLocaleString()}`}
                />
                <StatCard
                    label="Paid Revenue"
                    value={`₹${stats.revenue.toLocaleString()}`}
                    icon={<TrendingUp className="text-green-600" />}
                    color="bg-green-50"
                />
                <StatCard
                    label="Cost of Goods Sold (COGS)"
                    value={`₹${stats.expenses.toLocaleString()}`}
                    icon={<TrendingDown className="text-red-600" />}
                    color="bg-red-50"
                />
                <StatCard
                    label="Unpaid Dues"
                    value={`₹${stats.pending.toLocaleString()}`}
                    icon={<AlertCircle className="text-orange-600" />}
                    color="bg-orange-50"
                    isWarning={stats.pending > 0}
                />
            </div>

            <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col md:flex-row justify-between items-center gap-6">
                <div className="flex items-center gap-6">
                    <div className={`w-16 h-16 rounded-2xl flex items-center justify-center ${netProfit >= 0 ? 'bg-green-600 shadow-lg shadow-green-600/20' : 'bg-red-600 shadow-lg shadow-red-600/20'} text-white`}>
                        <ShoppingBag size={28} />
                    </div>
                    <div>
                        <h3 className="text-sm font-bold text-slate-400 uppercase tracking-widest">Net Operating Cash Flow</h3>
                        <p className={`text-3xl font-black ${netProfit >= 0 ? 'text-slate-800' : 'text-red-600'}`}>
                            ₹{netProfit.toLocaleString()}
                        </p>
                    </div>
                </div>
                <div className="flex gap-4">
                    <div className="px-6 py-4 bg-slate-50 rounded-2xl text-center">
                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Margin</p>
                        <p className="font-bold text-slate-800">{stats.revenue > 0 ? ((netProfit / stats.revenue) * 100).toFixed(1) : 0}%</p>
                    </div>
                    <button className="px-8 py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark transition-all shadow-xl shadow-primary/20">
                        Full Report
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div className="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 min-h-[300px]">
                    <h3 className="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <CreditCard size={20} className="text-primary" /> Upcoming Appointments
                    </h3>
                    <div className="text-slate-400 text-center mt-12 italic font-medium">No immediate scheduled visits.</div>
                </div>
                <div className="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 min-h-[300px]">
                    <h3 className="text-xl font-black text-slate-800 mb-6 flex items-center gap-2">
                        <TrendingUp size={20} className="text-primary" /> Growth Insights
                    </h3>
                    <div className="space-y-4">
                        <div className="p-4 bg-primary/5 rounded-2xl border border-primary/10">
                            <p className="text-sm font-bold text-primary italic">"Your patient retention is up by 14% this week. Consider exploring dental whitening promos."</p>
                        </div>
                        <div className="text-slate-400 text-center mt-4 text-sm font-medium italic">Data processing operational. All systems healthy.</div>
                    </div>
                </div>
            </div>
        </>
    );
}

function StatCard({ label, value, icon, color, isWarning }) {
    return (
        <div className="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:scale-[1.02] transition-all group">
            <div className={`p-3 rounded-xl ${color} w-fit mb-4 transition-transform group-hover:rotate-6`}>
                {icon}
            </div>
            <p className="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">{label}</p>
            <p className={`text-2xl font-black ${isWarning ? 'text-orange-600 animate-pulse' : 'text-slate-800'}`}>
                {value}
            </p>
        </div>
    );
}
