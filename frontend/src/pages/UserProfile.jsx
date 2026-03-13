import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../api/axios';
import toast from 'react-hot-toast';
import { User, Lock, Save, ArrowLeft, Trash2, RefreshCcw, TriangleAlert, History } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

export default function UserProfile() {
    const { user, refreshUser } = useAuth();
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        name: user?.name || '',
        password: ''
    });

    // Recycle Bin State
    const [deletedDocs, setDeletedDocs] = useState([]);
    const [loadingBin, setLoadingBin] = useState(false);

    // Fetch deleted doctors if super_admin
    useEffect(() => {
        if (user?.role === 'super_admin') {
            fetchRecycleBin();
        }
    }, [user]);

    const fetchRecycleBin = async () => {
        setLoadingBin(true);
        try {
            const res = await api.get('/admin/recycle-bin/doctors');
            setDeletedDocs(res.data);
        } catch (err) {
            toast.error(err.response?.data?.message || 'Unexpected error occurred');
        } finally {
            setLoadingBin(false);
        }
    };

    const handleRestore = async (id) => {
        if (!confirm('Restore this doctor and all their data?')) return;
        try {
            await api.post(`/admin/delete/doctor/${id}/restore`);
            toast.success('Doctor restored successfully');
            fetchRecycleBin();
        } catch (err) {
            toast.error('Failed to restore doctor');
        }
    };

    const handleForceDelete = async (id) => {
        if (!confirm('PERMANENTLY DELETE doctor? This action cannot be undone.')) return;
        try {
            await api.delete(`/admin/delete/doctor/${id}/force`);
            toast.success('Doctor permanently deleted');
            fetchRecycleBin();
        } catch (err) {
            toast.error('Failed to delete doctor');
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const res = await api.patch('/settings/profile', formData);
            toast.success('Profile updated successfully');
            await refreshUser(); // Reload user context to reflect name change
            setFormData(prev => ({ ...prev, password: '' })); // Clear password field
        } catch (err) {
            toast.error('Failed to update profile');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto">
            <button onClick={() => navigate(-1)} className="flex items-center text-slate-400 hover:text-slate-600 mb-6 font-medium transition-colors">
                <ArrowLeft size={16} className="mr-2" /> Back
            </button>

            <div className="bg-white rounded-[2rem] p-10 border border-slate-100 shadow-xl shadow-slate-200/40">
                <div className="flex items-center gap-6 mb-10 pb-8 border-b border-slate-50">
                    <div className="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center text-primary font-black text-2xl">
                        {user?.name?.charAt(0)}
                    </div>
                    <div>
                        <h1 className="text-3xl font-black text-slate-800 tracking-tight">My Profile</h1>
                        <p className="text-slate-500 font-medium">{user?.email}</p>
                        <p className="text-xs font-bold text-primary uppercase mt-1 tracking-widest bg-primary/5 inline-block px-3 py-1 rounded-lg">
                            {user?.role?.replace('_', ' ')}
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-black text-slate-700 mb-2">Full Name</label>
                        <div className="relative">
                            <User className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                            <input
                                type="text"
                                required
                                value={formData.name}
                                onChange={e => setFormData({ ...formData, name: e.target.value })}
                                className="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-transparent focus:bg-white focus:border-primary/20 rounded-2xl outline-none font-bold text-slate-700 transition-all placeholder:font-medium"
                                placeholder="Your Name"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-black text-slate-700 mb-2">New Password <span className="text-slate-400 font-medium text-xs">(Optional)</span></label>
                        <div className="relative">
                            <Lock className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                            <input
                                type="password"
                                minLength={6}
                                value={formData.password}
                                onChange={e => setFormData({ ...formData, password: e.target.value })}
                                className="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-transparent focus:bg-white focus:border-primary/20 rounded-2xl outline-none font-bold text-slate-700 transition-all placeholder:font-medium"
                                placeholder="Enter only to change"
                            />
                        </div>
                    </div>

                    <div className="pt-6">
                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50"
                        >
                            {loading ? 'Saving...' : <><Save size={20} /> Update Profile</>}
                        </button>
                    </div>
                </form>
            </div>

            {/* Recycle Bin Section (Super Admin Only) */}
            {user?.role === 'super_admin' && (
                <div className="mt-12 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-100">
                    <div className="flex items-center gap-3 mb-6 px-4">
                        <div className="p-2 bg-red-50 text-red-600 rounded-lg">
                            <Trash2 size={24} />
                        </div>
                        <div>
                            <h2 className="text-2xl font-black text-slate-800">Recycle Bin</h2>
                            <p className="text-slate-500 font-medium text-sm">Manage deleted doctors. Restoring brings back all related data.</p>
                        </div>
                    </div>

                    <div className="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
                        {loadingBin ? (
                            <div className="p-12 text-center text-slate-400 font-medium">Loading deleted records...</div>
                        ) : deletedDocs.length === 0 ? (
                            <div className="p-12 text-center flex flex-col items-center gap-4">
                                <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                                    <History size={32} />
                                </div>
                                <p className="text-slate-400 font-bold">Recycle Bin is empty</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left">
                                    <thead className="bg-slate-50 border-b border-slate-100">
                                        <tr>
                                            <th className="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-wider">Doctor Details</th>
                                            <th className="px-8 py-5 text-xs font-black text-slate-400 uppercase tracking-wider">Deleted On</th>
                                            <th className="px-8 py-5 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-50">
                                        {deletedDocs.map(doc => (
                                            <tr key={doc.id} className="hover:bg-slate-50/50 transition-colors">
                                                <td className="px-8 py-6">
                                                    <p className="font-bold text-slate-800">{doc.name}</p>
                                                    <div className="flex items-center gap-2 mt-1">
                                                        <span className="text-xs font-medium text-slate-500">{doc.email}</span>
                                                        <span className="text-[10px] font-black uppercase bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
                                                            {doc.specialty?.name || 'No Spec'}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="px-8 py-6">
                                                    <p className="text-sm font-bold text-slate-600">
                                                        {new Date(doc.deleted_at).toLocaleDateString()}
                                                    </p>
                                                    <p className="text-xs text-slate-400 font-medium">
                                                        {new Date(doc.deleted_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                    </p>
                                                </td>
                                                <td className="px-8 py-6 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <button
                                                            onClick={() => handleRestore(doc.id)}
                                                            className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg flex items-center gap-2 transition-colors tooltip"
                                                            title="Restore Doctor"
                                                        >
                                                            <RefreshCcw size={18} />
                                                        </button>
                                                        <button
                                                            onClick={() => handleForceDelete(doc.id)}
                                                            className="p-2 text-red-500 hover:bg-red-50 rounded-lg flex items-center gap-2 transition-colors tooltip"
                                                            title="Permanently Delete"
                                                        >
                                                            <Trash2 size={18} />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
