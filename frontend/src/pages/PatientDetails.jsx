import { useState, useEffect, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../api/axios';
import { API_BASE_URL } from '../api/axios';
import { useAuth } from '../context/AuthContext';
import toast from 'react-hot-toast';
import { handleApiError } from '../utils/errorHandler';
import { ChevronLeft, Plus, History, CheckCircle2, ClipboardIcon, Activity, CreditCard, AlertTriangle, Pill, X, ShieldAlert, Download, Printer, MessageSquare } from 'lucide-react';
import DentalChartSVG from '../components/DentalChartSVG';
import QuantityController from '../components/QuantityController';
import MedicineSelector from '../components/MedicineSelector';
import ServiceSelector from '../components/ServiceSelector';
import ToothHistoryPanel from '../components/ToothHistoryPanel';

export default function PatientDetails() {
    const { id } = useParams();
    const { user, can, isPro } = useAuth();
    const isReceptionist =
        user?.role === 'staff' &&
        user?.role_type === 'receptionist';
    const [patient, setPatient] = useState(null);
    const [treatments, setTreatments] = useState([]);
    const [invoices, setInvoices] = useState([]);
    const [inventory, setInventory] = useState([]);
    const [catalog, setCatalog] = useState([]);
    const [loading, setLoading] = useState(true);
    const [approvingId, setApprovingId] = useState(null);
    const [showAddModal, setShowAddModal] = useState(false);
    const [saving, setSaving] = useState(false);

    const [selectedTreatments, setSelectedTreatments] = useState([]);
    const [selectedMedicines, setSelectedMedicines] = useState([]);
    const [specialty, setSpecialty] = useState(null);
    const [selectedInvoice, setSelectedInvoice] = useState(null);
    const [showInvoiceModal, setShowInvoiceModal] = useState(false);
    const [appointments, setAppointments] = useState([]);
    const [nextAppointment, setNextAppointment] = useState({ date: '', time: '', notes: '' });

    // Teeth local state for dental
    const [selectedTeeth, setSelectedTeeth] = useState([]);
    const [activeTooth, setActiveTooth] = useState(null);
    const [toothServices, setToothServices] = useState({});
    const [selectedToothHistory, setSelectedToothHistory] = useState([]);
    const [billingData, setBillingData] = useState({ discount: 0, payment_method: 'Cash', paid_amount: 0, payment_status: 'Paid' });

    // Form
    const [formData, setFormData] = useState({
        procedure_name: '',
        catalog_id: '',
        ui_id: '',
        inventory_id: '',
        notes: '',
        status: 'Proposed',
        fee: 0
    });

    useEffect(() => {
        fetchData();
    }, [id]);

    const fetchData = async () => {
        try {
            const results = await Promise.allSettled([
                api.get(`/patients/${id}`),
                api.get(`/patients/${id}/treatments`),
                api.get('/clinical-catalog?view=my-services'),
                api.get(`/invoices?patient_id=${id}`),
                api.get(`/appointments?patient_id=${id}`),
                !isReceptionist
                    ? api.get('/inventory')
                    : Promise.resolve({ data: [] })
            ]);

            const [pRes, tRes, locRes, iRes, appRes, invRes] = results;

            // 1. Critical: Patient Data
            if (pRes.status === 'fulfilled') {
                setPatient(pRes.value.data);
            } else {
                throw new Error('Patient core data unreachable');
            }

            // 2. Non-Critical: Clinical Data
            if (tRes.status === 'fulfilled') setTreatments(tRes.value.data);

            // 3. Catalogs & Assets
            const services = locRes.status === 'fulfilled'
                ? locRes.value.data.map(s => ({
                    ...s,
                    react_key: `${s.is_local ? 'local' : 'global'}_${s.id}`,
                    ui_id: `${s.is_local ? 'local' : 'global'}_${s.id}`
                }))
                : [];
            setCatalog(services);

            if (iRes.status === 'fulfilled') {
                const invoicePayload = iRes.value.data;
                setInvoices(
                    Array.isArray(invoicePayload)
                        ? invoicePayload
                        : invoicePayload.data || []
                );
            }

            if (appRes.status === 'fulfilled') {
                setAppointments(appRes.value.data.data || []);
            }

            if (invRes.status === 'fulfilled') {
                const inventoryPayload = invRes.value.data;
                const finalInventory = Array.isArray(inventoryPayload)
                    ? inventoryPayload
                    : inventoryPayload.data || [];
                setInventory(finalInventory);
            }

            // Get specialty info
            if (user?.specialty) {
                setSpecialty(user.specialty);
            }
        } catch (err) {
            toast.error('Failed to load complete patient history');
        } finally {
            setLoading(false);
        }
    };

    const visitList = useMemo(() => {
        const visits = {};
        treatments.forEach(t => {
            const visitKey = t.invoice_id
                ? `invoice_${t.invoice_id}`
                : `draft_${new Date(t.created_at).toISOString().slice(0, 16)}`;

            if (!visits[visitKey]) {
                visits[visitKey] = {
                    invoice_id: t.invoice_id,
                    created_at: t.created_at,
                    treatments: [],
                    medicines: [],
                    next_appointment: null
                };
            }

            if (t.inventory_id) {
                visits[visitKey].medicines.push(t);
            } else {
                visits[visitKey].treatments.push(t);
            }
        });

        // Associate appointments with visits based on creation time (within 5 mins)
        appointments.forEach(a => {
            const appCreatedAt = new Date(a.created_at).getTime();
            Object.values(visits).forEach(v => {
                const visitCreatedAt = new Date(v.created_at).getTime();
                if (Math.abs(appCreatedAt - visitCreatedAt) < 5 * 60 * 1000) {
                    v.next_appointment = a;
                }
            });
        });

        return Object.values(visits).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }, [treatments, appointments]);

    const toothHistory = useMemo(() => {
        const map = {};
        treatments.forEach(t => {
            if (!t.teeth) return;
            const ids = t.teeth.split(',').map(id => id.trim());
            ids.forEach(id => {
                if (!map[id]) map[id] = [];
                map[id].push({
                    procedure: t.procedure_name,
                    fee: t.fee,
                    date: t.created_at,
                    status: t.status
                });
            });
        });
        return map;
    }, [treatments]);

    const upcomingAppointment = useMemo(() => {
        if (!appointments.length) return null;
        const now = new Date();
        return appointments
            .filter(a => new Date(a.appointment_date) > now && a.status === 'Scheduled')
            .sort((a, b) => new Date(a.appointment_date) - new Date(b.appointment_date))[0];
    }, [appointments]);

    const treatmentPlan = useMemo(() => {
        const planned = treatments.filter(t => t.status === 'Planned');
        const completed = treatments.filter(t => t.status === 'Completed');
        return { planned, completed };
    }, [treatments]);

    const stats = useMemo(() => {
        const totalValue = invoices.reduce((sum, i) => sum + Number(i.total_amount || 0), 0);
        const totalPaid = invoices.reduce((sum, i) => sum + Number(i.paid_amount || 0), 0);
        
        const financial = {
            totalValue,
            totalPaid,
            totalPending: invoices.reduce((sum, i) => sum + Number(i.balance_due || 0), 0),
            totalDiscount: invoices.reduce((sum, i) => sum + Number(i.discount_amount || 0), 0),
            collectionRate: totalValue > 0 ? ((totalPaid / totalValue) * 100).toFixed(0) : 0
        };

        const visitDates = treatments.map(t => new Date(t.created_at)).sort((a, b) => a - b);
        let totalGap = 0;
        for (let i = 1; i < visitDates.length; i++) {
            totalGap += (visitDates[i] - visitDates[i - 1]);
        }

        const clinical = {
            totalProcedures: treatments.filter(t => !t.inventory_id).length,
            totalMedicines: treatments.filter(t => t.inventory_id).length,
            totalVisits: [...new Set(treatments.filter(t => t.invoice_id).map(t => t.invoice_id))].length,
            totalTeethTreated: new Set(treatments.filter(t => t.teeth).flatMap(t => t.teeth.split(',').map(s => s.trim()))).size,
            firstVisit: visitDates.length > 0 ? visitDates[0] : null,
            lastVisit: visitDates.length > 0 ? visitDates[visitDates.length - 1] : null,
            avgVisitGap: visitDates.length > 1 ? Math.round(totalGap / (visitDates.length - 1) / (1000 * 60 * 60 * 24)) : 0
        };

        return { financial, clinical };
    }, [invoices, treatments]);

    const subtotal = Object.values(toothServices).flat().reduce((sum, s) => sum + Number(s.billed_price || s.default_fee || 0), 0) +
                   selectedMedicines.reduce((sum, m) => sum + (m.unit_price * m.quantity), 0);
    const finalTotal = Math.max(0, subtotal - (billingData.discount || 0));

    useEffect(() => {
        if (billingData.payment_status === 'Paid') {
            setBillingData(prev => ({ ...prev, paid_amount: finalTotal }));
        } else if (billingData.payment_status === 'Unpaid') {
            setBillingData(prev => ({ ...prev, paid_amount: 0 }));
        }
    }, [finalTotal, billingData.payment_status]); // Depend on finalTotal instead of subtotal and discount separately

    const handleGenerateInvoice = async () => {
        if (selectedTreatments.length === 0) {
            toast.error('Select at least one procedure to bill');
            return;
        }
        try {
            await api.post('/invoices', {
                patient_id: id,
                treatment_ids: selectedTreatments
            });
            toast.success('Invoice generated successfully');
            setSelectedTreatments([]);
            fetchData();
        } catch (err) {
            return;
        }
    };

    const toggleService = (service) => {
        if (selectedTeeth.length === 0) {
            toast.error('Select at least one tooth on the chart');
            return;
        }

        setToothServices(prev => {
            const updated = { ...prev };
            
            selectedTeeth.forEach(tooth => {
                const currentTeethServices = updated[tooth] || [];
                const exists = currentTeethServices.find(s => s.react_key === service.react_key);

                if (exists) {
                    updated[tooth] = currentTeethServices.filter(s => s.react_key !== service.react_key);
                } else {
                    updated[tooth] = [...currentTeethServices, { ...service, billed_price: service.default_fee }];
                }
            });

            return updated;
        });
    };


    const handlePriceChange = (reactKey, newPrice) => {
        if (!activeTooth) return;
        setToothServices(prev => ({
            ...prev,
            [activeTooth]: (prev[activeTooth] || []).map(s =>
                s.react_key === reactKey ? { ...s, billed_price: newPrice } : s
            )
        }));
    };


    const handleProcedureSelect = (itemId) => {
        if (!itemId) return;
        
        const item = catalog.find(c => c.react_key === itemId);
        if (item) {
            setFormData(prev => ({
                ...prev,
                catalog_id: item.react_key.startsWith('local_') ? item.react_key : item.id,
                ui_id: item.react_key,
                procedure_name: item.item_name,
                fee: item.default_fee
            }));
        }
    };

    const addMedicine = (id) => {
        const item = inventory.find(i => i.id == id);
        if (!item) return;
        
        if (item.stock < 1) {
            toast.error(`${item.item_name} is out of stock`);
            return;
        }

        setSelectedMedicines(prev => {
            const exists = prev.find(m => m.id === item.id);
            if (exists) {
                if (exists.quantity >= item.stock) {
                    toast.error(`Only ${item.stock} units available`);
                    return prev;
                }
                return prev.map(m => m.id === item.id ? { ...m, quantity: m.quantity + 1 } : m);
            }
            return [...prev, { ...item, quantity: 1, unit_price: item.sale_price }];
        });
    };

    const updateMedicinePrice = (id, price) => {
        setSelectedMedicines(prev => prev.map(m => m.id === id ? { ...m, unit_price: Number(price) } : m));
    };

    const removeMedicine = (id) => {
        setSelectedMedicines(prev => prev.filter(m => m.id !== id));
    };

    const updateMedicineQuantity = (id, q) => {
        const item = inventory.find(i => i.id == id);
        if (q > item?.stock) {
            toast.error(`Max ${item.stock} available`);
            return;
        }
        setSelectedMedicines(prev => prev.map(m => m.id === id ? { ...m, quantity: Math.max(1, q) } : m));
    };

    const handleSaveTreatment = async (e) => {
        if (e) e.preventDefault();
        setSaving(true);
        try {
            const allSavedIds = [];
            const completedSavedIds = [];

            // 1. Save Procedures with Tooth Mapping
            const teethWithServices = Object.keys(toothServices).filter(t => toothServices[t].length > 0);
            
            for (const tooth of teethWithServices) {
                for (const s of toothServices[tooth]) {
                    const payload = {
                        patient_id: id,
                        catalog_id: s.react_key.startsWith('local_') ? s.react_key : s.id,
                        procedure_name: s.item_name,
                        fee: s.billed_price || s.default_fee,
                        status: formData.status,
                        notes: formData.notes,
                        teeth: tooth.toString()
                    };
                    const res = await api.post('/treatments', payload);
                    if (res.data?.id) {
                        allSavedIds.push(res.data.id);
                        if (payload.status === 'Completed') completedSavedIds.push(res.data.id);
                    }
                }
            }

            // 2. Save Medicines (Always Completed)
            for (const med of selectedMedicines) {
                const payload = {
                    patient_id: id,
                    inventory_id: med.id,
                    procedure_name: med.item_name,
                    fee: med.unit_price || med.sale_price,
                    quantity: med.quantity,
                    status: 'Completed',
                    notes: formData.notes
                };
                const res = await api.post('/treatments', payload);
                if (res.data?.id) {
                    allSavedIds.push(res.data.id);
                    completedSavedIds.push(res.data.id);
                }
            }

            if (allSavedIds.length === 0) {
                toast.error('Add at least one service or medicine');
                setSaving(false);
                return;
            }

            // 3. Automated Invoicing & Payment (ONLY for completed)
            if (completedSavedIds.length > 0) {
                const invoicePayload = {
                    patient_id: id,
                    treatment_ids: completedSavedIds,
                    discount_amount: billingData.discount,
                    paid_amount: billingData.paid_amount,
                    payment_method: billingData.payment_method,
                    payment_status: billingData.payment_status,
                    status: billingData.payment_status
                };
                await api.post('/invoices', invoicePayload);
            }

            // 4. Save Next Appointment if scheduled
            if (nextAppointment.date) {
                const appPayload = {
                    patient_id: id,
                    appointment_date: `${nextAppointment.date} ${nextAppointment.time || '10:00'}`,
                    notes: nextAppointment.notes,
                    status: 'Scheduled'
                };
                await api.post('/appointments', appPayload);
                setNextAppointment({ date: '', time: '', notes: '' });
            }

            toast.success('Clinical records saved & invoice generated');
            setShowAddModal(false);
            setSelectedTeeth([]);
            setActiveTooth(null);
            setToothServices({});
            setSelectedMedicines([]);
            setBillingData({ discount: 0, payment_method: 'Cash', paid_amount: 0, payment_status: 'Paid' });
            setFormData({ procedure_name: '', catalog_id: '', ui_id: '', inventory_id: '', notes: '', status: 'Proposed', fee: 0, quantity: 1 });
            fetchData();
        } catch (err) {
            toast.error('Failed to save record');
        } finally {
            setSaving(false);
        }
    };


    const handleApproveReallocation = async (invoiceId) => {
        setApprovingId(invoiceId);
        const t = toast.loading('Processing stock reallocation...');
        try {
            await api.post(`/invoices/${invoiceId}/approve-reallocation`);
            toast.success('Reallocation approved and stock bound', { id: t });
            fetchData();
        } catch (err) {
            handleApiError(err, 'Reallocation failed');
            toast.dismiss(t);
        } finally {
            setApprovingId(null);
        }
    };

    const openInvoice = async (invoiceId) => {
        const t = toast.loading('Fetching invoice details...');
        try {
            const res = await api.get(`/invoices/${invoiceId}`);
            setSelectedInvoice(res.data);
            setShowInvoiceModal(true);
            toast.dismiss(t);
        } catch (err) {
            toast.error('Failed to load invoice items', { id: t });
        }
    };

    const handleWhatsAppShare = () => {
        if (!selectedInvoice || !patient) return;
        const phoneNumber = patient.phone.replace(/[^0-9]/g, '');
        const publicUrl = `${window.location.origin}/invoice/${selectedInvoice.invoice.uuid}`;
        const message = encodeURIComponent(`Hello ${patient.name}, here is your dental invoice INV-${selectedInvoice.invoice.id} from ${selectedInvoice.invoice.doctor?.clinic_name || 'DentFlow CRM'}.\n\nTotal Payable: ₹${selectedInvoice.invoice.total_amount}\nPaid: ₹${selectedInvoice.invoice.paid_amount}\nBalance Pending: ₹${selectedInvoice.invoice.balance_due}\n\nView Digital Statement: ${publicUrl}`);
        window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
    };

    const handleDownloadPdf = () => {
        if (!selectedInvoice) return;
        window.open(`${API_BASE_URL}/public/invoices/${selectedInvoice.invoice.uuid}/pdf`, '_blank');
    };



    if (loading) return <div className="p-8 text-center text-slate-500 font-medium">Loading patient records...</div>;

    if (!patient) {
        return (
            <div className="p-8 text-center bg-red-50 text-red-600 rounded-2xl border border-red-100 m-6">
                <ShieldAlert className="w-12 h-12 mx-auto mb-2 opacity-50" />
                <h3 className="text-lg font-bold">Unable to load patient details</h3>
                <p className="text-sm opacity-80 mb-4">The record may not exist or implies restricted access.</p>
                <Link to="/patients" className="inline-block px-4 py-2 bg-white border border-red-200 rounded-lg text-sm font-bold text-red-700 hover:bg-red-50">Back to Registry</Link>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <Link to="/patients" className="flex items-center gap-2 text-slate-500 hover:text-primary transition-all font-semibold mb-4">
                <ChevronLeft size={20} /> Back to Registry
            </Link>

            <div className="flex justify-between items-start bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                <div className="flex gap-6 items-center">
                    <div className="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-2xl font-black">
                        {patient?.name?.charAt(0) || '?'}
                    </div>
                    <div>
                        <h1 className="text-3xl font-black text-slate-800">{patient?.name || 'Unknown Patient'}</h1>
                        <p className="text-slate-500 font-medium">{patient?.age || 'N/A'}Y • {patient?.gender || 'N/A'} • {patient?.phone || 'N/A'}</p>
                    </div>
                </div>
                {!isReceptionist && (
                    <button
                        onClick={() => setShowAddModal(true)}
                        className="bg-primary text-white px-6 py-4 rounded-2xl font-black flex items-center gap-2 hover:bg-primary-dark transition-all shadow-xl shadow-primary/20"
                    >
                        <Plus size={20} /> Record Treatment
                    </button>
                )}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Treatment History */}
                <div className="lg:col-span-2 space-y-4">
                    <div className="flex justify-between items-center px-2">
                        <div className="flex items-center gap-2 text-slate-600 font-bold uppercase tracking-wider text-xs">
                            <History size={14} /> Clinical Timeline
                        </div>
                        {selectedTreatments.length > 0 && can('billing') && (
                            <button
                                onClick={handleGenerateInvoice}
                                className="bg-green-600 text-white px-4 py-2 rounded-xl text-xs font-black shadow-lg shadow-green-600/20 hover:bg-green-700 transition-all flex items-center gap-2"
                            >
                                <CreditCard size={14} /> Bill {selectedTreatments.length} Selected
                            </button>
                        )}
                    </div>

                    {visitList.length === 0 ? (
                        <div className="bg-slate-50 border-2 border-dashed border-slate-100 rounded-3xl p-12 text-center text-slate-400 italic">
                            No treatments recorded yet.
                        </div>
                    ) : (
                        visitList.map((visit, idx) => {
                            const inv = invoices.find(i => i.id === visit.invoice_id);
                            const procedureCount = visit.treatments.length;
                            const medicineCount = visit.medicines.length;

                            return (
                                <div key={idx} className="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm hover:shadow-md transition-all">
                                    <div className="flex justify-between items-center mb-6 pb-6 border-b border-slate-50">
                                        <div className="flex items-center gap-4">
                                            <div className="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white">
                                                <History size={20} />
                                            </div>
                                            <div>
                                                <p className="text-xl font-black text-slate-800 tracking-tight">Visit Statement</p>
                                                <p className="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none mt-1">
                                                    {new Date(visit.created_at).toLocaleDateString(undefined, { dateStyle: 'long' })} at {new Date(visit.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                </p>
                                                <div className="flex items-center gap-2 mt-3">
                                                    <span className="text-[9px] font-black bg-slate-100 text-slate-500 px-2 py-1 rounded-md uppercase tracking-tighter">
                                                        {procedureCount} {procedureCount === 1 ? 'Procedure' : 'Procedures'}
                                                    </span>
                                                    <span className="text-[9px] font-black bg-slate-100 text-slate-500 px-2 py-1 rounded-md uppercase tracking-tighter">
                                                        {medicineCount} {medicineCount === 1 ? 'Medicine' : 'Medicines'}
                                                    </span>
                                                    {inv && (
                                                        <>
                                                            <span className="text-[9px] font-black bg-primary/10 text-primary px-2 py-1 rounded-md uppercase tracking-tighter">
                                                                Total: ₹{parseFloat(inv.total_amount).toLocaleString()}
                                                            </span>
                                                            <span className={`text-[9px] font-black px-2 py-1 rounded-md uppercase tracking-tighter ${
                                                                inv.status === 'Paid' ? 'bg-emerald-100 text-emerald-600' :
                                                                inv.status === 'Partial' ? 'bg-amber-100 text-amber-600' :
                                                                'bg-red-100 text-red-600'
                                                            }`}>
                                                                {inv.status}
                                                            </span>
                                                        </>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        {visit.invoice_id && (
                                            <div className="text-right">
                                                <p className="text-[10px] font-black text-primary uppercase tracking-[0.2em]">Billed Under</p>
                                                <button 
                                                    onClick={() => openInvoice(visit.invoice_id)}
                                                    className="text-lg font-black text-slate-800 hover:text-primary transition-colors"
                                                >
                                                    #INV-{visit.invoice_id}
                                                </button>
                                            </div>
                                        )}
                                    </div>

                                <div className="space-y-6">
                                    {visit.treatments.length > 0 && (
                                        <div>
                                            <h4 className="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                                <Activity size={12} /> Clinical Procedures
                                            </h4>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {visit.treatments.map((t) => (
                                                    <div key={t.id} className="bg-slate-50/50 p-4 rounded-2xl border border-slate-50 flex items-center gap-4 group">
                                                        <div className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${t.status === 'Completed' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'}`}>
                                                            {t.status === 'Completed' ? <CheckCircle2 size={18} /> : <Activity size={18} />}
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <p className="font-bold text-slate-800 text-sm truncate">{t.procedure_name}</p>
                                                            <div className="flex items-center gap-2 mt-0.5">
                                                                {t.teeth && <span className="text-[10px] font-black text-primary uppercase">Tooth: {t.teeth}</span>}
                                                                <span className="text-[10px] font-bold text-slate-400">₹{t.fee}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    {visit.medicines.length > 0 && (
                                        <div className="pt-6 border-t border-slate-50">
                                            <h4 className="text-[10px] font-black text-purple-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                                <Pill size={12} /> Pharmacy Log
                                            </h4>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {visit.medicines.map((m) => (
                                                    <div key={m.id} className="bg-purple-50/20 p-4 rounded-2xl border border-purple-50 flex items-center gap-4">
                                                        <div className="w-10 h-10 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center shrink-0">
                                                            <Pill size={18} />
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <p className="font-bold text-slate-800 text-sm truncate">{m.procedure_name}</p>
                                                            <div className="flex items-center gap-2 mt-0.5">
                                                                <span className="text-[10px] font-black text-purple-500 uppercase">Qty: {m.quantity} Unit(s)</span>
                                                                <span className="text-[10px] font-bold text-slate-400">₹{m.fee}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    {visit.next_appointment && (
                                        <div className="pt-6 border-t border-slate-50">
                                            <h4 className="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                                <Activity size={12} /> Follow-up Scheduled
                                            </h4>
                                            <div className="bg-blue-50/50 p-4 rounded-2xl border border-blue-50 flex items-center justify-between">
                                                <div className="flex items-center gap-4">
                                                    <div className="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                                                        <Activity size={18} />
                                                    </div>
                                                    <div>
                                                        <p className="font-bold text-slate-800 text-sm">
                                                            {new Date(visit.next_appointment.appointment_date).toLocaleDateString(undefined, { dateStyle: 'medium' })}
                                                        </p>
                                                        <p className="text-[10px] font-black text-blue-500 uppercase">
                                                            {new Date(visit.next_appointment.appointment_date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                        </p>
                                                    </div>
                                                </div>
                                                {visit.next_appointment.notes && (
                                                    <div className="text-right">
                                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Planned Procedure</p>
                                                        <p className="text-xs font-bold text-slate-700">{visit.next_appointment.notes}</p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    {visit.invoice_id && (
                                        <div className="mt-4 flex justify-end">
                                            <button 
                                                onClick={() => openInvoice(visit.invoice_id)}
                                                className="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-primary transition-colors py-2 px-4 bg-slate-50 rounded-xl"
                                            >
                                                Details & Documents <CreditCard size={12} />
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        );
                    })
                    )}
                </div>

                {/* Quick Vitals / Patient Info / Invoices */}
                <div className="space-y-4">
                    <div className="flex items-center gap-2 text-slate-600 font-bold uppercase tracking-wider text-xs px-2">
                        <ClipboardIcon size={14} /> Patient Summary
                    </div>
                    <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-6">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Visits</label>
                                <p className="text-xl font-black text-slate-800">{stats.clinical.totalVisits}</p>
                            </div>
                            <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Teeth Treated</label>
                                <p className="text-xl font-black text-slate-800">{stats.clinical.totalTeethTreated}</p>
                            </div>
                            <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Collection Rate</label>
                                <p className="text-xl font-black text-emerald-600">{stats.financial.collectionRate}%</p>
                            </div>
                            <div className="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Visit Gap</label>
                                <p className="text-lg font-black text-slate-800">{stats.clinical.avgVisitGap} <span className="text-[10px] text-slate-500 font-bold uppercase">Days</span></p>
                            </div>
                        </div>

                        <div className="pt-6 border-t border-slate-100 space-y-4">
                            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Retention Roadmap</label>
                            <div className="grid grid-cols-2 gap-3">
                                <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span className="text-[9px] font-black text-slate-400 uppercase block mb-1">First Visit</span>
                                    <span className="text-[11px] font-black text-slate-700">
                                        {stats.clinical.firstVisit ? new Date(stats.clinical.firstVisit).toLocaleDateString() : 'N/A'}
                                    </span>
                                </div>
                                <div className="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span className="text-[9px] font-black text-slate-400 uppercase block mb-1">Last Visit</span>
                                    <span className="text-[11px] font-black text-slate-700">
                                        {stats.clinical.lastVisit ? new Date(stats.clinical.lastVisit).toLocaleDateString() : 'N/A'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="pt-6 border-t border-slate-100">
                            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Financial Dashboard</label>
                            <div className="space-y-3">
                                <div className="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span className="text-xs font-bold text-slate-600">Total Treatment Value</span>
                                    <span className="text-sm font-black text-slate-800">₹{stats.financial.totalValue.toLocaleString()}</span>
                                </div>
                                <div className="flex justify-between items-center p-3 bg-emerald-50/50 rounded-xl border border-emerald-100">
                                    <span className="text-xs font-bold text-emerald-700">Total Collected</span>
                                    <span className="text-sm font-black text-emerald-600">₹{stats.financial.totalPaid.toLocaleString()}</span>
                                </div>
                                <div className="flex justify-between items-center p-3 bg-red-50/50 rounded-xl border border-red-100">
                                    <span className="text-xs font-bold text-red-700">Pending Balance</span>
                                    <span className="text-sm font-black text-red-600">₹{stats.financial.totalPending.toLocaleString()}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Tooth Treatment History */}
                    <div className="flex items-center gap-2 text-slate-600 font-bold uppercase tracking-wider text-xs px-2 pt-4">
                        <Activity size={14} /> Tooth History Timeline
                    </div>
                    <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden p-6 space-y-6 max-h-[400px] overflow-y-auto custom-scrollbar">
                        {Object.keys(toothHistory).length === 0 ? (
                            <p className="text-center text-slate-400 text-xs italic">No specific tooth history recorded.</p>
                        ) : (
                            Object.keys(toothHistory).sort((a, b) => Number(a) - Number(b)).map(toothId => (
                                <div key={toothId} className="space-y-3">
                                    <div className="flex items-center gap-2">
                                        <div className="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center font-black text-sm">
                                            {toothId}
                                        </div>
                                        <div className="h-[2px] flex-1 bg-slate-50"></div>
                                    </div>
                                    <div className="pl-10 space-y-4 border-l-2 border-slate-50 ml-4">
                                        {toothHistory[toothId].sort((a,b) => new Date(b.date) - new Date(a.date)).map((entry, idx) => (
                                            <div key={idx} className="relative">
                                                <div className="absolute -left-[31px] top-1.5 w-3 h-3 rounded-full bg-white border-2 border-blue-400"></div>
                                                <p className="text-sm font-black text-slate-800 leading-tight">{entry.procedure}</p>
                                                <p className="text-[10px] font-bold text-slate-400 uppercase mt-0.5">
                                                    {new Date(entry.date).toLocaleDateString()} • ₹{entry.fee}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    {/* Invoices Section */}
                    <div className="flex items-center gap-2 text-slate-600 font-bold uppercase tracking-wider text-xs px-2 pt-4">
                        <CreditCard size={14} /> Financial Records
                    </div>
                    <div className="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        {invoices.length === 0 ? (
                            <div className="p-8 text-center text-slate-400 text-xs italic">No invoices generated.</div>
                        ) : (
                            <div className="divide-y divide-slate-50">
                                {invoices.map(inv => (
                                    <div key={inv.id} className="p-4 hover:bg-slate-50/50 transition-all flex flex-col gap-2 group border-b border-slate-50 last:border-0">
                                        <div className="flex justify-between items-center">
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <p className="font-bold text-slate-800">#INV-{inv.id}</p>
                                                    <span className={`text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase ${inv.status === 'Paid' ? 'bg-green-100 text-green-600' :
                                                        inv.status === 'Unpaid' ? 'bg-red-100 text-red-600' :
                                                            inv.status === 'ReallocationRequired' ? 'bg-amber-100 text-amber-700 animate-pulse' :
                                                                'bg-orange-100 text-orange-600'
                                                        }`}>
                                                        {inv.status === 'ReallocationRequired' ? 'Stock Required' : inv.status}
                                                    </span>
                                                </div>
                                                <p className="text-[10px] text-slate-400 font-bold">{new Date(inv.created_at).toLocaleDateString()}</p>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-black text-slate-800 text-sm">₹{inv.total_amount}</p>
                                                <button
                                                    onClick={() => openInvoice(inv.id)}
                                                    className="text-[10px] font-bold text-primary opacity-0 group-hover:opacity-100 transition-opacity"
                                                >
                                                    View Details
                                                </button>
                                            </div>
                                        </div>

                                        {inv.status === 'ReallocationRequired' && can('billing') && (
                                            <button
                                                disabled={approvingId === inv.id}
                                                onClick={() => handleApproveReallocation(inv.id)}
                                                className="w-full mt-1 bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 shadow-lg shadow-amber-600/20 disabled:opacity-50"
                                            >
                                                {approvingId === inv.id ? (
                                                    <div className="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                                ) : <CheckCircle2 size={12} />}
                                                Approve Reallocation
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Next Appointment Panel */}
                    <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="font-bold text-slate-800 flex items-center gap-2">
                                <Activity size={16} className="text-primary" /> Next Appointment
                            </h3>
                        </div>
                        {upcomingAppointment ? (
                            <div className="bg-primary/5 border border-primary/10 rounded-2xl p-4">
                                <p className="text-sm font-black text-slate-800">
                                    {new Date(upcomingAppointment.appointment_date).toLocaleDateString(undefined, { dateStyle: 'full' })}
                                </p>
                                <p className="text-xl font-black text-primary mt-1">
                                    {new Date(upcomingAppointment.appointment_date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                </p>
                                {upcomingAppointment.notes && (
                                    <p className="text-xs text-slate-500 font-bold mt-2 uppercase tracking-tighter">
                                        Procedure: {upcomingAppointment.notes}
                                    </p>
                                )}
                            </div>
                        ) : (
                            <div className="bg-slate-50 border border-slate-100 rounded-2xl p-6 text-center">
                                <p className="text-xs text-slate-400 font-bold italic">No upcoming appointment scheduled.</p>
                            </div>
                        )}
                    </div>

                    {/* Treatment Plan Panel */}
                    <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                        <h3 className="font-bold text-slate-800 mb-4 flex items-center gap-2 uppercase tracking-widest text-[10px]">
                            <Plus size={14} className="text-blue-500" /> Treatment Plan
                        </h3>
                        <div className="space-y-4">
                            {/* Planned */}
                            <div>
                                <h4 className="text-[9px] font-black text-orange-500 uppercase tracking-widest mb-2 border-b border-orange-50 pb-1">Planned</h4>
                                {treatmentPlan.planned.length === 0 ? (
                                    <p className="text-[10px] text-slate-400 italic">No planned procedures.</p>
                                ) : (
                                    <div className="space-y-2">
                                        {treatmentPlan.planned.map(t => (
                                            <div key={t.id} className="flex justify-between items-center text-xs font-bold text-slate-700 bg-orange-50/30 p-2 rounded-lg">
                                                <span>{t.procedure_name}</span>
                                                {t.teeth && <span className="text-[10px] text-orange-600">#{t.teeth}</span>}
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {/* Completed */}
                            <div>
                                <h4 className="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-2 border-b border-emerald-50 pb-1">Completed</h4>
                                {treatmentPlan.completed.length === 0 ? (
                                    <p className="text-[10px] text-slate-400 italic">No completed procedures.</p>
                                ) : (
                                    <div className="space-y-2">
                                        {treatmentPlan.completed.slice(0, 5).map(t => (
                                            <div key={t.id} className="flex justify-between items-center text-xs font-bold text-slate-700 bg-emerald-50/30 p-2 rounded-lg">
                                                <span>{t.procedure_name}</span>
                                                {t.teeth && <span className="text-[10px] text-emerald-600">#{t.teeth}</span>}
                                            </div>
                                        ))}
                                        {treatmentPlan.completed.length > 5 && (
                                            <p className="text-[9px] text-slate-400 text-center font-bold">+ {treatmentPlan.completed.length - 5} more completed</p>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                      {showAddModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-6xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                        {/* Header */}
                        <div className="p-8 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0 z-20">
                            <div>
                                <h2 className="text-3xl font-black text-slate-800">New Clinical Record</h2>
                                <p className="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Institutional Visit Log</p>
                            </div>
                            <button onClick={() => setShowAddModal(false)} className="p-2 hover:bg-slate-100 rounded-xl transition-colors">
                                <X size={24} />
                            </button>
                        </div>

                        <div className="flex-1 overflow-y-auto p-0 flex">
                            {/* Left Side: Controls (Assignment & Pharmacy) */}
                            <div className="w-1/2 border-r border-slate-100 overflow-y-auto p-10">
                                
                                {/* 1. Assignment Summary (Always visible at top) */}
                                <div className="mb-10">
                                    <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <Activity size={14} /> Treatment Assignment
                                    </h3>
                                    {Object.keys(toothServices).length === 0 && (
                                        <div className="bg-blue-50 border border-blue-100 rounded-2xl p-6 text-center text-blue-600 font-bold text-sm">
                                            Click a tooth on the chart to begin assignment.
                                        </div>
                                    )}
                                    <div className="grid grid-cols-1 gap-3">
                                        {Object.entries(toothServices).map(([tooth, services]) => services.length > 0 && (
                                            <div key={tooth} className="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                                                <div className="flex justify-between items-center mb-3">
                                                    <span className="text-sm font-black text-primary px-3 py-1 bg-primary/5 rounded-full">Tooth #{tooth}</span>
                                                    <button type="button" onClick={() => {
                                                        const updated = { ...toothServices };
                                                        delete updated[tooth];
                                                        setToothServices(updated);
                                                        setSelectedTeeth(prev => prev.filter(t => t !== Number(tooth)));
                                                        if (activeTooth === Number(tooth)) setActiveTooth(null);
                                                    }} className="text-[10px] font-bold text-red-500 hover:underline">Clear</button>
                                                </div>
                                                <div className="space-y-2">
                                                    {services.map(s => (
                                                        <div key={s.react_key} className="flex justify-between items-center text-sm font-bold text-slate-700">
                                                            <span>{s.item_name}</span>
                                                            <div className="flex items-center gap-2">
                                                                <span className="text-xs text-slate-400">₹</span>
                                                                <input 
                                                                    type="number" 
                                                                    value={s.billed_price} 
                                                                    onChange={(e) => handlePriceChange(s.react_key, e.target.value)}
                                                                    className="w-20 p-1 border rounded-lg text-right text-xs"
                                                                />
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* 2. Service Selection Panel (Modal-like state when tooth is active) */}
                                {activeTooth && (
                                    <div className="mb-10 bg-slate-50 p-6 rounded-3xl border-2 border-primary/20 ring-4 ring-primary/5">
                                        <h3 className="text-sm font-black text-primary uppercase mb-4">Select Service for Tooth #{activeTooth}</h3>
                                        <ServiceSelector
                                            services={catalog}
                                            selectedServices={toothServices[activeTooth] || []}
                                            onToggleService={toggleService}
                                            onPriceChange={handlePriceChange}
                                        />
                                    </div>
                                )}

                                {/* 3. Pharmacy Section */}
                                <div className="mb-10 pt-10 border-t border-slate-100">
                                    <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <Pill size={14} /> Pharmacy Dispensing
                                    </h3>
                                    <div className="space-y-4">
                                        <div className="bg-white border-2 border-slate-100 rounded-2xl p-4">
                                            <label className="text-[10px] font-black text-slate-400 uppercase ml-1">Search & Add Medicine</label>
                                            <select 
                                                onChange={(e) => addMedicine(e.target.value)} 
                                                className="w-full mt-2 p-3 font-bold border-none outline-none focus:ring-0 bg-transparent"
                                                defaultValue=""
                                            >
                                                <option value="" disabled>Select medicine to add...</option>
                                                {inventory.map(m => (
                                                    <option key={m.id} value={m.id} disabled={m.stock < 1}>
                                                        {m.item_name} (Stock: {m.stock})
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="space-y-2">
                                            {selectedMedicines.map(m => (
                                                <div key={m.id} className="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-3">
                                                    <div className="flex justify-between items-center">
                                                        <div className="flex-1">
                                                            <p className="text-sm font-bold text-slate-800">{m.item_name}</p>
                                                            <p className="text-[10px] text-slate-400 uppercase font-black">Quantity: {m.quantity}</p>
                                                        </div>
                                                        <div className="flex items-center gap-4">
                                                            <QuantityController 
                                                                value={m.quantity} 
                                                                onChange={(q) => updateMedicineQuantity(m.id, q)} 
                                                            />
                                                            <button onClick={() => removeMedicine(m.id)} className="text-red-400 hover:text-red-600 p-1">
                                                                <X size={16} />
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center justify-between pt-2 border-t border-slate-200/50">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-[10px] font-black text-slate-400 uppercase">Unit Price: ₹</span>
                                                            <input 
                                                                type="number" 
                                                                value={m.unit_price} 
                                                                onChange={(e) => updateMedicinePrice(m.id, e.target.value)}
                                                                className="w-20 p-1 border rounded bg-white text-xs font-bold text-right"
                                                            />
                                                        </div>
                                                        <p className="text-sm font-black text-slate-700">₹{(m.unit_price * m.quantity).toLocaleString()}</p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                {/* 4. Clinical Notes & Visit Summary */}
                                <div className="pt-10 border-t border-slate-100 space-y-6">
                                    <div className="flex gap-6">
                                        <div className="flex-1">
                                            <label className="text-xs font-black text-slate-400 uppercase ml-1">Overall Clinical Notes</label>
                                            <textarea
                                                value={formData.notes} onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                                                placeholder="Procedure findings, drug instructions..."
                                                className="w-full mt-2 px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 transition-all h-24 font-medium text-sm"
                                            ></textarea>
                                        </div>
                                        <div className="w-1/3">
                                            <label className="text-xs font-black text-slate-400 uppercase ml-1">Overall Status</label>
                                            <div className="mt-2 space-y-2">
                                                {['Completed', 'Planned'].map(s => (
                                                    <button 
                                                        key={s}
                                                        type="button"
                                                        onClick={() => setFormData({ ...formData, status: s })}
                                                        className={`w-full p-4 rounded-2xl text-xs font-black transition-all border ${formData.status === s ? 'bg-slate-800 text-white border-slate-800 shadow-xl' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'}`}
                                                    >
                                                        {s === 'Completed' ? '✅ Save as Completed' : '📋 Add to Treatment Plan'}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    </div>

                                    {/* VISIT SUMMARY & BILLING */}
                                    <div className="bg-slate-900 rounded-3xl p-8 text-white shadow-2xl">
                                        <div className="flex justify-between items-center mb-6">
                                            <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest">Billing & Checkout</h3>
                                            <div className="bg-primary/20 text-primary text-[10px] font-black px-3 py-1 rounded-full uppercase">Instant Invoice</div>
                                        </div>

                                        <div className="space-y-4 mb-8">
                                            <div className="flex justify-between items-center text-sm font-bold text-slate-400">
                                                <span>Subtotal</span>
                                                <span>₹{subtotal.toLocaleString()}</span>
                                            </div>
                                            
                                            <div className="flex gap-4 items-center">
                                                <div className="flex-1">
                                                    <label className="text-[10px] font-black text-slate-500 uppercase block mb-1">Discount (₹)</label>
                                                    <input 
                                                        type="number" 
                                                        value={billingData.discount}
                                                        onChange={(e) => {
                                                            const val = Number(e.target.value);
                                                            setBillingData({ ...billingData, discount: val > subtotal ? subtotal : val });
                                                        }}
                                                        className="w-full bg-slate-800 border-none rounded-xl p-2 text-sm font-bold focus:ring-1 focus:ring-primary"
                                                    />
                                                </div>
                                                <div className="flex-1">
                                                    <label className="text-[10px] font-black text-slate-500 uppercase block mb-1">Status</label>
                                                    <div className="flex p-1 bg-slate-800 rounded-xl gap-1">
                                                        {['Paid', 'Partial', 'Unpaid'].map(s => (
                                                            <button 
                                                                key={s}
                                                                type="button"
                                                                onClick={() => setBillingData({ ...billingData, payment_status: s })}
                                                                className={`flex-1 py-1 rounded-lg text-[9px] font-black transition-all ${billingData.payment_status === s ? 'bg-primary text-white' : 'text-slate-500 hover:text-slate-300'}`}
                                                            >
                                                                {s}
                                                            </button>
                                                        ))}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex gap-4 items-center">
                                                <div className="flex-1">
                                                    <label className="text-[10px] font-black text-slate-500 uppercase block mb-1">Paid Amount (₹)</label>
                                                    <input 
                                                        type="number" 
                                                        value={billingData.paid_amount}
                                                        disabled={billingData.payment_status !== 'Partial'}
                                                        onChange={(e) => {
                                                            const val = Number(e.target.value);
                                                            setBillingData({ ...billingData, paid_amount: val > finalTotal ? finalTotal : val });
                                                        }}
                                                        className={`w-full bg-slate-800 border-none rounded-xl p-2 text-sm font-bold focus:ring-1 focus:ring-green-500 ${billingData.payment_status !== 'Partial' ? 'opacity-50 cursor-not-allowed' : ''}`}
                                                    />
                                                </div>
                                                <div className="flex-1">
                                                    <label className="text-[10px] font-black text-slate-500 uppercase block mb-1">Method</label>
                                                    <div className="flex gap-1">
                                                        {['Cash', 'UPI', 'Card'].map(m => (
                                                            <button 
                                                                key={m}
                                                                type="button"
                                                                onClick={() => setBillingData({ ...billingData, payment_method: m })}
                                                                className={`flex-1 py-1.5 rounded-lg text-[9px] font-black transition-all ${billingData.payment_method === m ? 'bg-primary text-white' : 'bg-slate-800 text-slate-500 hover:bg-slate-700'}`}
                                                            >
                                                                {m}
                                                            </button>
                                                        ))}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-4 border-t border-slate-800 pt-6">
                                            <div className="bg-slate-800/50 p-6 rounded-2xl border border-slate-700/50">
                                                <h4 className="text-[10px] font-black text-primary uppercase tracking-widest mb-4">Schedule Next Appointment</h4>
                                                <div className="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label className="text-[9px] font-black text-slate-500 uppercase block mb-1">Date</label>
                                                        <input 
                                                            type="date" 
                                                            value={nextAppointment.date}
                                                            onChange={(e) => setNextAppointment({ ...nextAppointment, date: e.target.value })}
                                                            className="w-full bg-slate-900 border-slate-700 text-white rounded-lg p-2 text-xs font-bold"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-[9px] font-black text-slate-500 uppercase block mb-1">Time</label>
                                                        <input 
                                                            type="time" 
                                                            value={nextAppointment.time}
                                                            onChange={(e) => setNextAppointment({ ...nextAppointment, time: e.target.value })}
                                                            className="w-full bg-slate-900 border-slate-700 text-white rounded-lg p-2 text-xs font-bold"
                                                        />
                                                    </div>
                                                </div>
                                                <div className="mt-4">
                                                    <label className="text-[9px] font-black text-slate-500 uppercase block mb-1">Next Procedure Notes</label>
                                                    <textarea 
                                                        placeholder="e.g. Crown Placement, Suture Removal"
                                                        value={nextAppointment.notes}
                                                        onChange={(e) => setNextAppointment({ ...nextAppointment, notes: e.target.value })}
                                                        className="w-full bg-slate-900 border-slate-700 text-white rounded-lg p-2 text-xs font-bold"
                                                        rows="2"
                                                    ></textarea>
                                                </div>
                                            </div>

                                            <div className="flex justify-between items-end pt-2">
                                                <div>
                                                    <p className="text-[10px] font-black text-primary uppercase tracking-widest mb-1">Final Payable</p>
                                                    <p className="text-4xl font-black text-white">₹{finalTotal.toLocaleString()}</p>
                                                    {finalTotal - billingData.paid_amount > 0 && (
                                                        <p className="text-[10px] font-bold text-red-400 mt-2 italic">Balance: ₹{(finalTotal - billingData.paid_amount).toLocaleString()}</p>
                                                    )}
                                                </div>
                                                <button 
                                                    onClick={handleSaveTreatment}
                                                    disabled={saving || (Object.keys(toothServices).length === 0 && selectedMedicines.length === 0)}
                                                    className="bg-primary hover:bg-primary-dark text-white px-8 py-5 rounded-2xl font-black shadow-xl shadow-primary/20 transition-all disabled:opacity-50"
                                                >
                                                    {saving ? 'Processing...' : 'Sync & Complete'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Right Side: Dental Chart & History (Always Visible) */}
                            <div className="w-1/2 bg-slate-50 p-10 overflow-y-auto">
                                <div className="sticky top-0 space-y-8">
                                    <div>
                                        <div className="flex justify-between items-center mb-6">
                                            <h3 className="text-xl font-black text-slate-800">Dental Navigation</h3>
                                            {activeTooth && (
                                                <span className="bg-primary text-white text-[10px] font-black px-3 py-1 rounded-full animate-pulse uppercase">
                                                    Now Editing Tooth #{activeTooth}
                                                </span>
                                            )}
                                        </div>
                                        <div className="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
                                            <DentalChartSVG
                                                selectedTeeth={selectedTeeth}
                                                toothStatus={(() => {
                                                    const status = { ...patient?.tooth_status };
                                                    Object.keys(toothServices).forEach(t => {
                                                        if (toothServices[t].length > 0) status[t] = 'pending';
                                                    });
                                                    return status;
                                                })()}
                                                toothHistory={toothHistory}
                                                onTeethChange={(teeth) => {
                                                    setSelectedTeeth(teeth);
                                                    if (teeth.length > 0) {
                                                        const lastTooth = teeth[teeth.length - 1];
                                                        setActiveTooth(lastTooth);
                                                        
                                                        // Filter history for current tooth
                                                        const history = treatments.filter(t => {
                                                            if (!t.teeth) return false;
                                                            const ids = t.teeth.split(',').map(id => id.trim());
                                                            return ids.includes(lastTooth.toString());
                                                        });
                                                        setSelectedToothHistory(history);
                                                    } else {
                                                        setActiveTooth(null);
                                                        setSelectedToothHistory([]);
                                                    }
                                                }}
                                            />
                                        </div>
                                    </div>

                                    {/* History Panel */}
                                    {activeTooth ? (
                                        <ToothHistoryPanel 
                                            toothId={activeTooth}
                                            history={selectedToothHistory}
                                            onAddTreatment={() => {}}
                                        />
                                    ) : (
                                        <div className="bg-white/50 border-2 border-dashed border-slate-200 rounded-3xl p-12 text-center">
                                            <p className="text-slate-400 font-bold italic text-sm">Select a specific tooth to view clinical history.</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
            {/* Invoice Detail Modal */}
            {showInvoiceModal && selectedInvoice && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-2xl rounded-[2.5rem] p-10 shadow-2xl overflow-y-auto max-h-[90vh]">
                        <div className="flex justify-between items-center mb-8">
                            <div>
                                <h2 className="text-3xl font-black text-slate-800">Invoice Statement</h2>
                                <p className="text-slate-500 font-bold uppercase tracking-widest text-[10px] mt-1">Institutional Financial Record</p>
                            </div>
                            <span className={`text-[10px] font-black px-4 py-2 rounded-full uppercase tracking-widest ${selectedInvoice.invoice.status === 'Paid' ? 'bg-green-100 text-green-600' :
                                selectedInvoice.invoice.status === 'Partial' ? 'bg-amber-100 text-amber-700' :
                                    'bg-red-100 text-red-600'
                                }`}>
                                {selectedInvoice.invoice.status}
                            </span>
                        </div>

                        {/* Invoice Header */}
                        <div className="grid grid-cols-2 gap-8 border-b border-slate-100 pb-8 mb-8">
                            <div>
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1">Invoice ID</label>
                                <p className="text-xl font-black text-slate-800">#INV-{selectedInvoice.invoice.id}</p>
                                <p className="text-xs font-bold text-slate-500 mt-1">{new Date(selectedInvoice.invoice.created_at).toLocaleDateString(undefined, { dateStyle: 'long' })}</p>
                            </div>
                            <div className="text-right">
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1">Patient & provider</label>
                                <p className="text-lg font-black text-slate-800">{patient?.name}</p>
                                <p className="text-xs font-bold text-slate-500 uppercase tracking-wider">Dr. {selectedInvoice.invoice.doctor?.name || 'Medical Officer'}</p>
                            </div>
                        </div>

                        {/* Item Table (Grouped) */}
                        <div className="mb-8">
                            <table className="w-full text-left">
                                <thead>
                                    <tr>
                                        <th className="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Name</th>
                                        <th className="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-50">
                                    {/* Procedures Section */}
                                    {selectedInvoice.items?.filter(i => i.type === "Procedure" || (!i.type && !i.inventory_id)).length > 0 && (
                                        <>
                                            <tr>
                                                <td colSpan="2" className="pt-6 pb-2 text-[10px] font-black text-primary uppercase tracking-[0.2em]">Procedures</td>
                                            </tr>
                                            {selectedInvoice.items.filter(i => i.type === "Procedure" || (!i.type && !i.inventory_id)).map((item, idx) => (
                                                <tr key={`proc-${idx}`}>
                                                    <td className="py-3">
                                                        <p className="font-bold text-slate-800">{item.name || item.procedure_name || 'Clinical Service'}</p>
                                                        {item.teeth && <p className="text-[9px] font-black text-primary uppercase">Tooth: {item.teeth}</p>}
                                                    </td>
                                                    <td className="py-3 text-right font-black text-slate-800">₹{parseFloat(item.amount || item.fee || 0).toLocaleString()}</td>
                                                </tr>
                                            ))}
                                        </>
                                    )}

                                    {/* Medicines Section */}
                                    {selectedInvoice.items?.filter(i => i.type === "Medicine" || i.inventory_id).length > 0 && (
                                        <>
                                            <tr>
                                                <td colSpan="2" className="pt-6 pb-2 text-[10px] font-black text-purple-600 uppercase tracking-[0.2em]">Medicines</td>
                                            </tr>
                                            {selectedInvoice.items.filter(i => i.type === "Medicine" || i.inventory_id).map((item, idx) => (
                                                <tr key={`med-${idx}`}>
                                                    <td className="py-3">
                                                        <p className="font-bold text-slate-800">{item.name || item.procedure_name || 'Dispensed Stock'}</p>
                                                        {item.quantity > 1 && <span className="text-[9px] font-black text-slate-400 uppercase">Qty: {item.quantity} Units</span>}
                                                        {item.teeth && <p className="text-[9px] font-black text-primary uppercase ml-1 inline-block">Tooth: {item.teeth}</p>}
                                                    </td>
                                                    <td className="py-3 text-right font-black text-slate-800">₹{parseFloat(item.fee || 0).toLocaleString()}</td>
                                                </tr>
                                            ))}
                                        </>
                                    )}

                                    {(!selectedInvoice.items || selectedInvoice.items.length === 0) && (
                                        <tr>
                                            <td colSpan="2" className="py-8 text-center text-slate-400 italic text-sm">No itemized breakdown for this invoice.</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Totals & Payment Info */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start mb-8">
                            <div className="space-y-6">
                                <div>
                                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Payment Info</label>
                                    <div className="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                        <div className="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                                            <CreditCard size={18} />
                                        </div>
                                        <div>
                                            <p className="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Method</p>
                                            <p className="font-bold text-slate-700">{selectedInvoice.invoice.payment_method || 'Direct Payment'}</p>
                                        </div>
                                    </div>
                                </div>
                                {selectedInvoice.ledger && selectedInvoice.ledger.length > 0 && (
                                    <div>
                                        <label className="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Ledger Entries</label>
                                        <div className="space-y-2 max-h-32 overflow-y-auto pr-2 custom-scrollbar">
                                            {selectedInvoice.ledger.map((entry, idx) => (
                                                <div key={idx} className="flex justify-between items-center text-xs p-3 rounded-xl bg-slate-50 border border-slate-100">
                                                    <div className="flex flex-col">
                                                        <span className="text-slate-800 font-bold">{entry.method || 'Cash'} Payment</span>
                                                        <span className="text-[9px] text-slate-400 font-black">{new Date(entry.created_at).toLocaleDateString()}</span>
                                                    </div>
                                                    <span className="font-black text-green-600">+₹{parseFloat(entry.amount).toLocaleString()}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>

                            <div className="bg-slate-900 rounded-[2.5rem] p-8 text-white space-y-4 shadow-2xl shadow-slate-900/30">
                                <div className="flex justify-between text-xs font-bold opacity-70 uppercase tracking-widest">
                                    <span>Subtotal</span>
                                    <span>₹{parseFloat(selectedInvoice.invoice.subtotal).toLocaleString()}</span>
                                </div>
                                {parseFloat(selectedInvoice.invoice.discount_amount) > 0 && (
                                    <div className="flex justify-between text-xs font-bold text-orange-400 uppercase tracking-widest">
                                        <span>Discount</span>
                                        <span>-₹{parseFloat(selectedInvoice.invoice.discount_amount).toLocaleString()}</span>
                                    </div>
                                )}
                                <div className="flex justify-between items-center pt-2 border-t border-white/10">
                                    <span className="text-[10px] font-black uppercase tracking-[0.2em] opacity-80">Final Total</span>
                                    <span className="text-3xl font-black">₹{parseFloat(selectedInvoice.invoice.total_amount).toLocaleString()}</span>
                                </div>
                                <div className="flex justify-between text-sm text-green-400 font-bold">
                                    <span>Collected</span>
                                    <span>₹{parseFloat(selectedInvoice.invoice.paid_amount).toLocaleString()}</span>
                                </div>
                                <div className="flex justify-between text-lg font-black text-red-400 pt-3 border-t border-white/10 mt-2">
                                    <span className="text-[10px] uppercase tracking-[0.2em]">Balance Pending</span>
                                    <span>₹{parseFloat(selectedInvoice.invoice.balance_due).toLocaleString()}</span>
                                </div>
                            </div>
                        </div>

                        <div className="flex flex-wrap justify-end pt-8 gap-4 border-t border-slate-100 mt-8">
                            <button
                                onClick={() => setShowInvoiceModal(false)}
                                className="px-6 py-3 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition-all text-xs"
                            >
                                Close
                            </button>
                            
                            <button
                                onClick={handleWhatsAppShare}
                                className="px-6 py-3 bg-green-500 text-white font-bold rounded-2xl hover:bg-green-600 shadow-lg shadow-green-200 transition-all text-xs flex items-center gap-2"
                            >
                                <MessageSquare size={14} /> WhatsApp Share
                            </button>

                            <button
                                onClick={handleDownloadPdf}
                                className="px-6 py-3 bg-slate-800 text-white font-bold rounded-2xl hover:bg-slate-900 shadow-lg shadow-slate-200 transition-all text-xs flex items-center gap-2"
                            >
                                <Download size={14} /> PDF
                            </button>

                            <button
                                className="px-6 py-3 bg-primary text-white font-bold rounded-2xl hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all text-xs flex items-center gap-2"
                                onClick={() => window.open(`/invoice/${selectedInvoice.invoice.uuid}`, '_blank')}
                            >
                                <Printer size={14} /> Print
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div >
    );
}
