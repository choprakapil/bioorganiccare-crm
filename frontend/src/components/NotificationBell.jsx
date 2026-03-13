import { useState, useEffect, useRef } from 'react';
import { Bell, Check, Info, AlertTriangle, XCircle } from 'lucide-react';
import api from '../api/axios';
import toast from 'react-hot-toast';

export default function NotificationBell() {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [isOpen, setIsOpen] = useState(false);
    const wrapperRef = useRef(null);

    useEffect(() => {
        fetchNotifications();
        // Poll every 60s
        const interval = setInterval(fetchNotifications, 60000);
        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        setUnreadCount(notifications.filter(n => !n.is_read).length);
    }, [notifications]);

    useEffect(() => {
        function handleClickOutside(event) {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, [wrapperRef]);

    const fetchNotifications = async () => {
        try {
            const res = await api.get('/notifications');
            setNotifications(res.data);
        } catch (err) {
            toast.error(err.response?.data?.message || 'Unexpected error occurred');
        }
    };

    const markAsRead = async (id) => {
        try {
            await api.patch(`/notifications/${id}/read`);
            setNotifications(notifications.map(n => n.id === id ? { ...n, is_read: true } : n));
        } catch (err) {
            toast.error(err.response?.data?.message || 'Unexpected error occurred');
        }
    };

    const markAllRead = async () => {
        try {
            await api.post('/notifications/read-all');
            setNotifications(notifications.map(n => ({ ...n, is_read: true })));
            toast.success('All marked as read');
        } catch (err) {
            toast.error('Failed to mark all read');
        }
    };

    const getIcon = (type) => {
        switch (type) {
            case 'success': return <Check size={16} className="text-green-500" />;
            case 'warning': return <AlertTriangle size={16} className="text-amber-500" />;
            case 'error': return <XCircle size={16} className="text-red-500" />;
            default: return <Info size={16} className="text-blue-500" />;
        }
    };

    return (
        <div className="relative" ref={wrapperRef}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="p-3 text-slate-400 hover:bg-slate-50 hover:text-slate-600 rounded-xl transition-all relative"
            >
                <Bell size={20} />
                {unreadCount > 0 && (
                    <span className="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                )}
            </button>

            {isOpen && (
                <div className="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in slide-in-from-top-2 z-50">
                    <div className="p-4 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <h3 className="font-bold text-slate-800">Notifications</h3>
                        {unreadCount > 0 && (
                            <button onClick={markAllRead} className="text-xs font-bold text-primary hover:text-primary-dark">
                                Mark all read
                            </button>
                        )}
                    </div>
                    <div className="max-h-[400px] overflow-y-auto">
                        {notifications.length === 0 ? (
                            <div className="p-8 text-center text-slate-400 text-sm italic">
                                No new notifications.
                            </div>
                        ) : (
                            <div className="divide-y divide-slate-50">
                                {notifications.map(n => (
                                    <div
                                        key={n.id}
                                        className={`p-4 hover:bg-slate-50 transition-colors flex gap-3 ${!n.is_read ? 'bg-blue-50/30' : ''}`}
                                        onClick={() => markAsRead(n.id)}
                                    >
                                        <div className={`mt-1 p-2 rounded-full ${!n.is_read ? 'bg-white shadow-sm' : 'bg-transparent'}`}>
                                            {getIcon(n.type)}
                                        </div>
                                        <div>
                                            <h4 className={`text-sm ${!n.is_read ? 'font-bold text-slate-800' : 'font-medium text-slate-600'}`}>
                                                {n.title}
                                            </h4>
                                            <p className="text-xs text-slate-500 mt-0.5 leading-relaxed">{n.message}</p>
                                            <p className="text-[10px] text-slate-400 mt-2 font-medium">
                                                {new Date(n.created_at).toLocaleDateString()} {new Date(n.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
