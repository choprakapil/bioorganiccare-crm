import { useState, useEffect } from 'react';
import api from '../api/axios';
import { useAuth } from '../context/AuthContext';
import toast from 'react-hot-toast';
import { Settings as SettingsIcon, Palette, Building, ShieldCheck, CreditCard, ChevronRight, Upload } from 'lucide-react';
import StaffManagement from '../components/StaffManagement';

export default function Settings() {
    const { user, refreshUser } = useAuth();
    const [loading, setLoading] = useState(false);

    // Branding Form
    const [branding, setBranding] = useState({
        clinic_name: '',
        brand_color: '#4f46e5',
        brand_secondary_color: '#f8fafc'
    });

    useEffect(() => {
        if (user) {
            setBranding({
                clinic_name: user.clinic_name || '',
                brand_color: user.brand_color || '#4f46e5',
                brand_secondary_color: user.brand_secondary_color || '#f8fafc'
            });
        }
    }, [user]);

    const handleUpdateBranding = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await api.patch('/settings/branding', branding);
            await refreshUser();
            toast.success('Branding updated successfully');
        } catch (err) {
            toast.error('Failed to update branding');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="space-y-8">
            <header>
                <h1 className="text-4xl font-black text-slate-800 tracking-tight">Clinic Settings</h1>
                <p className="text-slate-500 font-medium mt-1">Manage your white-label branding, subscription limits, and security configuration.</p>
            </header>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Left Column: Sections */}
                <div className="lg:col-span-2 space-y-8">
                    {/* White-Label Branding */}
                    <section className="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="flex items-center gap-3 mb-8">
                            <div className="p-3 bg-primary/10 text-primary rounded-2xl">
                                <Palette size={24} />
                            </div>
                            <h2 className="text-2xl font-black text-slate-800">White-Label Branding</h2>
                        </div>

                        <form onSubmit={handleUpdateBranding} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2 tracking-widest">Clinic Name</label>
                                    <input
                                        type="text"
                                        value={branding.clinic_name}
                                        onChange={(e) => setBranding({ ...branding, clinic_name: e.target.value })}
                                        placeholder="e.g. Aura Dental Clinic"
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2 tracking-widest">Clinic Logo URL</label>
                                    <div className="flex gap-2">
                                        <input
                                            type="text"
                                            disabled
                                            placeholder="Logo upload coming soon..."
                                            className="flex-1 px-5 py-4 rounded-2xl border border-slate-200 bg-slate-50 font-medium text-slate-400"
                                        />
                                        <button type="button" className="p-4 bg-slate-100 text-slate-400 rounded-2xl cursor-not-allowed">
                                            <Upload size={20} />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2 tracking-widest">Primary Brand Color</label>
                                    <div className="flex items-center gap-4">
                                        <input
                                            type="color"
                                            value={branding.brand_color}
                                            onChange={(e) => setBranding({ ...branding, brand_color: e.target.value })}
                                            className="w-16 h-16 rounded-2xl border-none cursor-pointer p-0 bg-transparent overflow-hidden"
                                        />
                                        <input
                                            type="text"
                                            value={branding.brand_color}
                                            onChange={(e) => setBranding({ ...branding, brand_color: e.target.value })}
                                            className="flex-1 px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-mono font-bold"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2 tracking-widest">Preview Theme</label>
                                    <div className="h-16 rounded-2xl border border-slate-100 flex items-center px-4 gap-3 bg-slate-50">
                                        <div style={{ backgroundColor: branding.brand_color }} className="w-8 h-8 rounded-lg shadow-sm"></div>
                                        <span className="font-bold text-slate-800">{branding.clinic_name || 'Clinic Name'}</span>
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-end pt-4">
                                <button
                                    type="submit"
                                    disabled={loading}
                                    className="px-10 py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark shadow-2xl shadow-primary/30 transition-all disabled:opacity-50"
                                >
                                    {loading ? 'Saving...' : 'Apply Branding Assets'}
                                </button>
                            </div>
                        </form>
                    </section>

                    {/* Subscription & Limits */}
                    <section className="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm animate-in fade-in slide-in-from-bottom-4 duration-700">
                        <div className="flex items-center justify-between mb-8">
                            <div className="flex items-center gap-3">
                                <div className="p-3 bg-blue-50 text-blue-600 rounded-2xl">
                                    <ShieldCheck size={24} />
                                </div>
                                <h2 className="text-2xl font-black text-slate-800">Operational Limits</h2>
                            </div>
                            <span className="bg-green-100 text-green-600 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-green-200">Plan: {user?.plan?.name}</span>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <p className="text-xs font-black text-slate-400 uppercase mb-4 tracking-widest">Patient Capacity</p>
                                <div className="flex items-end gap-2">
                                    <span className="text-3xl font-black text-slate-800">{user?.plan?.max_patients === -1 ? '∞' : user?.plan?.max_patients}</span>
                                    <span className="text-slate-400 font-bold mb-1">Max Patients</span>
                                </div>
                                <div className="mt-4 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div className="h-full bg-primary" style={{ width: user?.plan?.max_patients === -1 ? '100%' : '60%' }}></div>
                                </div>
                            </div>
                            <div className="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <p className="text-xs font-black text-slate-400 uppercase mb-4 tracking-widest">Monthly Appointments</p>
                                <div className="flex items-end gap-2">
                                    <span className="text-3xl font-black text-slate-800">{user?.plan?.max_appointments_monthly === -1 ? '∞' : user?.plan?.max_appointments_monthly}</span>
                                    <span className="text-slate-400 font-bold mb-1">Per Month</span>
                                </div>
                                <div className="mt-4 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div className="h-full bg-blue-500" style={{ width: user?.plan?.max_appointments_monthly === -1 ? '100%' : '40%' }}></div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 p-6 bg-primary/5 rounded-[2rem] border border-primary/10 flex items-center justify-between">
                            <div>
                                <h4 className="font-bold text-slate-800">Need more capacity?</h4>
                                <p className="text-sm text-slate-500 font-medium">Upgrade to the Enterprise or Pro Unlimited plans for unrestricted clinical scaling.</p>
                            </div>
                            <button className="px-6 py-3 bg-white text-primary border border-primary/20 font-black rounded-xl hover:bg-primary hover:text-white transition-all">
                                View Plans
                            </button>
                        </div>
                    </section>

                    {/* Staff Management */}
                    <StaffManagement />
                </div>

                {/* Right Column: Mini Stats */}
                <div className="space-y-6">
                    <div className="bg-slate-900 p-8 rounded-[3rem] text-white shadow-xl">
                        <h3 className="text-xl font-black mb-6">Security Audit</h3>
                        <ul className="space-y-4">
                            <li className="flex items-center gap-3">
                                <div className="w-2 h-2 rounded-full bg-green-400"></div>
                                <span className="text-sm font-bold text-slate-300">Tenancy Isolation: Active</span>
                            </li>
                            <li className="flex items-center gap-3">
                                <div className="w-2 h-2 rounded-full bg-green-400"></div>
                                <span className="text-sm font-bold text-slate-300">Auth Token: Secure (Sanctum)</span>
                            </li>
                            <li className="flex items-center gap-3">
                                <div className="w-2 h-2 rounded-full bg-orange-400"></div>
                                <span className="text-sm font-bold text-slate-300">2FA: Not Configured</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    );
}
