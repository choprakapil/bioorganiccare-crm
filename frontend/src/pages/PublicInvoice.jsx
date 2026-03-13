import { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import { Printer, Download, MessageSquare, ShieldCheck, Landmark, Pill, Scissors } from 'lucide-react';

export default function PublicInvoice() {
    const { uuid } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchInvoice = async () => {
            try {
                const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
                const res = await axios.get(`${baseUrl}/public/invoices/${uuid}`);
                setData(res.data);
            } catch (err) {
                setError('Invoice not found or expired.');
            } finally {
                setLoading(false);
            }
        };
        fetchInvoice();
    }, [uuid]);

    const handleDownloadPdf = () => {
        const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
        window.open(`${baseUrl}/public/invoices/${uuid}/pdf`, '_blank');
    };

    if (loading) return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center">
            <div className="text-center">
                <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p className="text-slate-500 font-bold uppercase tracking-widest text-[10px]">Verifying Digital Record...</p>
            </div>
        </div>
    );

    if (error || !data) return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center p-6">
            <div className="max-w-md w-full bg-white rounded-[2.5rem] p-12 text-center shadow-xl border border-slate-100">
                <div className="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <ShieldCheck size={40} />
                </div>
                <h1 className="text-2xl font-black text-slate-800 mb-2">Access Restricted</h1>
                <p className="text-slate-500 font-medium mb-8">{error || 'This digital record is currently unavailable.'}</p>
                <button 
                    onClick={() => window.location.reload()}
                    className="w-full py-4 bg-slate-100 text-slate-600 font-black rounded-2xl hover:bg-slate-200 transition-all"
                >
                    Retry Access
                </button>
            </div>
        </div>
    );

    const { invoice, items } = data;

    return (
        <div className="min-h-screen bg-[#f8fafc] py-12 px-4 sm:px-6">
            <div className="max-w-4xl mx-auto">
                <div className="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/60 overflow-hidden border border-slate-100">
                    {/* Branding Header */}
                    <div className="bg-slate-900 p-10 sm:p-14 text-white relative">
                        <div className="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                        <div className="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-8">
                            <div>
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center shadow-lg shadow-primary/40">
                                        <Landmark size={24} className="text-white" />
                                    </div>
                                    <h1 className="text-2xl font-black tracking-tight">{invoice.doctor?.clinic_name || 'Medical Center'}</h1>
                                </div>
                                <p className="text-slate-400 font-bold uppercase tracking-[0.3em] text-[10px]">Official Digital Statement</p>
                            </div>
                            <div className="text-right">
                                <span className={`inline-block px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest ${
                                    invoice.status === 'Paid' ? 'bg-emerald-500/20 text-emerald-400' :
                                    invoice.status === 'Partial' ? 'bg-amber-500/20 text-amber-400' :
                                    'bg-red-500/20 text-red-400'
                                }`}>
                                    {invoice.status} Status
                                </span>
                                <h2 className="text-4xl font-black mt-4 tracking-tighter">#INV-{invoice.id}</h2>
                            </div>
                        </div>
                    </div>

                    <div className="p-10 sm:p-14">
                        {/* Meta Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-12 pb-12 border-b border-slate-100 mb-12">
                            <div>
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Patient Record</label>
                                <p className="text-xl font-black text-slate-800">{invoice.patient?.name}</p>
                                <p className="text-sm font-bold text-slate-500 mt-1">{invoice.patient?.phone}</p>
                                <p className="text-xs text-slate-400 mt-2 italic line-clamp-1">{invoice.patient?.address}</p>
                            </div>
                            <div>
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Service Provider</label>
                                <p className="text-xl font-black text-slate-800">Dr. {invoice.doctor?.name}</p>
                                <p className="text-sm font-bold text-slate-500 mt-1">{invoice.doctor?.clinic_name}</p>
                                <p className="text-xs text-slate-400 mt-2 uppercase font-black tracking-tighter">Licensed Practitioner</p>
                            </div>
                            <div className="md:text-right">
                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-4">Timeline</label>
                                <p className="text-xl font-black text-slate-800">{new Date(invoice.created_at).toLocaleDateString(undefined, { dateStyle: 'long' })}</p>
                                <p className="text-sm font-bold text-slate-500 mt-1">Due: {new Date(invoice.due_date).toLocaleDateString()}</p>
                                <p className="text-xs text-slate-400 mt-2 uppercase font-black tracking-tighter">Generated by DentFlow</p>
                            </div>
                        </div>

                        {/* Items Section */}
                        <div className="mb-16">
                            <h3 className="text-sm font-black text-slate-800 uppercase tracking-widest mb-8 flex items-center gap-3">
                                <span className="w-8 h-px bg-slate-200"></span>
                                Itemized Breakdown
                            </h3>
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-slate-50">
                                        <th className="pb-6 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                                        <th className="pb-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest w-32">Total Fee</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-50">
                                    {items.map((item, idx) => (
                                        <tr key={idx} className="group hover:bg-slate-50/50 transition-colors">
                                            <td className="py-8">
                                                <div className="flex items-start gap-5">
                                                    <div className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 ${
                                                        item.type === 'Medicine' || item.inventory_id ? 'bg-purple-50 text-purple-500' : 'bg-primary/5 text-primary'
                                                    }`}>
                                                        {item.type === 'Medicine' || item.inventory_id ? <Pill size={20} /> : <Scissors size={20} />}
                                                    </div>
                                                    <div>
                                                        <p className="text-lg font-black text-slate-800 tracking-tight">{item.name || item.procedure_name}</p>
                                                        <div className="flex flex-wrap items-center gap-3 mt-1.5">
                                                            {item.teeth && (
                                                                <span className="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-black rounded-lg uppercase tracking-widest">
                                                                    Tooth: {item.teeth}
                                                                </span>
                                                            )}
                                                            {item.quantity > 1 && (
                                                                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                                                    {item.quantity} Units Displayed
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="py-8 text-right align-top">
                                                <p className="text-xl font-black text-slate-800">₹{parseFloat(item.fee || 0).toLocaleString()}</p>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Totals Section */}
                        <div className="flex flex-col md:flex-row justify-between items-start gap-12 bg-slate-50 rounded-[2.5rem] p-10 sm:p-14">
                            <div className="max-w-xs">
                                <h4 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Institutional Note</h4>
                                <p className="text-slate-500 text-sm font-medium leading-relaxed italic">
                                    "This digital document serves as a verified statement of clinical services rendered. Financial transactions recorded are final and immutable."
                                </p>
                            </div>
                            <div className="w-full md:w-80 space-y-6">
                                <div className="space-y-3 pb-6 border-b border-slate-200">
                                    <div className="flex justify-between text-slate-500 font-bold text-sm tracking-tight">
                                        <span>Gross Subtotal</span>
                                        <span>₹{parseFloat(invoice.subtotal).toLocaleString()}</span>
                                    </div>
                                    {parseFloat(invoice.discount_amount) > 0 && (
                                        <div className="flex justify-between text-orange-500 font-bold text-sm tracking-tight">
                                            <span>Adjustment/Discount</span>
                                            <span>-₹{parseFloat(invoice.discount_amount).toLocaleString()}</span>
                                        </div>
                                    )}
                                </div>
                                <div className="space-y-4 pt-2">
                                    <div className="flex justify-between items-center text-slate-800">
                                        <span className="text-[10px] font-black uppercase tracking-widest">Net Final Total</span>
                                        <span className="text-4xl font-black tracking-tighter">₹{parseFloat(invoice.total_amount).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between items-center text-emerald-600 bg-emerald-50 px-5 py-3 rounded-2xl border border-emerald-100">
                                        <span className="text-[9px] font-black uppercase tracking-widest">Total Collected</span>
                                        <span className="font-black text-xl">₹{parseFloat(invoice.paid_amount).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between items-center text-red-600 bg-red-50 px-5 py-3 rounded-2xl border border-red-100">
                                        <span className="text-[9px] font-black uppercase tracking-widest">Balance Pending</span>
                                        <span className="font-black text-xl">₹{parseFloat(invoice.balance_due).toLocaleString()}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Public Action Bar */}
                        <div className="mt-14 flex flex-wrap items-center justify-center gap-6 print:hidden">
                            <button 
                                onClick={() => window.print()}
                                className="px-8 py-4 bg-white border-2 border-slate-100 text-slate-600 font-black rounded-2xl hover:bg-slate-50 transition-all flex items-center gap-3 shadow-lg shadow-slate-100/50"
                            >
                                <Printer size={18} /> Instant Print
                            </button>
                            <button 
                                onClick={handleDownloadPdf}
                                className="px-8 py-4 bg-slate-900 text-white font-black rounded-2xl hover:bg-slate-800 transition-all flex items-center gap-3 shadow-xl shadow-slate-200"
                            >
                                <Download size={18} /> Official PDF
                            </button>
                            <button 
                                onClick={() => window.open(`https://wa.me/?text=${encodeURIComponent('View my dental invoice: ' + window.location.href)}`, '_blank')}
                                className="px-8 py-4 bg-emerald-500 text-white font-black rounded-2xl hover:bg-emerald-600 transition-all flex items-center gap-3 shadow-xl shadow-emerald-100"
                            >
                                <MessageSquare size={18} /> Share Record
                            </button>
                        </div>
                    </div>
                </div>
                
                <p className="text-center mt-10 text-slate-400 font-bold uppercase tracking-[0.4em] text-[9px]">
                    Verified Clinical Record • Powered by DentFlow CRM
                </p>
            </div>
        </div>
    );
}
