import { useState, useEffect } from 'react';
import api from '../../api/axios';
import toast from 'react-hot-toast';
import { MessageSquare, User, Smartphone, MapPin, Globe, Monitor, Clock, ChevronLeft, ChevronRight, Stethoscope } from 'lucide-react';
import AdminPageHeader from '../../components/layout/AdminPageHeader';

export default function LandingEnquiries() {
    const [enquiries, setEnquiries] = useState([]);
    const [loading, setLoading] = useState(true);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0
    });

    useEffect(() => {
        fetchEnquiries();
    }, [pagination.current_page]);

    const fetchEnquiries = async () => {
        setLoading(true);
        try {
            const res = await api.get(`/admin/enquiries?page=${pagination.current_page}`);
            setEnquiries(res.data.data);
            setPagination({
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                total: res.data.total
            });
        } catch (err) {
            toast.error('Failed to load enquiries');
        } finally {
            setLoading(false);
        }
    };

    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= pagination.last_page) {
            setPagination(prev => ({ ...prev, current_page: newPage }));
        }
    };

    return (
        <div className="max-w-7xl mx-auto px-6 py-6 transition-all duration-300">
            <AdminPageHeader
                title="Landing Enquiries"
                description="Monitor lead generation and trial requests."
            />

            {loading ? (
                <div className="p-12 text-center text-slate-400 font-medium animate-pulse">Fetching latest lead intelligence...</div>
            ) : enquiries.length === 0 ? (
                <div className="bg-white p-12 rounded-[3rem] border-2 border-dashed border-slate-100 text-center">
                    <div className="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <MessageSquare size={32} />
                    </div>
                    <h3 className="text-xl font-black text-slate-800 mb-2">No Enquiries Found</h3>
                    <p className="text-slate-500 max-w-md mx-auto font-medium">New leads from the landing page will appear here automatically.</p>
                </div>
            ) : (
                <div className="space-y-6">
                    <div className="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                        <table className="w-full border-collapse">
                            <thead>
                                <tr className="bg-slate-50 border-b border-slate-100">
                                    <th className="px-8 py-5 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Doctor / Clinic</th>
                                    <th className="px-8 py-5 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Contact Info</th>
                                    <th className="px-8 py-5 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Location / Spec</th>
                                    <th className="px-8 py-5 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Device Info</th>
                                    <th className="px-8 py-5 text-left text-xs font-black text-slate-400 uppercase tracking-widest">Created At</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {enquiries.map(enquiry => (
                                    <tr key={enquiry.id} className="hover:bg-slate-50/50 transition-colors">
                                        <td className="px-8 py-6">
                                            <div className="flex items-center gap-4">
                                                <div className="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary font-black">
                                                    {enquiry.name.charAt(0)}
                                                </div>
                                                <div>
                                                    <p className="font-black text-slate-800">{enquiry.name}</p>
                                                    <p className="text-xs font-bold text-slate-400">{enquiry.clinic_name}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="space-y-1">
                                                <p className="text-sm font-bold text-slate-600 flex items-center gap-2">
                                                    <Smartphone size={14} className="text-slate-400" /> {enquiry.phone}
                                                </p>
                                                {enquiry.whatsapp && (
                                                    <p className="text-xs font-bold text-green-500 flex items-center gap-2">
                                                        <Globe size={14} /> {enquiry.whatsapp}
                                                    </p>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="space-y-1">
                                                <p className="text-sm font-bold text-slate-600 flex items-center gap-2">
                                                    <MapPin size={14} className="text-slate-400" /> {enquiry.city}
                                                </p>
                                                <p className="text-xs font-bold text-slate-400 flex items-center gap-2">
                                                    <Stethoscope size={14} /> {enquiry.practice_type || 'General'}
                                                </p>
                                            </div>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="space-y-1">
                                                <p className="text-xs font-bold text-slate-600 flex items-center gap-2">
                                                    <Monitor size={14} className="text-slate-400" /> {enquiry.browser_name} / {enquiry.os_name}
                                                </p>
                                                <p className="text-[10px] font-black text-primary uppercase tracking-tighter">
                                                    {enquiry.device_type}
                                                </p>
                                            </div>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="text-sm font-bold text-slate-500 flex items-center gap-2">
                                                <Clock size={14} className="text-slate-400" />
                                                {new Date(enquiry.created_at).toLocaleDateString()}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {pagination.last_page > 1 && (
                        <div className="flex justify-between items-center px-8">
                            <p className="text-sm font-bold text-slate-400">Showing page {pagination.current_page} of {pagination.last_page}</p>
                            <div className="flex gap-2">
                                <button
                                    onClick={() => handlePageChange(pagination.current_page - 1)}
                                    disabled={pagination.current_page === 1}
                                    className="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-primary disabled:opacity-50 transition-all"
                                >
                                    <ChevronLeft size={20} />
                                </button>
                                <button
                                    onClick={() => handlePageChange(pagination.current_page + 1)}
                                    disabled={pagination.current_page === pagination.last_page}
                                    className="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-primary disabled:opacity-50 transition-all"
                                >
                                    <ChevronRight size={20} />
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
