import { useState, useEffect } from 'react';
import api from '../api/axios';
import toast from 'react-hot-toast';
import { Users, Plus, Trash2, Edit2, CheckCircle, XCircle } from 'lucide-react';

export default function StaffManagement() {
    const [staff, setStaff] = useState([]);
    const [limits, setLimits] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editMode, setEditMode] = useState(false);
    const [selectedId, setSelectedId] = useState(null);

    const [form, setForm] = useState({
        name: '',
        email: '',
        password: '',
        phone: '',
        role_type: 'assistant',
        is_active: true,
        permissions: {}
    });

    useEffect(() => {
        fetchStaff();
    }, []);

    const fetchStaff = async () => {
        try {
            const res = await api.get('/staff');
            setStaff(res.data.data || res.data); // Handle pagination or simple list
            setLimits(res.data.meta);
        } catch (err) {
            toast.error('Failed to load team members');
        } finally {
            setLoading(false);
        }
    };

    const openCreateParams = () => {
        setForm({ name: '', email: '', password: '', phone: '', role_type: 'assistant', is_active: true, permissions: {} });
        setEditMode(false);
        setSelectedId(null);
        setShowModal(true);
    };

    const openEditParams = (member) => {
        // Parse permissions if it's a string (though it should be cast by Laravel)
        let perms = member.permissions || {};
        if (typeof perms === 'string') {
            try { perms = JSON.parse(perms); } catch (e) { perms = {}; }
        }

        setForm({
            name: member.name,
            email: member.email,
            password: '',
            phone: member.phone || '',
            role_type: member.role_type || 'assistant',
            is_active: member.is_active,
            permissions: perms
        });
        setEditMode(true);
        setSelectedId(member.id);
        setShowModal(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        try {
            if (editMode) {
                // Update
                const payload = { ...form };
                if (!payload.password) delete payload.password; // Don't send empty password

                await api.put(`/staff/${selectedId}`, payload);
                toast.success('Team member updated');
            } else {
                // Create
                await api.post('/staff', form);
                toast.success('Team member added');
            }
            setShowModal(false);
            fetchStaff();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Operation failed');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Are you sure? This will revoke access immediately.')) return;
        try {
            await api.delete(`/admin/delete/staff/${id}/archive`);
            toast.success('Account removed');
            fetchStaff();
        } catch (err) {
            toast.error('Failed to delete account');
        }
    };

    return (
        <section className="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm animate-in fade-in slide-in-from-bottom-4 duration-1000">
            <div className="flex items-center justify-between mb-8">
                <div className="flex items-center gap-3">
                    <div className="p-3 bg-purple-50 text-purple-600 rounded-2xl">
                        <Users size={24} />
                    </div>
                    <div>
                        <h2 className="text-2xl font-black text-slate-800">Team Management</h2>
                        <p className="text-sm text-slate-500 font-medium">Manage assistants & receptionist access.</p>
                    </div>
                </div>
                <div className="flex items-center gap-4">
                    {limits && (
                        <div className="text-right hidden sm:block">
                            <p className="text-xs font-bold text-slate-400 uppercase tracking-wider">{limits.plan_name} Plan</p>
                            <p className={`text-sm font-bold ${limits.max_staff !== -1 && limits.count >= limits.max_staff ? 'text-red-500' : 'text-slate-600'
                                }`}>
                                {limits.max_staff === -1 ? 'Unlimited Staff' : `${limits.count} / ${limits.max_staff} Active`}
                            </p>
                        </div>
                    )}
                    <button
                        onClick={openCreateParams}
                        disabled={limits && limits.max_staff !== -1 && limits.count >= limits.max_staff}
                        className={`px-6 py-3 font-bold rounded-xl flex items-center gap-2 transition-all ${limits && limits.max_staff !== -1 && limits.count >= limits.max_staff
                            ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                            : 'bg-slate-900 text-white hover:bg-slate-800'
                            }`}
                    >
                        <Plus size={18} /> Add Member
                    </button>
                </div>
            </div>

            {loading ? (
                <div className="text-center py-8 text-slate-400">Loading team...</div>
            ) : staff.length === 0 ? (
                <div className="text-center py-12 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                    <p className="text-slate-400 font-bold mb-4">No team members connected.</p>
                    <p className="text-xs text-slate-400 w-2/3 mx-auto">Add staff to allow them to manage appointments and check-ins while you focus on patients.</p>
                </div>
            ) : (
                <div className="space-y-4">
                    {staff.map(member => (
                        <div key={member.id} className={`flex flex-col sm:flex-row sm:justify-between sm:items-start p-6 rounded-2xl border ${!member.is_active ? 'bg-slate-100 border-slate-200' : 'bg-slate-50 border-slate-100'}`}>
                            <div className="flex items-start gap-4 mb-4 sm:mb-0">
                                <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold flex-shrink-0 ${!member.is_active ? 'bg-slate-200 text-slate-500' : 'bg-indigo-100 text-indigo-600'}`}>
                                    {member.name.charAt(0)}
                                </div>
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h4 className={`font-bold ${!member.is_active ? 'text-slate-500' : 'text-slate-800'}`}>{member.name}</h4>
                                        {!member.is_active && <span className="text-[10px] bg-slate-200 text-slate-500 px-2 py-0.5 rounded-full font-bold">DISABLED</span>}
                                    </div>
                                    <p className="text-xs text-slate-500 font-medium mb-2">{member.email}</p>

                                    {/* Access Visualization */}
                                    {/* Access Visualization */}
                                    <div className="flex flex-wrap gap-2">
                                        {[
                                            { id: 'patients', label: 'Patients' },
                                            { id: 'appointments', label: 'Appointments' },
                                            { id: 'treatments', label: 'Clinical', restricted: member.role_type === 'receptionist' },
                                            { id: 'pharmacy', label: 'Pharmacy', restricted: member.role_type === 'receptionist' },
                                            { id: 'billing_write', label: 'Billing', restricted: member.role_type === 'receptionist' }
                                        ].map(mod => {
                                            // Determine if allowed
                                            const isRestricted = mod.restricted;
                                            const userPerms = member.permissions || {};
                                            // If restricted by role -> Blocked
                                            if (isRestricted) return null; // Don't show hard-blocked items or show as disabled? 
                                            // Prompt: "Clearly SEE what access".
                                            // If Receptionist, they don't have Clinical. Showing it stroke-through is noisy. Just don't show it.

                                            // If explicitly disabled in permissions -> Gray out or Don't show?
                                            // "SELECT access they WANT to grant".
                                            // If I uncheck Pharmacy for Assistant, they shouldn't have it.
                                            // So if userPerms[mod.id] === false, it is denied.
                                            // Default is true (legacy).
                                            if (userPerms[mod.id] === false) return (
                                                <span key={mod.id} className="text-[10px] font-bold text-slate-300 bg-slate-50 px-2 py-1 rounded-md border border-slate-100 decoration-slate-400 line-through decoration-2 opacity-60" title="Access Revoked">
                                                    {mod.label}
                                                </span>
                                            );

                                            // Allowed
                                            return (
                                                <span key={mod.id} className="text-[10px] font-bold text-slate-500 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                                                    {mod.label}
                                                </span>
                                            );
                                        })}
                                        {member.role_type === 'receptionist' && (
                                            <span className="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-md border border-slate-100 italic">View Only: Billing</span>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="flex items-center gap-4 self-start">
                                <span className={`px-3 py-1 text-[10px] font-black uppercase rounded-full ${member.role_type === 'receptionist' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'
                                    }`}>
                                    {member.role_type || 'Assistant'}
                                </span>

                                <button
                                    onClick={() => openEditParams(member)}
                                    className="p-2 text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="Edit Details"
                                >
                                    <Edit2 size={16} />
                                </button>

                                <button
                                    onClick={() => handleDelete(member.id)}
                                    className="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Delete Account"
                                >
                                    <Trash2 size={16} />
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )
            }

            {
                showModal && (
                    <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                        <div className="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl">
                            <h3 className="text-2xl font-black text-slate-800 mb-6">{editMode ? 'Edit Team Member' : 'New Team Member'}</h3>
                            <form onSubmit={handleSave} className="space-y-4">
                                <input
                                    placeholder="Full Name"
                                    value={form.name}
                                    onChange={e => setForm({ ...form, name: e.target.value })}
                                    className="w-full p-4 bg-slate-50 rounded-2xl font-bold outline-none border border-transparent focus:border-slate-200 focus:bg-white transition-all"
                                    required
                                />
                                <input
                                    type="email"
                                    placeholder="Login Email"
                                    value={form.email}
                                    onChange={e => setForm({ ...form, email: e.target.value })}
                                    className="w-full p-4 bg-slate-50 rounded-2xl font-bold outline-none border border-transparent focus:border-slate-200 focus:bg-white transition-all"
                                    required
                                    disabled={editMode} // Prevent changing email as it's the ID mostly
                                />

                                <select
                                    value={form.role_type}
                                    onChange={e => setForm({ ...form, role_type: e.target.value })}
                                    className="w-full p-4 bg-slate-50 rounded-2xl font-bold outline-none border border-transparent focus:border-slate-200 focus:bg-white transition-all"
                                >
                                    <option value="assistant">Assistant (Clinical Access)</option>
                                    <option value="receptionist">Receptionist (Front Desk)</option>
                                </select>

                                <input
                                    type="tel"
                                    placeholder="Phone (Optional)"
                                    value={form.phone}
                                    onChange={e => setForm({ ...form, phone: e.target.value })}
                                    className="w-full p-4 bg-slate-50 rounded-2xl font-bold outline-none border border-transparent focus:border-slate-200 focus:bg-white transition-all"
                                />

                                <div>
                                    <input
                                        type="password"
                                        placeholder={editMode ? "New Password (Optional)" : "Setup Password"}
                                        value={form.password}
                                        onChange={e => setForm({ ...form, password: e.target.value })}
                                        className="w-full p-4 bg-slate-50 rounded-2xl font-bold outline-none border border-transparent focus:border-slate-200 focus:bg-white transition-all"
                                        required={!editMode}
                                    />
                                    {editMode && <p className="text-xs text-slate-400 mt-2 ml-2">Leave blank to keep current password.</p>}
                                </div>

                                {editMode && (
                                    <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl">
                                        <label className="flex-1 font-bold text-slate-700">Account Status</label>
                                        <button
                                            type="button"
                                            onClick={() => setForm({ ...form, is_active: !form.is_active })}
                                            className={`w-12 h-6 rounded-full p-1 transition-colors ${form.is_active ? 'bg-green-500' : 'bg-slate-300'}`}
                                        >
                                            <div className={`w-4 h-4 bg-white rounded-full transition-transform ${form.is_active ? 'translate-x-6' : 'translate-x-0'}`} />
                                        </button>
                                        <span className={`text-xs font-bold ${form.is_active ? 'text-green-600' : 'text-slate-400'}`}>
                                            {form.is_active ? 'ACTIVE' : 'DISABLED'}
                                        </span>
                                    </div>
                                )}

                                <div>
                                    <label className="block text-xs font-black text-slate-400 uppercase mb-2">Access Privileges</label>
                                    <div className="grid grid-cols-2 gap-2">
                                        {[
                                            { id: 'patients', label: 'Patients' },
                                            { id: 'appointments', label: 'Appointments' },
                                            { id: 'treatments', label: 'Treatments', restricted: form.role_type === 'receptionist' },
                                            { id: 'pharmacy', label: 'Pharmacy', restricted: form.role_type === 'receptionist' },
                                            { id: 'billing_write', label: 'Billing (Write)', restricted: form.role_type === 'receptionist' },
                                            { id: 'reports', label: 'Financial Reports', restricted: form.role_type === 'receptionist' },
                                        ].map(mod => (
                                            <label key={mod.id} className={`flex items-center gap-2 p-3 rounded-xl border transition-all ${mod.restricted ? 'bg-slate-50 border-slate-100 opacity-50 cursor-not-allowed' :
                                                form.permissions[mod.id] !== false ? 'bg-indigo-50 border-indigo-100 text-indigo-700' : 'bg-white border-slate-200 text-slate-500'
                                                }`}>
                                                <input
                                                    type="checkbox"
                                                    checked={form.permissions[mod.id] !== false}
                                                    disabled={mod.restricted}
                                                    onChange={(e) => {
                                                        const newPerms = { ...form.permissions, [mod.id]: e.target.checked };
                                                        setForm({ ...form, permissions: newPerms });
                                                    }}
                                                    className="w-4 h-4 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <span className="text-xs font-bold">{mod.label}</span>
                                            </label>
                                        ))}
                                    </div>
                                    {form.role_type === 'receptionist' && (
                                        <p className="text-[10px] text-orange-500 mt-2 font-bold px-1">
                                            Note: Clinical & Pharmacy modules are restricted for Receptionists by system policy.
                                        </p>
                                    )}
                                </div>

                                <div className="flex justify-end gap-3 mt-6">
                                    <button type="button" onClick={() => setShowModal(false)} className="px-6 py-3 font-bold text-slate-500">Cancel</button>
                                    <button type="submit" className="px-6 py-3 bg-slate-900 text-white font-bold rounded-xl">
                                        {editMode ? 'Save Changes' : 'Create Account'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )
            }
        </section >
    );
}
