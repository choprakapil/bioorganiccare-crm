import { useState, useEffect } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import { Users, UserPlus, Shield, Activity, Phone, Mail, CheckCircle2, XCircle, KeyRound, Trash2, Eye, RefreshCw, AlertTriangle, FileText, Smartphone } from 'lucide-react';
import { useAuth } from '../../context/AuthContext';

import SubscriptionControlModal from '../../components/admin/SubscriptionControlModal';
import AdminPageHeader from '../../components/layout/AdminPageHeader';

export default function DoctorManagement() {
    const { startImpersonation } = useAuth();
    const [doctors, setDoctors] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showAddModal, setShowAddModal] = useState(false);
    const [refData, setRefData] = useState({ plans: [], specialties: [] });
    const [submitting, setSubmitting] = useState(false);

    // Subscription Manage State
    const [showManageModal, setShowManageModal] = useState(false);
    const [selectedManageDoctor, setSelectedManageDoctor] = useState(null);

    // Reset Password State
    const [showResetModal, setShowResetModal] = useState(false);
    const [selectedDoctor, setSelectedDoctor] = useState(null);
    const [newPassword, setNewPassword] = useState('');

    // Logs State
    const [showLogsModal, setShowLogsModal] = useState(false);
    const [logs, setLogs] = useState([]);
    const [loadingLogs, setLoadingLogs] = useState(false);

    // Staff View State
    const [showStaffModal, setShowStaffModal] = useState(false);
    const [staffList, setStaffList] = useState([]);

    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        phone: '',
        whatsapp_number: '',
        specialty_id: '',
        plan_id: '',
        clinic_name: ''
    });

    useEffect(() => {
        fetchDoctors();
        fetchRefData();
    }, []);

    const fetchDoctors = async () => {
        try {
            const res = await api.get('/admin/doctors');
            setDoctors(res.data);
        } catch (err) {
            toast.error('Failed to load doctor registry');
        } finally {
            setLoading(false);
        }
    };

    const fetchRefData = async () => {
        try {
            const res = await api.get('/admin/doctors/reference-data');
            setRefData(res.data);
        } catch (err) {
            toast.error(err.response?.data?.message || 'Unexpected error occurred');
        }
    };

    const handleManageSubscription = (doctor) => {
        setSelectedManageDoctor(doctor);
        setShowManageModal(true);
    };

    const handleCreate = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            await api.post('/admin/doctors', formData);
            toast.success('Doctor registered successfully');
            setShowAddModal(false);
            setFormData({ name: '', email: '', password: '', phone: '', whatsapp_number: '', specialty_id: '', plan_id: '', clinic_name: '' });
            fetchDoctors();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Registration failed');
        } finally {
            setSubmitting(false);
        }
    };

    const handleToggleActive = async (id) => {
        try {
            const res = await api.patch(`/admin/doctors/${id}/toggle-active`);
            toast.success(res.data.message);
            fetchDoctors();
        } catch (err) {
            toast.error('Failed to update doctor status');
        }
    };

    const handleDelete = async (doc) => {
        if (!confirm(`⚠️ DANGER ZONE\n\nAre you sure you want to PERMANENTLY DELETE ${doc.name}'s account?\n\nThis will:\n- Prevent future logins FOREVER\n- Terminate valid sessions\n- Hide account from active lists\n\nPatient data remains preserved.`)) {
            return;
        }
        try {
            await api.delete(`/admin/delete/doctor/${doc.id}/archive`);
            toast.success('Account permanently deleted');
            fetchDoctors();
        } catch (err) {
            toast.error('Deletion failed');
        }
    };

    const handleViewLogs = async (doc) => {
        setSelectedDoctor(doc);
        setShowLogsModal(true);
        setLoadingLogs(true);
        try {
            const res = await api.get(`/admin/doctors/${doc.id}/logs`);
            setLogs(res.data);
        } catch (err) {
            toast.error('Failed to fetch logs');
        } finally {
            setLoadingLogs(false);
        }
    };

    const handleViewStaff = async (doc) => {
        setSelectedDoctor(doc);
        setLoadingLogs(true);
        setShowStaffModal(true);
        try {
            const res = await api.get(`/admin/doctors/${doc.id}`);
            // res.data is user with 'staff' relation
            setStaffList(res.data.staff || []);
        } catch (err) {
            toast.error('Failed to load staff details');
        } finally {
            setLoadingLogs(false);
        }
    };

    const openResetModal = (doc) => {
        setSelectedDoctor(doc);
        setNewPassword('');
        setShowResetModal(true);
    };

    const generatePassword = () => {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let pass = "";
        for (let i = 0; i < 12; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        setNewPassword(pass);
    };

    const handlePasswordReset = async () => {
        if (!newPassword || newPassword.length < 8) {
            toast.error('Password must be at least 8 characters');
            return;
        }

        try {
            await api.patch(`/admin/doctors/${selectedDoctor.id}/reset-password`, { password: newPassword });
            toast.success(`Password reset for ${selectedDoctor.name}`);
            setShowResetModal(false);
            setSelectedDoctor(null);
            setNewPassword('');
        } catch (err) {
            toast.error('Password reset failed');
        }
    };

    return (
        <div className="max-w-7xl mx-auto px-6 py-6 transition-all duration-300">
            <AdminPageHeader
                title="Doctor Management"
                description="Manage tenant accounts, clinic assignments and subscriptions."
                actions={
                    <button
                        onClick={() => setShowAddModal(true)}
                        className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg flex items-center gap-2 transition-all shadow-sm"
                    >
                        <UserPlus size={20} /> Add New Doctor
                    </button>
                }
            />

            {loading ? (
                <div className="p-12 text-center text-slate-400 font-medium animate-pulse">Synchronizing platform registry...</div>
            ) : doctors.length === 0 ? (
                <div className="bg-white p-12 rounded-[3rem] border-2 border-dashed border-slate-100 text-center">
                    <div className="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <Users size={32} />
                    </div>
                    <h3 className="text-xl font-black text-slate-800 mb-2">No Doctor Records Found</h3>
                    <p className="text-slate-500 max-w-md mx-auto font-medium">Click "Add New Doctor" to register the first professional on the platform.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 gap-6">
                    {doctors.map(doctor => {
                        const maxP = doctor.plan?.max_patients === -1 ? 999999 : (doctor.plan?.max_patients || 0);
                        const usedP = doctor.patients_count || 0;
                        const pctP = Math.min(100, (usedP / maxP) * 100);

                        const maxA = doctor.plan?.max_appointments_monthly === -1 ? 999999 : (doctor.plan?.max_appointments_monthly || 0);
                        const usedA = doctor.this_month_appointments_count || 0;
                        const pctA = Math.min(100, (usedA / maxA) * 100);

                        const staffCount = doctor.staff_count || 0;
                        const maxStaff = doctor.plan?.features?.max_staff === -1 ? 99 : (doctor.plan?.features?.max_staff || 0);

                        return (
                            <div key={doctor.id} className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:translate-y-[-2px] transition-all flex flex-col items-start gap-4 group">

                                {/* Header Row */}
                                <div className="w-full flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div className="flex gap-6 items-center">
                                        <div className="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-2xl font-black transition-transform group-hover:rotate-6">
                                            {doctor.name.charAt(0)}
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-black text-slate-800 flex items-center gap-2">
                                                {doctor.name}
                                                {doctor.is_active ?
                                                    <span className="bg-green-100 text-green-600 text-[10px] px-2 py-0.5 rounded-full uppercase">Active</span> :
                                                    <span className="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full uppercase">Inactive</span>
                                                }
                                            </h3>
                                            <p className="text-slate-500 font-bold text-sm">{doctor.specialty?.name || 'Unassigned Specialization'} • {doctor.clinic_name || 'Individual Practice'}</p>
                                            <div className="flex gap-4 mt-2 text-xs text-slate-400 font-medium">
                                                <span className="flex items-center gap-1"><Mail size={12} /> {doctor.email}</span>
                                                <span className="flex items-center gap-1"><Smartphone size={12} /> {doctor.phone || 'No phone'}</span>
                                                <span className="flex items-center gap-1"><Shield size={12} /> {doctor.plan?.name || 'Basic'}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex gap-2 items-center">
                                        <button
                                            onClick={() => startImpersonation(doctor.id)}
                                            className="px-3 py-1 bg-indigo-600 text-white rounded text-xs font-bold hover:bg-indigo-700"
                                        >
                                            Impersonate
                                        </button>

                                        <button
                                            onClick={() => handleManageSubscription(doctor)}
                                            title="Manage Subscription"
                                            className="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-green-50 hover:text-green-600 transition-colors border border-transparent hover:border-green-100">
                                            <RefreshCw size={20} />
                                        </button>

                                        <button
                                            onClick={() => handleViewStaff(doctor)}
                                            title="View Staff Team"
                                            className="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-purple-50 hover:text-purple-600 transition-colors">
                                            <Users size={20} />
                                        </button>

                                        <button
                                            onClick={() => handleViewLogs(doctor)}
                                            title="View Activity Logs"
                                            className="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                            <FileText size={20} />
                                        </button>

                                        <button
                                            onClick={() => handleToggleActive(doctor.id)}
                                            title={doctor.is_active ? "Deactivate (Temporary block)" : "Activate"}
                                            className={`p-3 rounded-xl transition-all ${doctor.is_active ? 'bg-slate-50 text-slate-400 hover:bg-orange-50 hover:text-orange-500' : 'bg-green-50 text-green-600 hover:bg-green-100'}`}>
                                            {doctor.is_active ? <XCircle size={20} /> : <CheckCircle2 size={20} />}
                                        </button>

                                        <button
                                            onClick={() => openResetModal(doctor)}
                                            title="Reset Password"
                                            className="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-blue-50 hover:text-blue-500 transition-colors">
                                            <KeyRound size={20} />
                                        </button>

                                        <button
                                            onClick={() => handleDelete(doctor)}
                                            title="Permanently Delete"
                                            className="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-red-50 hover:text-red-500 transition-colors">
                                            <Trash2 size={20} />
                                        </button>
                                    </div>
                                </div>

                                {/* Subscription Lifecycle Row */}
                                <div className="w-full flex justify-between items-center py-2 px-1">
                                    <div className="flex gap-3 items-center">
                                        <span className={`px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider
                                            ${doctor.subscription_status === 'active' ? 'bg-green-100 text-green-700' :
                                                doctor.subscription_status === 'past_due' ? 'bg-yellow-100 text-yellow-700' :
                                                    doctor.subscription_status === 'lifetime' ? 'bg-purple-100 text-purple-700' :
                                                        'bg-red-100 text-red-700'}`}>
                                            {doctor.subscription_status?.replace('_', ' ') || 'Inactive'}
                                        </span>
                                        <div className="flex items-center gap-1 text-xs font-bold text-slate-500">
                                            <span className="capitalize">{doctor.billing_interval || 'Monthly'}</span>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Renews On</p>
                                        <p className="text-xs font-black text-slate-700">
                                            {doctor.subscription_renews_at ? new Date(doctor.subscription_renews_at).toLocaleDateString() : 'Lifetime / Never'}
                                        </p>
                                    </div>
                                </div>

                                {/* Plan Usage Row */}
                                <div className="w-full grid grid-cols-3 gap-4 mt-2 bg-slate-50/50 p-4 rounded-2xl border border-slate-50">
                                    <div>
                                        <div className="flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                            <span>Patients</span>
                                            <span>{usedP} / {doctor.plan?.max_patients === -1 ? '∞' : maxP}</span>
                                        </div>
                                        <div className="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div className={`h-full rounded-full ${pctP > 90 ? 'bg-red-500' : 'bg-primary'}`} style={{ width: `${pctP}%` }}></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                            <span>Appts (Month)</span>
                                            <span>{usedA} / {doctor.plan?.max_appointments_monthly === -1 ? '∞' : maxA}</span>
                                        </div>
                                        <div className="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div className={`h-full rounded-full ${pctA > 90 ? 'bg-orange-500' : 'bg-blue-500'}`} style={{ width: `${pctA}%` }}></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="flex justify-between items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                                            <span>Staff</span>
                                            <span>{staffCount} / {maxStaff === -1 ? '∞' : maxStaff}</span>
                                        </div>
                                        <div className="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div className="h-full bg-purple-500 rounded-full" style={{ width: `${Math.min(100, (staffCount / (maxStaff || 1)) * 100)}%` }}></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )
                    })}
                </div>
            )}

            {/* Registration Modal */}
            {showAddModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-2xl rounded-[3rem] p-10 shadow-2xl overflow-y-auto max-h-[90vh]">
                        <h2 className="text-3xl font-black text-slate-800 mb-8">Register New Doctor</h2>

                        <form onSubmit={handleCreate} className="space-y-6">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Doctor Name</label>
                                    <input
                                        type="text" required value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Email Address</label>
                                    <input
                                        type="email" required value={formData.email} onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">WhatsApp Number</label>
                                    <div className="relative">
                                        <Smartphone className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={16} />
                                        <input
                                            type="text" value={formData.phone} onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                                            className="w-full pl-12 pr-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                            placeholder="+91..."
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Initial Password</label>
                                    <input
                                        type="password" required value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-black text-slate-400 uppercase mb-2">Clinic/Practice Name</label>
                                <input
                                    type="text" value={formData.clinic_name} onChange={(e) => setFormData({ ...formData, clinic_name: e.target.value })}
                                    className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold"
                                    placeholder="e.g. Smith Dental Care"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Medical Specialty</label>
                                    <select
                                        required
                                        value={formData.specialty_id}
                                        onChange={(e) => {
                                            const specialtyId = e.target.value;
                                            setFormData({
                                                ...formData,
                                                specialty_id: specialtyId,
                                                plan_id: ''
                                            });
                                        }}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 font-bold bg-slate-50"
                                    >
                                        <option value="">Select Specialty</option>
                                        {refData.specialties.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Subscription Plan</label>
                                    <select
                                        required value={formData.plan_id} onChange={(e) => setFormData({ ...formData, plan_id: e.target.value })}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 font-bold bg-slate-50"
                                    >
                                        <option value="">Select Tier</option>
                                        {refData.plans
                                            .filter(p => p.specialty_id == formData.specialty_id)
                                            .map(p => (
                                                <option key={p.id} value={p.id}>{p.name}</option>
                                            ))
                                        }
                                    </select>
                                </div>
                            </div>

                            <div className="flex justify-end gap-4 pt-6">
                                <button type="button" onClick={() => setShowAddModal(false)} className="px-8 py-4 font-black text-slate-400 hover:bg-slate-100 rounded-2xl">Dismiss</button>
                                <button
                                    type="submit" disabled={submitting}
                                    className="px-10 py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark shadow-2xl shadow-primary/30 transition-all disabled:opacity-50"
                                >
                                    {submitting ? 'Registering...' : 'Enroll Doctor'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Password Reset Modal */}
            {showResetModal && selectedDoctor && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-md rounded-[2.5rem] p-8 shadow-2xl animate-fade-in-up">
                        <div className="mb-6 text-center">
                            <div className="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 text-primary">
                                <KeyRound size={32} />
                            </div>
                            <h2 className="text-2xl font-black text-slate-800">Reset Security</h2>
                            <p className="text-slate-500 font-medium mt-1">Update credentials for {selectedDoctor.name}</p>
                        </div>

                        <div className="bg-amber-50 rounded-xl p-4 mb-6 border border-amber-100 flex gap-3 text-amber-700 text-xs font-bold">
                            <AlertTriangle size={32} className="shrink-0" />
                            <p>Changing the password will immediately terminate all active sessions for this doctor.</p>
                        </div>

                        <div className="space-y-4">
                            <div>
                                <label className="block text-xs font-black text-slate-400 uppercase mb-2">New Password</label>
                                <div className="relative">
                                    <input
                                        type="text"
                                        value={newPassword}
                                        onChange={(e) => setNewPassword(e.target.value)}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 font-bold font-mono text-center tracking-widest"
                                        placeholder="Generate or Type"
                                    />
                                    <button
                                        onClick={generatePassword}
                                        className="absolute right-2 top-2 bottom-2 aspect-square rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 flex items-center justify-center transition-colors"
                                        title="Auto Generate"
                                    >
                                        <RefreshCw size={18} />
                                    </button>
                                </div>
                            </div>

                            <button
                                onClick={handlePasswordReset}
                                className="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all"
                            >
                                Update Credentials
                            </button>

                            <button
                                onClick={() => setShowResetModal(false)}
                                className="w-full py-3 text-slate-400 font-bold hover:text-slate-600 transition-colors"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Logs Modal */}
            {showLogsModal && selectedDoctor && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-2xl rounded-[2.5rem] p-8 shadow-2xl animate-fade-in-up max-h-[80vh] flex flex-col">
                        <div className="mb-6 flex justify-between items-center">
                            <div>
                                <h2 className="text-2xl font-black text-slate-800">Activity Logs</h2>
                                <p className="text-slate-500 font-medium text-sm">Recent actions for {selectedDoctor.name}</p>
                            </div>
                            <button onClick={() => setShowLogsModal(false)} className="p-2 bg-slate-100 rounded-full hover:bg-slate-200 text-slate-500">
                                <XCircle size={20} />
                            </button>
                        </div>

                        <div className="flex-1 overflow-y-auto space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            {loadingLogs ? (
                                <p className="text-center text-slate-400 py-4">Loading history...</p>
                            ) : logs.length === 0 ? (
                                <p className="text-center text-slate-400 py-4 italic">No activity recorded for this user.</p>
                            ) : (
                                logs.map(log => (
                                    <div key={log.id} className="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex items-start gap-3">
                                        <Activity size={16} className="text-slate-400 mt-1" />
                                        <div>
                                            <div className="font-bold text-slate-800 text-sm">{log.action || 'Unknown Action'}</div>
                                            <div className="text-xs text-slate-500">{log.description}</div>
                                            <div className="text-[10px] text-slate-400 mt-1">{new Date(log.created_at).toLocaleString()}</div>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* Staff Modal */}
            {showStaffModal && selectedDoctor && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-2xl rounded-[2.5rem] p-8 shadow-2xl animate-fade-in-up max-h-[80vh] flex flex-col">
                        <div className="mb-6 flex justify-between items-center">
                            <div>
                                <h2 className="text-2xl font-black text-slate-800">Clinic Staff</h2>
                                <p className="text-slate-500 font-medium text-sm">Team members for {selectedDoctor.name}</p>
                            </div>
                            <button onClick={() => setShowStaffModal(false)} className="p-2 bg-slate-100 rounded-full hover:bg-slate-200 text-slate-500">
                                <XCircle size={20} />
                            </button>
                        </div>

                        <div className="flex-1 overflow-y-auto space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            {loadingLogs ? (
                                <p className="text-center text-slate-400 py-4">Loading team...</p>
                            ) : staffList.length === 0 ? (
                                <p className="text-center text-slate-400 py-4 italic">No staff members registered.</p>
                            ) : (
                                staffList.map(staff => (
                                    <div key={staff.id} className="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between">
                                        <div className="flex items-center gap-4">
                                            <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold ${staff.is_active ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-400'}`}>
                                                {staff.name.charAt(0)}
                                            </div>
                                            <div>
                                                <div className="font-bold text-slate-800 text-sm">{staff.name}</div>
                                                <div className="text-xs text-slate-500">{staff.email}</div>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <span className={`px-2 py-1 text-[10px] font-black uppercase rounded-full ${staff.role_type === 'receptionist' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'
                                                }`}>
                                                {staff.role_type || 'Assistant'}
                                            </span>
                                            <span className={`text-[10px] font-bold ${staff.is_active ? 'text-green-500' : 'text-slate-400'}`}>
                                                {staff.is_active ? 'Active' : 'Disabled'}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* Subscription Control Modal */}
            {showManageModal && selectedManageDoctor && (
                <SubscriptionControlModal
                    doctor={selectedManageDoctor}
                    onClose={() => setShowManageModal(false)}
                    onSuccess={() => {
                        fetchDoctors(); // Refresh list to show new dates/status
                    }}
                />
            )}

        </div>
    );
}
