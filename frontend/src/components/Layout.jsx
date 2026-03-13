import { useState } from 'react';
import { Outlet, Link, useNavigate } from 'react-router-dom';
import { ChevronDown, User, LogOut } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import NotificationBell from './NotificationBell';
import AdminSidebar from './layout/AdminSidebar';
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

function cn(...inputs) {
    return twMerge(clsx(inputs));
}

export default function Layout() {
    const { user, logout, loading, isImpersonating } = useAuth();
    const navigate = useNavigate();
    const [showProfileMenu, setShowProfileMenu] = useState(false);
    const [collapsed, setCollapsed] = useState(false);

    // Prevent UI flicker during auth context network requests
    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center bg-slate-50">
                <div className="animate-pulse flex flex-col items-center gap-4">
                    <div className="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                    <span className="text-slate-500 font-semibold text-sm">Loading Workspace...</span>
                </div>
            </div>
        );
    }

    const handleLogout = () => {
        logout();
        navigate('/login');
    };

    return (
        <div className={cn("flex min-h-screen w-full bg-slate-100", isImpersonating && "pt-10")}>
            {/* Sidebar Component */}
            <AdminSidebar
                collapsed={collapsed}
                toggle={() => setCollapsed(!collapsed)}
                isImpersonating={isImpersonating}
            />

            {/* Main Content Area */}
            <main className={cn(
                "flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out",
                collapsed ? "ml-20" : "ml-64"
            )}>
                {/* Header */}
                <header className={cn(
                    "h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky z-10 px-8 flex items-center justify-end gap-2",
                    isImpersonating ? "top-10" : "top-0"
                )}>
                    <div className="relative">
                        <NotificationBell />
                    </div>

                    <div className="relative">
                        <button
                            onClick={() => setShowProfileMenu(!showProfileMenu)}
                            className="flex items-center gap-2 hover:bg-slate-50 p-2 rounded-xl transition-all"
                        >
                            <div className="w-10 h-10 bg-indigo-50 border border-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-black text-xs">
                                {user?.name?.charAt(0)}
                            </div>
                            <ChevronDown size={16} className="text-slate-400" />
                        </button>

                        {showProfileMenu && (
                            <div className="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden py-2 animate-in fade-in slide-in-from-top-2">
                                <Link
                                    to="/profile"
                                    onClick={() => setShowProfileMenu(false)}
                                    className="px-4 py-3 flex items-center gap-3 text-slate-600 hover:bg-slate-50 hover:text-indigo-600 font-medium transition-colors"
                                >
                                    <User size={18} /> My Profile
                                </Link>
                                <button
                                    onClick={handleLogout}
                                    className="w-full text-left px-4 py-3 flex items-center gap-3 text-rose-500 hover:bg-rose-50 font-medium transition-colors"
                                >
                                    <LogOut size={18} /> Logout
                                </button>
                            </div>
                        )}
                    </div>
                </header>

                {/* Page Content Rendering */}
                <div className="p-8 flex-1 overflow-y-auto">
                    <Outlet />
                </div>
            </main>
        </div>
    );
}
