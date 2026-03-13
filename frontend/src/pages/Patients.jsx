import React, { useState, useEffect } from 'react';
import api from '../api/axios';
import toast from 'react-hot-toast';
import { UserPlus, Search, Phone, Calendar, MoreVertical, Trash2, Edit, ShieldAlert, X } from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Patients() {
    const { user } = useAuth();
    const navigate = useNavigate();
    const [patients, setPatients] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [showAddModal, setShowAddModal] = useState(false);
    const [editingPatient, setEditingPatient] = useState(null);

    useEffect(() => {
        fetchPatients();
    }, []);

    const fetchPatients = async () => {
        try {
            const res = await api.get('/patients');
            setPatients(Array.isArray(res.data) ? res.data : res.data?.data ?? []);
        } catch (err) {
            toast.error('Failed to load patients');
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Are you sure?')) return;
        try {
            await api.delete(`/admin/delete/patient/${id}/archive`);
            toast.success('Deleted');
            fetchPatients();
        } catch (err) {
            toast.error('Delete failed');
        }
    };

    const handleEdit = (patient) => {
        setEditingPatient(patient);
        setShowAddModal(true);
    };

    const filteredPatients = patients.filter(p =>
        p.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.phone?.includes(searchQuery)
    );

    const isLimitReached = user?.plan?.max_patients !== -1 && patients.length >= user?.plan?.max_patients;
    const isLimitNear = user?.plan?.max_patients !== -1 && (patients.length / user?.plan?.max_patients) >= 0.8;

    return (
        <div className="space-y-6">
            <header className="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div>
                    <h1 className="text-4xl font-black text-slate-800 tracking-tight">Patient Registry</h1>
                    <p className="text-slate-500 font-medium">Manage your clinical records with professional-grade privacy.</p>
                </div>

                <div className="flex items-center gap-4 w-full md:w-auto">
                    {user?.plan?.max_patients !== -1 && (
                        <div className={`hidden lg:flex flex-col items-end px-4 py-2 rounded-2xl border ${isLimitNear ? 'bg-orange-50 border-orange-100' : 'bg-slate-50 border-slate-100'}`}>
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Clinic Capacity</p>
                            <div className="flex items-center gap-2">
                                <span className={`text-sm font-black ${isLimitNear ? 'text-orange-600' : 'text-slate-700'}`}>{patients.length} / {user?.plan?.max_patients}</span>
                                <div className="w-20 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                    <div
                                        className={`h-full ${isLimitNear ? 'bg-orange-500' : 'bg-primary'}`}
                                        style={{ width: `${(patients.length / user?.plan?.max_patients) * 100}%` }}
                                    ></div>
                                </div>
                            </div>
                        </div>
                    )}

                    <button
                        onClick={() => setShowAddModal(true)}
                        disabled={isLimitReached}
                        className={`px-8 py-4 rounded-2xl font-black flex items-center gap-2 transition-all shadow-xl ${isLimitReached ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-primary text-white hover:bg-primary-dark shadow-primary/20'}`}
                    >
                        {isLimitReached ? <ShieldAlert size={20} /> : <UserPlus size={20} />}
                        {isLimitReached ? 'Limit Reached' : 'Register Patient'}
                    </button>
                </div>
            </header>

            {/* Search Bar */}
            <div className="relative max-w-md">
                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                <input
                    type="text"
                    placeholder="Search name or phone..."
                    className="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-primary outline-none transition-all shadow-sm"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                />
            </div>

            {/* Patient Table */}
            {!Array.isArray(patients) ? null : (
                <div className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead className="bg-slate-50 border-bottom border-slate-100">
                                <tr>
                                    <th className="px-6 py-4 font-bold text-slate-600 w-16">S.No</th>
                                    <th className="px-6 py-4 font-bold text-slate-600">Patient Name</th>
                                    <th className="px-6 py-4 font-bold text-slate-600">Contact</th>
                                    <th className="px-6 py-4 font-bold text-slate-600">Status</th>
                                    <th className="px-6 py-4 font-bold text-slate-600 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {loading ? (
                                    <tr><td colSpan="5" className="px-6 py-12 text-center text-slate-400">Loading registry...</td></tr>
                                ) : filteredPatients.length === 0 ? (
                                    <tr><td colSpan="5" className="px-6 py-12 text-center text-slate-400">No patients found.</td></tr>
                                ) : (
                                    Array.isArray(filteredPatients) && filteredPatients.map((p, idx) => (
                                        <tr key={p.id} className="hover:bg-slate-50/50 transition-colors">
                                            <td className="px-6 py-4 font-black text-slate-400">
                                                {(idx + 1).toString().padStart(2, '0')}
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="font-bold text-slate-800">{p.name}</div>
                                                <div className="text-xs text-slate-400 italic">ID: #{p.id}</div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-2 text-slate-600">
                                                    <Phone size={14} className="text-primary" />
                                                    {p.phone || 'N/A'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                {(() => {
                                                    const total = Number(p.invoice_total) || 0;
                                                    const paid = Number(p.amount_paid) || 0;

                                                    let paymentStatus = "Unpaid";

                                                    if (paid >= total && total > 0) {
                                                        paymentStatus = "Paid";
                                                    } else if (paid > 0) {
                                                        paymentStatus = "Partial";
                                                    }

                                                    return (
                                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${paymentStatus === "Paid"
                                                            ? "bg-green-100 text-green-800"
                                                            : paymentStatus === "Partial"
                                                                ? "bg-yellow-100 text-yellow-800"
                                                                : "bg-orange-100 text-orange-800"
                                                            }`}>
                                                            {paymentStatus}
                                                        </span>
                                                    );
                                                })()}
                                            </td>
                                            <td className="px-6 py-4 text-right space-x-2">
                                                <Link
                                                    to={`/patients/${p.id}`}
                                                    className="inline-flex items-center px-3 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors"
                                                >
                                                    View
                                                </Link>

                                                <button
                                                    onClick={() => handleEdit(p)}
                                                    className="inline-flex items-center px-3 py-2 bg-slate-50 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-100 transition-colors"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    onClick={() => handleDelete(p.id)}
                                                    className="inline-flex items-center px-3 py-2 bg-red-50 text-red-500 text-xs font-bold rounded-lg hover:bg-red-100 transition-colors"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* Add Modal */}
            {showAddModal && (
                <ErrorBoundary onClose={() => {
                    setShowAddModal(false);
                    setEditingPatient(null);
                }}>
                    <RegistrationModal
                        existingPatients={patients}
                        initialData={editingPatient}
                        onClose={() => {
                            setShowAddModal(false);
                            setEditingPatient(null);
                        }}
                        onSuccess={() => {
                            setShowAddModal(false);
                            setEditingPatient(null);
                            fetchPatients();
                        }}
                    />
                </ErrorBoundary>
            )}
        </div>
    );
}

function RegistrationModal({ existingPatients, initialData, onClose, onSuccess }) {
    const isEdit = !!initialData;
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [duplicateMatch, setDuplicateMatch] = useState(null);

    // Form
    const [formData, setFormData] = useState({
        name: '', phone: '', age: '', gender: 'Male', address: '', notes: '',
        first_visit_date: new Date().toISOString().split('T')[0]
    });

    // Duplicate Check Effect
    useEffect(() => {
        if (isEdit) {
            setDuplicateMatch(null);
            return;
        }

        const normalizedPhone = formData.phone ? formData.phone.replace(/[\s\-\+\(\)]/g, '') : '';
        const normalizedName = formData.name ? formData.name.toLowerCase().trim() : '';

        if (!normalizedPhone && normalizedName.length < 3) {
            setDuplicateMatch(null);
            return;
        }

        let match = null;
        let type = null;

        // 1. Check Phone (High Confidence)
        if (normalizedPhone.length >= 4) {
            match = existingPatients?.find(p => {
                const pPhone = p.phone ? p.phone.replace(/[\s\-\+\(\)]/g, '') : '';
                return pPhone === normalizedPhone;
            });
            if (match) type = 'phone';
        }

        // 2. Check Name (Medium Confidence) - Only if no phone match
        if (!match && normalizedName.length >= 3) {
            match = existingPatients?.find(p => p.name.toLowerCase().trim() === normalizedName);
            if (match) type = 'name';
        }

        if (match) {
            setDuplicateMatch({ ...match, matchType: type });
        } else {
            setDuplicateMatch(null);
        }
    }, [formData.phone, formData.name, existingPatients, isEdit]);

    useEffect(() => {
        if (initialData) {
            setFormData({
                name: initialData.name,
                phone: initialData.phone,
                age: initialData.age,
                gender: initialData.gender,
                address: initialData.address || '',
                notes: initialData.notes || '',
                first_visit_date: initialData.created_at?.split('T')[0] || new Date().toISOString().split('T')[0]
            });
        }
    }, [initialData]);


    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            if (isEdit) {
                await api.put(`/patients/${initialData.id}`, formData);
                toast.success('Patient details updated');
                onSuccess();
            } else {
                const res = await api.post('/patients', formData);
                toast.success('Patient registered successfully');
                
                const patientId = res.data?.patient?.id || res.data?.id;
                if (patientId) {
                    navigate(`/patients/${patientId}`);
                } else {
                    onSuccess();
                }
            }
        } catch (err) {
            toast.error(err.response?.data?.message || 'Operation failed');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div className={`bg-white w-full ${isEdit ? 'max-w-xl' : 'max-w-4xl'} rounded-3xl shadow-2xl flex flex-col max-h-[90vh]`}>
                <div className="p-6 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white rounded-t-3xl z-10">
                    <div>
                        <h2 className="text-2xl font-black text-slate-800">{isEdit ? 'Edit Patient' : 'Register New Patient'}</h2>
                        <p className="text-sm text-slate-500 font-medium">Identity & Contact Demographics</p>
                    </div>
                </div>

                <div className="flex-1 overflow-y-auto p-8">
                    <form id="regForm" onSubmit={handleSubmit} className="space-y-8">
                        {/* 1. Core Details (Shown in both) */}
                        <section>
                            {!isEdit && (
                                <h3 className="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <span className="w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs">1</span>
                                    Patient Demographics
                                </h3>
                            )}
                            <div className={`grid grid-cols-1 ${isEdit ? 'gap-4' : 'md:grid-cols-3 gap-4'}`}>
                                <div>
                                    <label className="block text-xs font-bold text-slate-500 mb-1">Full Name *</label>
                                    <input required type="text" value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary font-bold" />
                                    {duplicateMatch && duplicateMatch.matchType === 'name' && (
                                        <div className="mt-2 p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3 animate-in fade-in slide-in-from-top-2">
                                            <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0 text-blue-500">
                                                <UserPlus size={16} />
                                            </div>
                                            <div>
                                                <p className="text-xs font-bold text-blue-700">Possible Name Match</p>
                                                <p className="text-[10px] text-blue-600 font-medium">{duplicateMatch.name} • {duplicateMatch.phone || 'No Phone'}</p>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        onClose();
                                                        setTimeout(() => toast('Please search for this patient to view details.', { icon: '🔍' }), 100);
                                                    }}
                                                    className="mt-1 text-[10px] font-black text-blue-700 underline hover:text-blue-800"
                                                >
                                                    View Profile
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-slate-500 mb-1">Phone *</label>
                                    <input required type="tel" value={formData.phone} onChange={e => setFormData({ ...formData, phone: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary font-bold" />
                                    {duplicateMatch && duplicateMatch.matchType === 'phone' && (
                                        <div className="mt-2 p-3 bg-orange-50 border border-orange-100 rounded-xl flex items-start gap-3 animate-in fade-in slide-in-from-top-2">
                                            <ShieldAlert className="text-orange-500 shrink-0 mt-0.5" size={16} />
                                            <div>
                                                <p className="text-xs font-bold text-orange-700">Likely Duplicate Patient</p>
                                                <p className="text-[10px] text-orange-600 font-medium">{duplicateMatch.name} • Last Visit: {new Date(duplicateMatch.created_at).toLocaleDateString()}</p>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        onClose();
                                                        setTimeout(() => toast('Please search for this patient to view details.', { icon: '🔍' }), 100);
                                                    }}
                                                    className="mt-1 text-[10px] font-black text-orange-700 underline hover:text-orange-800"
                                                >
                                                    View Existing Profile
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-slate-500 mb-1">Visit Date</label>
                                    <input type="date" value={formData.first_visit_date} onChange={e => setFormData({ ...formData, first_visit_date: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary font-bold" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-slate-500 mb-1">Age</label>
                                    <input type="number" value={formData.age} onChange={e => setFormData({ ...formData, age: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary font-bold" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-slate-500 mb-1">Gender</label>
                                    <select value={formData.gender} onChange={e => setFormData({ ...formData, gender: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary font-bold bg-white">
                                        <option>Male</option><option>Female</option><option>Other</option>
                                    </select>
                                </div>
                                <div className={isEdit ? '' : 'md:col-span-3'}>
                                    <label className="block text-xs font-bold text-slate-500 mb-1">Address / Notes</label>
                                    <textarea value={formData.address} onChange={e => setFormData({ ...formData, address: e.target.value })} className="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary h-20 font-medium"></textarea>
                                </div>
                            </div>
                        </section>

                    </form>
                </div>

                <div className="p-6 border-t border-slate-100 bg-slate-50 rounded-b-3xl flex justify-end gap-3 z-10">
                    <button type="button" onClick={onClose} className="px-6 py-3 font-bold text-slate-500 hover:bg-white rounded-xl transition-all">Cancel</button>
                    <button
                        type="submit"
                        form="regForm"
                        disabled={loading}
                        className="px-8 py-3 bg-primary text-white font-black rounded-xl hover:bg-primary-dark shadow-xl shadow-primary/20 transition-all disabled:opacity-50 flex items-center gap-2"
                    >
                        {loading ? 'Processing...' : (isEdit ? 'Update Details' : 'Complete Registration')}
                    </button>
                </div>
            </div>
        </div>
    );
}

class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        toast.error('Unexpected error occurred');
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div className="bg-white p-8 rounded-3xl max-w-md text-center shadow-2xl">
                        <div className="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <ShieldAlert size={32} />
                        </div>
                        <h2 className="text-xl font-black text-slate-800 mb-2">Something went wrong</h2>
                        <p className="text-slate-500 font-medium text-sm mb-6">The patient form encountered an unexpected error. Please try again.</p>
                        <button
                            onClick={() => {
                                this.setState({ hasError: false });
                                if (this.props.onClose) this.props.onClose();
                            }}
                            className="px-6 py-3 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-all"
                        >
                            Close & Recover
                        </button>
                    </div>
                </div>
            );
        }

        return this.props.children;
    }
}
