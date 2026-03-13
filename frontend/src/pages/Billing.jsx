import { useState, useEffect } from 'react';
import { useSubscription } from '../context/SubscriptionContext';
import SubscriptionCard from '../components/subscription/SubscriptionCard';
import api from '../api/axios';

// ─── Usage bar with percentage display ────────────────────────────────────────
const UsageBar = ({ label, used, limit, unlimited, percent }) => {
    const pct = unlimited ? 0 : (percent ?? 0);
    const barColor = pct >= 90 ? 'bg-red-500' : pct >= 70 ? 'bg-yellow-500' : 'bg-emerald-500';
    return (
        <div>
            <div className="flex justify-between text-sm mb-1">
                <span className="font-medium text-gray-700">{label}</span>
                <span className="text-gray-500">
                    {unlimited ? `${used} / ∞` : `${used} / ${limit}`}
                </span>
            </div>
            <div className="w-full bg-gray-100 rounded-full h-2.5">
                <div
                    className={`${barColor} h-2.5 rounded-full transition-all duration-500`}
                    style={{ width: unlimited ? '4%' : `${Math.min(pct, 100)}%` }}
                />
            </div>
            {!unlimited && (
                <p className="text-xs text-gray-400 mt-0.5">
                    {percent}% used · {Math.max(0, limit - used)} remaining
                </p>
            )}
        </div>
    );
};

// ─── Status badge ─────────────────────────────────────────────────────────────
const StatusBadge = ({ status }) => {
    const map = {
        paid: 'bg-emerald-100 text-emerald-700',
        partial: 'bg-yellow-100 text-yellow-700',
        unpaid: 'bg-red-100 text-red-700',
        cancelled: 'bg-gray-100 text-gray-500',
    };
    return (
        <span className={`px-2 py-0.5 rounded-full text-xs font-medium capitalize ${map[status?.toLowerCase()] ?? 'bg-gray-100 text-gray-500'}`}>
            {status}
        </span>
    );
};

