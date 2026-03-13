import { useState } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import { XCircle, RefreshCw, AlertTriangle, PlayCircle, Ban, Infinity } from 'lucide-react';

export default function SubscriptionControlModal({ doctor, onClose, onSuccess }) {
    const [loading, setLoading] = useState(false);
    const [action, setAction] = useState(null); // 'renew', 'restart', 'cancel', 'lifetime'

    if (!doctor) return null;

    const handleAction = async (type) => {
        if (type === 'cancel' || type === 'restart') {
            if (!confirm(`Are you sure you want to ${type.toUpperCase()} this subscription? This action cannot be easily undone.`)) {
                return;
            }
        }

        setLoading(true);
        setAction(type);

        try {
            await api.post(`/admin/subscriptions/${doctor.id}/${type}`);
            toast.success(`Subscription ${type} successful!`);
            onSuccess();
            onClose();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Action failed');
        } finally {
            setLoading(false);
            setAction(null);
        }
    };

    return (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div className="bg-white w-full max-w-lg rounded-[2.5rem] p-8 shadow-2xl animate-fade-in-up">

                {/* Header */}
                <div className="mb-8 flex justify-between items-center">
                    <div>
                        <h2 className="text-2xl font-black text-slate-800">Manage Subscription</h2>
                        <p className="text-slate-500 font-medium">Controls for Dr. {doctor.name}</p>
                    </div>
                    <button onClick={onClose} className="p-2 bg-slate-100 rounded-full hover:bg-slate-200 text-slate-500 transition-colors">
                        <XCircle size={24} />
                    </button>
                </div>

                {/* Status Card */}
                <div className="bg-slate-50 rounded-2xl p-6 mb-8 border border-slate-100">
                    <div className="flex justify-between items-center mb-2">
                        <span className="text-xs font-black text-slate-400 uppercase tracking-wider">Current Status</span>
                        <span className={`px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider
                            ${doctor.subscription_status === 'active' ? 'bg-green-100 text-green-700' :
                                doctor.subscription_status === 'past_due' ? 'bg-yellow-100 text-yellow-700' :
                                    doctor.subscription_status === 'lifetime' ? 'bg-purple-100 text-purple-700' :
                                        'bg-red-100 text-red-700'}`}>
                            {doctor.subscription_status?.replace('_', ' ') || 'Inactive'}
                        </span>
                    </div>
                    <div className="flex justify-between items-center">
                        <span className="text-xs font-black text-slate-400 uppercase tracking-wider">Next Renewal</span>
                        <span className="font-bold text-slate-800">
                            {doctor.subscription_renews_at ? new Date(doctor.subscription_renews_at).toLocaleDateString() : 'N/A'}
                        </span>
                    </div>
                </div>

                {/* Actions Grid */}
                <div className="space-y-4">

                    {/* Renew Button */}
                    <button
                        onClick={() => handleAction('renew')}
                        disabled={loading}
                        className="w-full p-4 bg-green-50 hover:bg-green-100 border border-green-100 rounded-2xl flex items-center gap-4 transition-all group disabled:opacity-50"
                    >
                        <div className="w-10 h-10 bg-green-200 text-green-700 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <RefreshCw size={20} className={loading && action === 'renew' ? 'animate-spin' : ''} />
                        </div>
                        <div className="text-left">
                            <h4 className="font-black text-green-900">Extend / Renew</h4>
                            <p className="text-xs text-green-700 font-medium">Add 1 cycle to current end date. Safe for early renewal.</p>
                        </div>
                    </button>

                    {/* Restart Button */}
                    <button
                        onClick={() => handleAction('restart')}
                        disabled={loading}
                        className="w-full p-4 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-2xl flex items-center gap-4 transition-all group disabled:opacity-50"
                    >
                        <div className="w-10 h-10 bg-blue-200 text-blue-700 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <PlayCircle size={20} className={loading && action === 'restart' ? 'animate-spin' : ''} />
                        </div>
                        <div className="text-left">
                            <h4 className="font-black text-blue-900">Restart Cycle</h4>
                            <p className="text-xs text-blue-700 font-medium">Reset dates to NOW. Useful for reactivation.</p>
                        </div>
                    </button>

                    {/* Lifetime Button */}
                    <button
                        onClick={() => handleAction('lifetime')}
                        disabled={loading}
                        className="w-full p-4 bg-purple-50 hover:bg-purple-100 border border-purple-100 rounded-2xl flex items-center gap-4 transition-all group disabled:opacity-50"
                    >
                        <div className="w-10 h-10 bg-purple-200 text-purple-700 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <Infinity size={20} className={loading && action === 'lifetime' ? 'animate-pulse' : ''} />
                        </div>
                        <div className="text-left">
                            <h4 className="font-black text-purple-900">Grant Lifetime Access</h4>
                            <p className="text-xs text-purple-700 font-medium">Bypass billing logic permanently.</p>
                        </div>
                    </button>

                    {/* Cancel Button */}
                    <button
                        onClick={() => handleAction('cancel')}
                        disabled={loading}
                        className="w-full p-4 bg-red-50 hover:bg-red-100 border border-red-100 rounded-2xl flex items-center gap-4 transition-all group disabled:opacity-50"
                    >
                        <div className="w-10 h-10 bg-red-200 text-red-700 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                            <Ban size={20} className={loading && action === 'cancel' ? 'animate-pulse' : ''} />
                        </div>
                        <div className="text-left">
                            <h4 className="font-black text-red-900">Cancel Subscription</h4>
                            <p className="text-xs text-red-700 font-medium">Block access immediately (status: cancelled).</p>
                        </div>
                    </button>

                </div>
            </div>
        </div>
    );
}
