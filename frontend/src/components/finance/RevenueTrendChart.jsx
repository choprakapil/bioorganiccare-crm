import React, { useState, useEffect, useMemo } from 'react';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer
} from 'recharts';
import { TrendingUp, AlertCircle } from 'lucide-react';
import api from '../../api/axios';

const RevenueTrendChart = () => {
    const [days, setDays] = useState(90);
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchTrend = async () => {
            setLoading(true);
            try {
                const response = await api.get(`/finance/revenue-trend?days=${days}`);
                setData(response.data);
                setError(null);
            } catch (err) {
                console.error('Trend fetch error:', err);
                setError('Failed to load revenue trend.');
            } finally {
                setLoading(false);
            }
        };
        fetchTrend();
    }, [days]);

    const formattedData = useMemo(() => {
        return data?.points ?? [];
    }, [data?.points]);

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0,
        }).format(value);
    };

    const CustomTooltip = ({ active, payload, label }) => {
        if (active && payload && payload.length) {
            const date = new Date(label).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
            return (
                <div className="bg-white p-3 border border-slate-200 shadow-xl rounded-lg">
                    <p className="text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">{date}</p>
                    <p className="text-sm font-black text-slate-900">{formatCurrency(payload[0].value)}</p>
                </div>
            );
        }
        return null;
    };

    return (
        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full h-[400px]">
            <div className="flex items-center justify-between mb-8">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-indigo-50 rounded-lg">
                        <TrendingUp className="w-4 h-4 text-indigo-600" />
                    </div>
                    <div>
                        <h3 className="text-sm font-bold text-slate-900">Revenue Trend</h3>
                        <p className="text-[11px] text-slate-500 font-medium">Daily collection trajectory</p>
                    </div>
                </div>

                <div className="flex bg-slate-50 p-1 rounded-lg border border-slate-100">
                    {[30, 90].map((d) => (
                        <button
                            key={d}
                            onClick={() => setDays(d)}
                            className={`px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md transition-all ${days === d
                                ? 'bg-white text-indigo-600 shadow-sm border border-slate-200'
                                : 'text-slate-400 hover:text-slate-600'
                                }`}
                        >
                            {d}D
                        </button>
                    ))}
                </div>
            </div>

            <div className="flex-1 min-h-[300px] relative">
                {loading && !data && (
                    <div className="absolute inset-0 z-10 flex items-center justify-center">
                        <div className="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                )}

                {error ? (
                    <div className="h-full flex flex-col items-center justify-center text-slate-400 gap-2">
                        <AlertCircle className="w-8 h-8 opacity-20" />
                        <span className="text-xs font-medium">{error}</span>
                    </div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={formattedData} margin={{ top: 10, right: 10, left: -10, bottom: 0 }}>
                            <defs>
                                <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="5%" stopColor="#4f46e5" stopOpacity={0.1} />
                                    <stop offset="95%" stopColor="#4f46e5" stopOpacity={0} />
                                </linearGradient>
                            </defs>
                            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                            <XAxis
                                dataKey="date"
                                tickFormatter={(value) =>
                                    new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
                                }
                                tick={{ fontSize: 10, fill: '#94a3b8', fontWeight: 500 }}
                                axisLine={false}
                                tickLine={false}
                                minTickGap={30}
                            />
                            <YAxis
                                tickFormatter={(value) => `${Math.round(value / 1000)}k`}
                                tick={{ fontSize: 10, fill: '#94a3b8', fontWeight: 500 }}
                                axisLine={false}
                                tickLine={false}
                                domain={['auto', 'auto']}
                            />
                            <Tooltip content={<CustomTooltip />} />
                            <Area
                                type="monotone"
                                dataKey="revenue"
                                stroke="#4f46e5"
                                strokeWidth={2}
                                fillOpacity={1}
                                fill="url(#colorRevenue)"
                                dot={false}
                                activeDot={{ r: 4, strokeWidth: 0, fill: '#4f46e5' }}
                                animationDuration={1000}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                )}
            </div>
        </div>
    );
};

export default RevenueTrendChart;
