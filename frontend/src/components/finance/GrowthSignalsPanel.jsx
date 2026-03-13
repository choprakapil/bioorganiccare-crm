import React, { useState, useEffect } from 'react';
import { Sparkles, AlertTriangle, Info, CheckCircle2, Loader2, Clock } from 'lucide-react';
import api from '../../api/axios';
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

function cn(...inputs) {
    return twMerge(clsx(inputs));
}

const GrowthSignalsPanel = () => {
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchInsights = async () => {
            try {
                const response = await api.get('/growth/insights');
                // Harden fetch logic with safe fallbacks
                setData(response.data ?? null);
            } catch (err) {
                console.error('Insights fetch error:', err);
                setError('Unable to load growth signals.');
            } finally {
                setLoading(false);
            }
        };
        fetchInsights();
    }, []);

    const insights = data?.insights ?? [];
    const generatedAt = data?.generated_at;

    const formatTimestamp = (dateStr) => {
        if (!dateStr) return null;
        try {
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            }).format(new Date(dateStr));
        } catch (e) {
            return null;
        }
    };

    const priorityStyles = {
        high: {
            container: "bg-rose-50 border-rose-100",
            icon: <AlertTriangle className="w-4 h-4 text-rose-600" />,
            title: "text-rose-900",
            description: "text-rose-700"
        },
        medium: {
            container: "bg-amber-50 border-amber-100",
            icon: <Info className="w-4 h-4 text-amber-600" />,
            title: "text-amber-900",
            description: "text-amber-700"
        },
        low: {
            container: "bg-slate-50 border-slate-200",
            icon: <CheckCircle2 className="w-4 h-4 text-slate-500" />,
            title: "text-slate-900",
            description: "text-slate-600"
        }
    };

    return (
        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px] overflow-hidden">
            <div className="flex items-start justify-between mb-6">
                <div className="flex items-center gap-3">
                    <div className="p-2 bg-primary/10 rounded-lg text-primary">
                        <Sparkles className="w-4 h-4" />
                    </div>
                    <div>
                        <h3 className="text-sm font-bold text-slate-900">Growth Signals</h3>
                        <p className="text-[11px] text-slate-500 font-medium tracking-tight leading-tight">AI-generated operational insights</p>
                    </div>
                </div>
                {generatedAt && (
                    <div className="flex items-center gap-1.5 text-[10px] text-slate-400 font-medium whitespace-nowrap pt-1">
                        <Clock className="w-3 h-3" />
                        <span>Updated {formatTimestamp(generatedAt)}</span>
                    </div>
                )}
            </div>

            <div className="flex-1 overflow-y-auto pr-1 space-y-3 custom-scrollbar">
                {loading ? (
                    <div className="h-full flex flex-col items-center justify-center space-y-3">
                        <Loader2 className="w-6 h-6 text-slate-300 animate-spin" />
                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Analyzing Data...</p>
                    </div>
                ) : error ? (
                    <div className="h-full flex flex-col items-center justify-center space-y-2 opacity-60">
                        <AlertTriangle className="w-8 h-8 text-slate-400 stroke-1" />
                        <p className="text-xs text-slate-500 font-medium text-center">{error}</p>
                    </div>
                ) : insights.length === 0 ? (
                    <div className="h-full flex flex-col items-center justify-center space-y-4">
                        <div className="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center">
                            <CheckCircle2 className="w-6 h-6 text-emerald-500" />
                        </div>
                        <div className="text-center space-y-1">
                            <p className="text-sm font-bold text-slate-900 tracking-tight">All financial indicators stable</p>
                            <p className="text-[11px] text-slate-400 font-medium max-w-[180px] mx-auto leading-relaxed">
                                No risks or anomalies detected in this window.
                            </p>
                        </div>
                    </div>
                ) : (
                    insights.map((insight, idx) => {
                        const style = priorityStyles[insight.priority] || priorityStyles.low;
                        return (
                            <div
                                key={idx}
                                className={cn(
                                    "p-4 rounded-xl border transition-all duration-200 hover:-translate-y-0.5",
                                    style.container
                                )}
                            >
                                <div className="flex gap-3">
                                    <div className="mt-0.5 flex-shrink-0">{style.icon}</div>
                                    <div>
                                        <h4 className={cn("text-xs font-bold leading-none", style.title)}>
                                            {insight.title}
                                        </h4>
                                        <p className={cn("text-[11px] mt-1.5 leading-relaxed font-medium", style.description)}>
                                            {insight.description}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </div>
    );
};

export default GrowthSignalsPanel;
