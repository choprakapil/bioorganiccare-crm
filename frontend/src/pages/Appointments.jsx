import { useState, useEffect, useMemo, useCallback, memo } from 'react';
import api from '../api/axios';
import toast from 'react-hot-toast';
import {
    Calendar as CalendarIcon,
    List,
    Plus,
    Search,
    ChevronLeft,
    ChevronRight,
    Edit,
    Trash2,
    Clock,
    XCircle,
    AlertCircle
} from 'lucide-react';
import { format, startOfMonth, endOfMonth, eachDayOfInterval, isSameDay, addMonths, subMonths, isSameMonth } from 'date-fns';
import { useAuth } from '../context/AuthContext';

// --- Sub-Components (Defined first to avoid ReferenceError) ---

const TableView = memo(function TableView({ loading, appointments, onEdit, onDelete, onStatusUpdate, searchQuery, setSearchQuery }) {
    return (
        <div className="space-y-6">
            <div className="relative max-w-sm">
                <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" size={16} />
                <input
                    type="text"
                    placeholder="Search schedules..."
                    className="w-full pl-10 pr-4 py-2 text-sm rounded-full bg-slate-50 border-transparent focus:bg-white focus:border-slate-200 focus:ring-0 outline-none transition-all placeholder:text-slate-400 font-medium"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                />
            </div>

            <div className="w-full overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="border-b border-slate-100">
                                <th className="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Patient</th>
                                <th className="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Visit Time</th>
                                <th className="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Reason</th>
                                <th className="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Status</th>
                                <th className="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-50">
                            {loading ? (
                                <tr><td colSpan="5" className="px-4 py-12 text-center text-slate-400 animate-pulse text-xs font-medium tracking-wide">Synchronizing clinical data...</td></tr>
                            ) : appointments.length === 0 ? (
                                <tr>
                                    <td colSpan="5" className="px-4 py-20 text-center">
                                        <p className="text-xs font-bold text-slate-300 tracking-widest uppercase">No appointments yet</p>
                                    </td>
                                </tr>
                            ) : (
                                appointments.map((app) => (
                                    <tr key={app.id} className="hover:bg-slate-50/80 transition-all group">
                                        <td className="px-4 py-4">
                                            <div className="font-bold text-slate-800 text-sm">{app.patient?.name || 'Unknown Patient'}</div>
                                            <div className="text-[10px] text-slate-400 font-medium tracking-tight">{app.patient?.phone || 'No phone'}</div>
                                        </td>
                                        <td className="px-4 py-4">
                                            <div className="font-bold text-slate-700 text-sm tracking-tight">{format(new Date(app.appointment_date), 'MMM d, yyyy')}</div>
                                            <div className="text-[10px] text-slate-400 font-medium flex items-center gap-1">
                                                <Clock size={10} /> {format(new Date(app.appointment_date), 'hh:mm a')}
                                            </div>
                                        </td>
                                        <td className="px-4 py-4 text-xs text-slate-500 font-medium">
                                            {app.reason || 'Routine Visit'}
                                        </td>
                                        <td className="px-4 py-4">
                                            <select
                                                value={app.status}
                                                onChange={(e) => onStatusUpdate(app, e.target.value)}
                                                className={`text-[10px] font-black uppercase tracking-widest px-0 py-0 bg-transparent border-none outline-none cursor-pointer transition-all ${app.status === 'Completed' ? 'text-green-500' :
                                                    app.status === 'Cancelled' ? 'text-slate-300' :
                                                        'text-blue-500'
                                                    }`}
                                            >
                                                <option value="Scheduled">Scheduled</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </td>
                                        <td className="px-4 py-4 text-right">
                                            <div className="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button onClick={() => onEdit(app)} className="p-2 text-slate-300 hover:text-slate-600 transition-colors"><Edit size={14} /></button>
                                                <button onClick={() => onDelete(app.id)} className="p-2 text-slate-300 hover:text-red-400 transition-colors"><Trash2 size={14} /></button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
});

const CalendarView = memo(function CalendarView({ currentDate, setCurrentDate, appointments, onEdit }) {
    const monthStart = startOfMonth(currentDate);
    const monthEnd = endOfMonth(monthStart);
    const days = eachDayOfInterval({ start: monthStart, end: monthEnd });

    const groupedApps = useMemo(() => {
        const monthApps = appointments.filter(app => isSameMonth(new Date(app.appointment_date), currentDate));
        return monthApps.reduce((acc, app) => {
            const key = format(new Date(app.appointment_date), 'yyyy-MM-dd');
            if (!acc[key]) acc[key] = [];
            acc[key].push(app);
            return acc;
        }, {});
    }, [appointments, currentDate]);

    return (
        <div className="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div className="p-6 md:p-8 border-b border-slate-100 flex justify-between items-center text-center">
                <h3 className="text-xl md:text-2xl font-black text-slate-800 tracking-tight">{format(currentDate, 'MMMM yyyy')}</h3>
                <div className="flex gap-1 md:gap-2">
                    <button onClick={() => setCurrentDate(subMonths(currentDate, 1))} className="p-2 hover:bg-slate-50 rounded-xl transition-colors text-slate-400"><ChevronLeft size={20} /></button>
                    <button onClick={() => setCurrentDate(new Date())} className="px-3 py-1.5 md:px-4 md:py-2 text-[10px] md:text-xs font-bold text-primary bg-primary/5 rounded-xl hover:bg-primary/10 transition-colors uppercase tracking-widest">Today</button>
                    <button onClick={() => setCurrentDate(addMonths(currentDate, 1))} className="p-2 hover:bg-slate-50 rounded-xl transition-colors text-slate-400"><ChevronRight size={20} /></button>
                </div>
            </div>

            <div className="hidden md:block">
                <div className="grid grid-cols-7 border-b border-slate-50">
                    {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day => (
                        <div key={day} className="py-4 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">{day}</div>
                    ))}
                </div>

                <div className="grid grid-cols-7 auto-rows-[120px]">
                    {Array.from({ length: monthStart.getDay() }).map((_, i) => (
                        <div key={`pad-${i}`} className="border-r border-b border-slate-50 bg-slate-50/20"></div>
                    ))}

                    {days.map(day => {
                        const key = format(day, 'yyyy-MM-dd');
                        const dayAppointments = groupedApps[key] || [];
                        const isToday = isSameDay(day, new Date());

                        return (
                            <div key={day.toString()} className="border-r border-b border-slate-50 p-2 overflow-hidden hover:bg-slate-50 transition-colors relative">
                                <span className={`text-xs font-black ${isToday ? 'bg-primary text-white w-6 h-6 flex items-center justify-center rounded-full shadow-lg shadow-primary/30' : 'text-slate-400'}`}>
                                    {format(day, 'd')}
                                </span>
                                <div className="mt-2 space-y-1">
                                    {dayAppointments.slice(0, 3).map(app => (
                                        <div
                                            key={app.id}
                                            onClick={() => onEdit(app)}
                                            className={`text-[9px] font-bold px-1.5 py-0.5 rounded truncate cursor-pointer hover:bg-opacity-80 transition-all ${app.status === 'Completed' ? 'bg-green-50 text-green-700' :
                                                app.status === 'Cancelled' ? 'bg-slate-50 text-slate-300 line-through' :
                                                    'bg-blue-50 text-blue-700'
                                                }`}
                                        >
                                            {format(new Date(app.appointment_date), 'HH:mm')} {app.patient?.name}
                                        </div>
                                    ))}
                                    {dayAppointments.length > 3 && (
                                        <div className="text-[9px] font-bold text-slate-400 text-center">+{dayAppointments.length - 3} more</div>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            <div className="block md:hidden p-6">
                {Object.keys(groupedApps).length === 0 ? (
                    <div className="py-12 text-center">
                        <p className="text-xs font-bold text-slate-300 tracking-widest uppercase">No visits this month</p>
                    </div>
                ) : (
                    <div className="space-y-8">
                        {Object.entries(groupedApps)
                            .sort(([a], [b]) => a.localeCompare(b))
                            .map(([date, apps]) => (
                                <div key={date} className="space-y-4">
                                    <div className="flex items-center gap-3">
                                        <span className="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] whitespace-nowrap">
                                            {format(new Date(date), 'EEE, MMM d')}
                                        </span>
                                        <div className="h-[1px] w-full bg-slate-50"></div>
                                    </div>
                                    <div className="space-y-2">
                                        {apps.map(app => (
                                            <button
                                                key={app.id}
                                                onClick={() => onEdit(app)}
                                                className="w-full flex items-center justify-between p-4 rounded-2xl bg-slate-50/50 border border-slate-100 active:bg-slate-100 transition-all text-left"
                                            >
                                                <div>
                                                    <div className="text-sm font-bold text-slate-800">{app.patient?.name}</div>
                                                    <div className="text-[10px] font-medium text-slate-400">{app.reason || 'General Visit'}</div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-xs font-black text-primary">{format(new Date(app.appointment_date), 'hh:mm a')}</div>
                                                    <div className={`text-[9px] font-black uppercase tracking-widest ${app.status === 'Completed' ? 'text-green-500' :
                                                        app.status === 'Cancelled' ? 'text-slate-300' :
                                                            'text-blue-500'
                                                        }`}>{app.status}</div>
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            ))}
                    </div>
                )}
            </div>
        </div>
    );
});

const AppointmentModal = memo(function AppointmentModal({ patients, initialData, onClose, onSuccess }) {
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        patient_id: initialData?.patient_id || '',
        appointment_date: initialData ? format(new Date(initialData.appointment_date), "yyyy-MM-dd'T'HH:mm") : '',
        reason: initialData?.reason || '',
        status: initialData?.status || 'Scheduled'
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            if (initialData) {
                await api.put(`/appointments/${initialData.id}`, formData);
                toast.success('Appointment updated');
            } else {
                await api.post('/appointments', formData);
                toast.success('Appointment scheduled');
            }
            onSuccess();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to save appointment');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div className="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div className="p-8 border-b border-slate-100 flex justify-between items-center">
                    <h2 className="text-3xl font-black text-slate-800 tracking-tight">{initialData ? 'Edit Visit' : 'Schedule Visit'}</h2>
                    <button onClick={onClose} className="p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400"><XCircle /></button>
                </div>

                <form onSubmit={handleSubmit} className="p-8 space-y-6">
                    <div>
                        <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Select Patient</label>
                        <select
                            required
                            value={formData.patient_id}
                            onChange={(e) => setFormData({ ...formData, patient_id: e.target.value })}
                            className="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 focus:border-primary outline-none font-bold transition-all bg-slate-50"
                        >
                            <option value="">-- Choose Patient --</option>
                            {patients.map(p => (
                                <option key={p.id} value={p.id}>{p.name} ({p.phone || 'No phone'})</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Date & Time</label>
                        <input
                            required
                            type="datetime-local"
                            value={formData.appointment_date}
                            onChange={(e) => setFormData({ ...formData, appointment_date: e.target.value })}
                            className="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 focus:border-primary outline-none font-bold transition-all bg-slate-50"
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Reason for Visit</label>
                        <textarea
                            value={formData.reason}
                            placeholder="e.g. Regular Checkup, Toothache, Root Canal"
                            onChange={(e) => setFormData({ ...formData, reason: e.target.value })}
                            className="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 focus:border-primary outline-none font-medium transition-all bg-slate-50 h-32"
                        />
                    </div>

                    {initialData && (
                        <div>
                            <label className="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
                            <select
                                value={formData.status}
                                onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                                className="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 focus:border-primary outline-none font-bold transition-all bg-slate-50"
                            >
                                <option value="Scheduled">Scheduled</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    )}

                    <div className="pt-4 flex gap-3">
                        <button type="button" onClick={onClose} className="flex-1 py-4 font-bold text-slate-400 hover:bg-slate-50 rounded-2xl transition-all">Cancel</button>
                        <button
                            disabled={loading}
                            type="submit"
                            className="flex-[2] py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark transition-all shadow-xl shadow-primary/20 disabled:opacity-50"
                        >
                            {loading ? 'Processing...' : (initialData ? 'Update Schedule' : 'Confirm Appointment')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
});

// --- Main Page Component ---

export default function Appointments() {
    const { user } = useAuth();
    const [view, setView] = useState('table'); // 'table' or 'calendar'
    const [appointments, setAppointments] = useState([]);
    const [patients, setPatients] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingAppointment, setEditingAppointment] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [debouncedSearchQuery, setDebouncedSearchQuery] = useState('');
    const [currentDate, setCurrentDate] = useState(new Date());

    // Debounce search query
    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearchQuery(searchQuery);
        }, 300);
        return () => clearTimeout(handler);
    }, [searchQuery]);

    // Subscription Limits
    const maxAppointments = user?.plan?.max_appointments_monthly || 0;
    const isUnlimited = user?.plan?.max_appointments_monthly === -1 || !user?.plan?.max_appointments_monthly;

    // Memoize current month count
    const currentMonthAppointments = useMemo(() => {
        return appointments.filter(app => {
            const date = new Date(app.appointment_date);
            return isSameMonth(date, new Date());
        }).length;
    }, [appointments]);

    const usagePercent = isUnlimited ? 0 : (currentMonthAppointments / maxAppointments) * 100;
    const isOverLimit = !isUnlimited && currentMonthAppointments >= maxAppointments;

    const fetchData = useCallback(async () => {
        setLoading(true);
        try {
            const [appRes, patRes] = await Promise.all([
                api.get('/appointments'),
                api.get('/patients')
            ]);
            setAppointments(Array.isArray(appRes.data) ? appRes.data : appRes.data?.data ?? []);
            setPatients(Array.isArray(patRes.data) ? patRes.data : patRes.data?.data ?? []);
        } catch (err) {
            toast.error('Failed to load dashboard data');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    const handleDelete = useCallback(async (id) => {
        if (!confirm('Are you sure you want to delete this appointment?')) return;
        try {
            await api.delete(`/admin/delete/appointment/${id}/archive`);
            toast.success('Appointment deleted');
            fetchData();
        } catch (err) {
            toast.error('Delete failed');
        }
    }, [fetchData]);

    const handleStatusUpdate = useCallback(async (app, status) => {
        try {
            await api.put(`/appointments/${app.id}`, { status });
            toast.success(`Status updated to ${status}`);
            fetchData();
        } catch (err) {
            toast.error('Update failed');
        }
    }, [fetchData]);

    const filteredAppointments = useMemo(() => {
        if (!Array.isArray(appointments)) return [];
        const query = debouncedSearchQuery.toLowerCase();
        return appointments.filter(app =>
            app.patient?.name?.toLowerCase().includes(query) ||
            app.reason?.toLowerCase().includes(query)
        );
    }, [appointments, debouncedSearchQuery]);

    const analytics = useMemo(() => {
        const now = new Date();
        let todayCount = 0;
        let monthlyTotal = 0;
        let monthlyCompleted = 0;
        let monthlyCancelled = 0;
        let nextUpcoming = null;

        if (Array.isArray(appointments)) {
            appointments.forEach(app => {
                const date = new Date(app.appointment_date);

                // Today
                if (isSameDay(date, now)) {
                    todayCount++;
                }

                // Current Month
                if (isSameMonth(date, now)) {
                    monthlyTotal++;
                    if (app.status === "Completed") monthlyCompleted++;
                    if (app.status === "Cancelled") monthlyCancelled++;
                }

                // Next upcoming (Scheduled and in future)
                if (app.status === "Scheduled" && date > now) {
                    if (!nextUpcoming || date < new Date(nextUpcoming.appointment_date)) {
                        nextUpcoming = app;
                    }
                }
            });
        }

        const resolvedVisits = monthlyCompleted + monthlyCancelled;

        return {
            todayCount,
            monthlyTotal,
            monthlyCompleted,
            monthlyCancelled,
            monthlyCompletionRate: resolvedVisits === 0 ? 0 : Math.round((monthlyCompleted / resolvedVisits) * 100),
            monthlyCancellationRate: resolvedVisits === 0 ? 0 : Math.round((monthlyCancelled / resolvedVisits) * 100),
            nextUpcoming
        };
    }, [appointments]);

    const handleOpenSchedule = useCallback(() => {
        setEditingAppointment(null);
        setShowModal(true);
    }, []);

    const handleEdit = useCallback((app) => {
        setEditingAppointment(app);
        setShowModal(true);
    }, []);

    const handleCloseModal = useCallback(() => {
        setShowModal(false);
    }, []);

    const handleModalSuccess = useCallback(() => {
        setShowModal(false);
        fetchData();
    }, [fetchData]);

    return (
        <div className="space-y-12">
            <header className="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                <div>
                    <h1 className="text-4xl font-extrabold text-slate-800 tracking-tight">Appointments</h1>
                    <p className="text-slate-500 font-medium mt-1">Coordinate your clinical schedule and patient visits.</p>
                </div>

                <div className="flex items-center gap-3 w-full md:w-auto">
                    <div className="bg-slate-100 p-1 rounded-xl flex gap-1">
                        <button
                            onClick={() => setView('table')}
                            className={`p-2 rounded-lg transition-all ${view === 'table' ? 'bg-white text-primary' : 'text-slate-400'}`}
                        >
                            <List size={20} />
                        </button>
                        <button
                            onClick={() => setView('calendar')}
                            className={`p-2 rounded-lg transition-all ${view === 'calendar' ? 'bg-white text-primary' : 'text-slate-400'}`}
                        >
                            <CalendarIcon size={20} />
                        </button>
                    </div>

                    <button
                        onClick={handleOpenSchedule}
                        disabled={isOverLimit}
                        className={`px-8 py-3.5 rounded-xl font-bold flex items-center gap-2 transition-all ${isOverLimit ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-primary text-white hover:bg-primary-dark'}`}
                    >
                        <Plus size={20} />
                        Schedule
                    </button>
                </div>
            </header>

            {/* Analytics Preview Panel */}
            <div className="flex flex-wrap items-center gap-x-12 gap-y-6 py-6 border-b border-slate-100">
                {analytics.monthlyTotal > 0 || analytics.todayCount > 0 ? (
                    <>
                        <div className="flex flex-col">
                            <span className="text-xs text-slate-500 mb-1">Daily load</span>
                            <span className="text-sm font-semibold text-slate-900">{analytics.todayCount} visits</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs text-slate-500 mb-1">Monthly total</span>
                            <span className="text-sm font-semibold text-slate-900">{analytics.monthlyTotal} items</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs text-slate-500 mb-1">Efficiency</span>
                            <span className="text-sm font-semibold text-primary">{analytics.monthlyCompletionRate}% Completion</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="text-xs text-slate-500 mb-1">Attrition</span>
                            <span className="text-sm font-semibold text-slate-400">{analytics.monthlyCancellationRate}% Cancelled</span>
                        </div>
                        <div className="flex flex-col flex-1 min-w-[240px]">
                            <span className="text-xs text-slate-500 mb-1">Next patient</span>
                            <span className="text-sm font-semibold text-primary truncate">
                                {analytics.nextUpcoming
                                    ? `${analytics.nextUpcoming.patient?.name} · ${format(new Date(analytics.nextUpcoming.appointment_date), 'hh:mm a')}`
                                    : 'No future visits'
                                }
                            </span>
                        </div>
                    </>
                ) : (
                    <div className="w-full py-2">
                        <p className="text-xs text-slate-400 font-medium">No activity recorded for this period.</p>
                    </div>
                )}
            </div>

            {!isUnlimited && (
                <div className="flex flex-col items-center gap-6 py-4">
                    <div className="text-center">
                        <p className="text-[11px] font-bold text-slate-400 mb-2">Monthly patient allocation</p>
                        <div className="flex items-baseline justify-center gap-1">
                            <span className={`text-3xl font-black tracking-tighter ${usagePercent >= 80 ? 'text-orange-500' : 'text-slate-900'}`}>{currentMonthAppointments}</span>
                            <span className="text-slate-300 font-bold">/</span>
                            <span className="text-slate-400 font-bold">{maxAppointments}</span>
                        </div>
                    </div>

                    <div className="w-full max-w-md h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div
                            className={`h-full transition-all duration-1000 ease-out ${usagePercent >= 90 ? 'bg-red-400/80' :
                                usagePercent >= 70 ? 'bg-orange-400/80' :
                                    'bg-primary/80'
                                }`}
                            style={{ width: `${Math.min(usagePercent, 100)}%` }}
                        ></div>
                    </div>

                    {usagePercent >= 80 && (
                        <div className={`flex items-center gap-2 px-4 py-1.5 rounded-full border text-[10px] font-black uppercase tracking-widest ${isOverLimit ? 'bg-red-50 text-red-500 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100'}`}>
                            <AlertCircle size={12} />
                            {isOverLimit ? 'Limit Reached' : 'Approaching Limit'}
                        </div>
                    )}
                </div>
            )}

            {view === 'table' ? (
                <TableView
                    loading={loading}
                    appointments={filteredAppointments}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    onStatusUpdate={handleStatusUpdate}
                    searchQuery={searchQuery}
                    setSearchQuery={setSearchQuery}
                />
            ) : (
                <CalendarView
                    currentDate={currentDate}
                    setCurrentDate={setCurrentDate}
                    appointments={filteredAppointments}
                    onEdit={handleEdit}
                />
            )}

            {showModal && (
                <AppointmentModal
                    patients={patients}
                    initialData={editingAppointment}
                    onClose={handleCloseModal}
                    onSuccess={handleModalSuccess}
                />
            )}
        </div>
    );
}
