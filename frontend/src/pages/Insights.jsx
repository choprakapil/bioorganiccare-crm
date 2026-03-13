import { useState, useEffect } from 'react';
import api from '../api/axios';
import { useAuth } from '../context/AuthContext';
import toast from 'react-hot-toast';
import { handleApiError } from '../utils/errorHandler';
import { Sparkles, TrendingUp, Package, Activity, Lock, RefreshCw, AlertCircle, Info } from 'lucide-react';

export default function Insights() {
    const { isPro } = useAuth();
    const [insights, setInsights] = useState([]);
    const [loading, setLoading] = useState(true);
    const [timestamp, setTimestamp] = useState('');

    useEffect(() => {
        if (isPro) {
            fetchInsights();
        } else {
            setLoading(false);
        }
    }, [isPro]);

    const fetchInsights = async () => {
        setLoading(true);
        try {
            const res = await api.get('/insights');
            setInsights(res.data.insights);
            setTimestamp(res.data.generated_at);
        } catch (err) {
            if (err.response?.status === 403) {
                toast.error("Upgrade to Pro to access AI Insights.");
                return;
            } else {
                toast.error('Failed to analyze clinical data');
            }
        } finally {
            setLoading(false);
        }
    };

    if (!isPro) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
                <div className="w-24 h-24 bg-primary/10 rounded-3xl flex items-center justify-center text-primary mb-8 animate-bounce">
                    <Lock size={40} />
                </div>
                <h1 className="text-4xl font-black text-slate-800 mb-4 tracking-tight">Growth Insights AI</h1>
                <p className="text-slate-500 max-w-md mb-10 font-medium">
                    Our proprietary intelligence engine analyzes your clinic's financial and clinical data to provide actionable growth strategies.
                </p>
                <div className="bg-white p-8 rounded-[3rem] shadow-2xl border border-slate-100 max-w-lg w-full">
                    <h3 className="text-xl font-black text-slate-800 mb-6">Unlock Pro Plan</h3>
                    <ul className="text-left space-y-4 mb-8">
                        <li className="flex items-center gap-3 text-slate-600 font-bold">
                            <TrendingUp size={20} className="text-green-500" /> Revenue Optimization Strategies
                        </li>
                        <li className="flex items-center gap-3 text-slate-600 font-bold">
                            <Package size={20} className="text-blue-500" /> Inventory Efficiency Patterns
                        </li>
                        <li className="flex items-center gap-3 text-slate-600 font-bold">
                            <Activity size={20} className="text-primary" /> Patient Re-engagement Scoring
                        </li>
                    </ul>
                    <button className="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark transition-all shadow-xl shadow-primary/20">
                        Upgrade Now
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-8">
            <header className="flex justify-between items-end">
                <div>
                    <div className="flex items-center gap-2 mb-2">
                        <span className="bg-primary/10 text-primary text-[10px] font-black px-2 py-1 rounded-full border border-primary/20">AURA INTELLIGENCE v1.0</span>
                    </div>
                    <h1 className="text-4xl font-black text-slate-800 tracking-tight">Growth Insights AI</h1>
                    <p className="text-slate-500 font-medium mt-1 italic">
                        Real-time heuristic analysis of your clinic's operational performance.
                    </p>
                </div>
                <button
                    onClick={fetchInsights}
                    className="bg-white text-slate-700 px-6 py-3 rounded-2xl font-black flex items-center gap-2 hover:bg-slate-50 border border-slate-200 transition-all shadow-sm"
                >
                    <RefreshCw size={18} className={loading ? 'animate-spin' : ''} />
                    Re-run Analysis
                </button>
            </header>

            {loading ? (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {[1, 2, 3, 4].map(i => (
                        <div key={i} className="bg-white p-8 rounded-[2.5rem] border border-slate-100 animate-pulse h-48"></div>
                    ))}
                </div>
            ) : insights.length === 0 ? (
                <div className="bg-white p-12 rounded-[3rem] border border-slate-100 text-center">
                    <div className="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <Info size={32} />
                    </div>
                    <h3 className="text-xl font-black text-slate-800 mb-2">Insufficient Data for Analysis</h3>
                    <p className="text-slate-500 max-w-md mx-auto">Record more treatments and invoices to help the AURA engine identify patterns in your clinical workflow.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {insights.map((insight, idx) => (
                        <InsightCard key={idx} insight={insight} />
                    ))}
                </div>
            )}

            <div className="bg-slate-900 p-8 rounded-[3rem] text-white flex flex-col md:flex-row items-center gap-6 justify-between">
                <div>
                    <h4 className="flex items-center gap-2 font-black text-lg mb-2">
                        <Sparkles size={20} className="text-primary" /> AURA Decision Support
                    </h4>
                    <p className="text-slate-400 text-sm font-medium">Insights generated last on {new Date(timestamp).toLocaleString()}. Data is processed locally for privacy.</p>
                </div>
                <div className="text-[10px] font-black text-slate-500 border border-slate-800 px-3 py-1 rounded-full uppercase tracking-tighter">
                    No Medical Advice Provided.
                </div>
            </div>
        </div>
    );
}

function InsightCard({ insight }) {
    const getIcon = () => {
        switch (insight.type) {
            case 'revenue': return <TrendingUp size={24} />;
            case 'inventory': return <Package size={24} />;
            case 'efficiency': return <Activity size={24} />;
            default: return <Sparkles size={24} />;
        }
    };

    const getColor = () => {
        switch (insight.priority) {
            case 'high': return 'border-red-500 bg-red-50/50 text-red-600';
            case 'medium': return 'border-orange-500 bg-orange-50/50 text-orange-600';
            default: return 'border-primary bg-primary/5 text-primary';
        }
    };

    return (
        <div className="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl hover:translate-y-[-4px] transition-all group">
            <div className="flex justify-between items-start mb-6">
                <div className={`p-4 rounded-2xl ${getColor()} transition-transform group-hover:rotate-6 shadow-sm`}>
                    {getIcon()}
                </div>
                <span className={`text-[10px] font-black px-2 py-1 rounded-full uppercase ${insight.priority === 'high' ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-500'}`}>
                    {insight.priority} Priority
                </span>
            </div>
            <h3 className="text-xl font-black text-slate-800 mb-3">{insight.title}</h3>
            <p className="text-slate-500 font-medium leading-relaxed mb-6">
                {insight.description}
            </p>
            <div className="flex items-center gap-2 text-primary font-black text-xs uppercase tracking-widest cursor-pointer hover:underline">
                View related report <TrendingUp size={12} />
            </div>
        </div>
    );
}