// ─── Main Billing Component ───────────────────────────────────────────────────
const Billing = () => {
    const { subscription, loading: subLoading } = useSubscription();

    const [usage, setUsage] = useState(null);
    const [invoices, setInvoices] = useState([]);
    const [finance, setFinance] = useState(null);
    const [loadingUsage, setLoadingUsage] = useState(true);
    const [loadingInvoices, setLoadingInvoices] = useState(true);
    const [loadingFinance, setLoadingFinance] = useState(true);
    const [activeTab, setActiveTab] = useState('overview');

    // Fetch usage metrics
    useEffect(() => {
        api.get('/subscription/usage')
            .then(r => setUsage(r.data))
            .catch(() => setUsage(null))
            .finally(() => setLoadingUsage(false));
    }, []);

    // Fetch recent invoices
    useEffect(() => {
        api.get('/invoices?per_page=10')
            .then(r => setInvoices(r.data?.data ?? r.data ?? []))
            .catch(() => setInvoices([]))
            .finally(() => setLoadingInvoices(false));
    }, []);

    // Fetch 90-day finance summary
    useEffect(() => {
        api.get('/finance/summary')
            .then(r => setFinance(r.data))
            .catch(() => setFinance(null))
            .finally(() => setLoadingFinance(false));
    }, []);

    if (subLoading) {
        return (
            <div className="flex items-center justify-center p-20 text-gray-400">
                <p>Loading Billing Information...</p>
            </div>
        );
    }

    const tabs = [
        { key: 'overview', label: 'Overview' },
        { key: 'invoices', label: 'Invoice History' },
        { key: 'financials', label: 'Financials' },
    ];

    const fmt = (n) => `₹${Number(n ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

    return (
        <div className="max-w-5xl mx-auto py-10 px-4 space-y-8">

            {/* Header */}
            <div>
                <h1 className="text-3xl font-bold text-gray-900">Billing &amp; Subscription</h1>
                <p className="text-gray-500 mt-1">Manage your clinic's plan, usage, invoices, and financial summary.</p>
            </div>

            {/* Tabs */}
            <div className="border-b border-gray-200">
                <nav className="-mb-px flex gap-6">
                    {tabs.map(t => (
                        <button
                            key={t.key}
                            onClick={() => setActiveTab(t.key)}
                            className={`pb-3 text-sm font-medium border-b-2 transition-colors ${activeTab === t.key
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                                }`}
                        >
                            {t.label}
                        </button>
                    ))}
                </nav>
            </div>

            {/* ── TAB: Overview ────────────────────────────────────────────── */}
            {activeTab === 'overview' && (
                <div className="space-y-8">
                    {/* Subscription Card */}
                    <SubscriptionCard />

                    {/* Plan Details */}
                    {subscription && (
                        <div className="bg-white rounded-xl shadow-sm border p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Plan Details</h3>
                            <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Active Plan</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{subscription?.plan?.name ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Monthly Appointments</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        {subscription?.plan?.max_appointments_monthly === -1
                                            ? 'Unlimited'
                                            : `${subscription?.plan?.max_appointments_monthly} / month`}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Billing Interval</dt>
                                    <dd className="mt-1 text-sm text-gray-900 capitalize">{subscription.lifecycle?.interval ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-sm font-medium text-gray-500">Next Renewal</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{subscription.lifecycle?.renews_at ?? 'Lifetime'}</dd>
                                </div>
                            </dl>
                        </div>
                    )}

                    {/* Usage Metrics */}
                    <div className="bg-white rounded-xl shadow-sm border p-6">
                        <h3 className="text-lg font-semibold text-gray-900 mb-4">Current Cycle Usage</h3>
                        {loadingUsage ? (
                            <p className="text-sm text-gray-400">Loading usage...</p>
                        ) : usage ? (
                            <div className="space-y-5">
                                <UsageBar label="Patients"     {...usage.usage.patients} />
                                <UsageBar label="Appointments" {...usage.usage.appointments} />
                                <UsageBar label="Staff"        {...usage.usage.staff} />
                            </div>
                        ) : (
                            <p className="text-sm text-gray-400">Usage data unavailable.</p>
                        )}
                    </div>
                </div>
            )}

            {/* ── TAB: Invoice History ─────────────────────────────────────── */}
            {activeTab === 'invoices' && (
                <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <div className="px-6 py-4 border-b">
                        <h3 className="text-lg font-semibold text-gray-900">Recent Invoices</h3>
                        <p className="text-sm text-gray-500">Last 10 invoices across all patients</p>
                    </div>
                    {loadingInvoices ? (
                        <div className="p-8 text-center text-gray-400 text-sm">Loading invoices...</div>
                    ) : invoices.length === 0 ? (
                        <div className="p-8 text-center text-gray-400 text-sm">No invoices yet.</div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-100 text-sm">
                                <thead className="bg-gray-50">
                                    <tr>
                                        {['Invoice #', 'Patient', 'Total', 'Paid', 'Balance', 'Status', 'Date'].map(h => (
                                            <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-50">
                                    {invoices.map(inv => (
                                        <tr key={inv.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-4 py-3 font-mono text-gray-700">#{inv.id}</td>
                                            <td className="px-4 py-3 text-gray-700">{inv.patient?.name ?? '—'}</td>
                                            <td className="px-4 py-3 text-gray-900 font-medium">{fmt(inv.total_amount)}</td>
                                            <td className="px-4 py-3 text-emerald-600">{fmt(inv.paid_amount)}</td>
                                            <td className="px-4 py-3 text-red-600">{fmt(inv.balance_due)}</td>
                                            <td className="px-4 py-3"><StatusBadge status={inv.status} /></td>
                                            <td className="px-4 py-3 text-gray-400">{new Date(inv.created_at).toLocaleDateString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            )}

            {/* ── TAB: Financials ──────────────────────────────────────────── */}
            {activeTab === 'financials' && (
                <div className="space-y-6">
                    {!finance?.metrics ? (
                        <div className="p-8 text-center text-gray-400 text-sm">Initializing financial metrics...</div>
                    ) : (
                        <>
                            <div className="bg-blue-50 border border-blue-100 rounded-xl px-5 py-3 text-sm text-blue-600">
                                Showing data from <strong>{finance.window?.start}</strong> to <strong>{finance.window?.end}</strong> ({finance.window?.days} days)
                            </div>
                            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                                {[
                                    { label: 'Accrual Revenue', value: finance.metrics.revenue?.current, color: 'text-blue-700' },
                                    { label: 'Cash Collected', value: finance.metrics.cash_collected?.current, color: 'text-emerald-700' },
                                    { label: 'Pending Dues', value: finance.metrics.outstanding_balance?.current, color: 'text-orange-600' },
                                    { label: 'Net Profit', value: finance.metrics.net_profit?.current, color: 'text-emerald-800' },
                                    { label: 'Inventory Value', value: finance.metrics.inventory_value, color: 'text-gray-700' },
                                ].map(({ label, value, color }) => (
                                    <div key={label} className="bg-white rounded-xl shadow-sm border p-4">
                                        <p className="text-xs text-gray-500 mb-1">{label}</p>
                                        <p className={`text-xl font-bold ${color}`}>{fmt(value)}</p>
                                    </div>
                                ))}
                            </div>
                            {finance.reallocation_count > 0 && (
                                <div className="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 text-sm">
                                    ⚠️ <strong>{finance.reallocation_count}</strong> invoice(s) require stock reallocation review.
                                </div>
                            )}
                        </>
                    )}
                </div>
            )}
        </div>
    );
};

export default Billing;
