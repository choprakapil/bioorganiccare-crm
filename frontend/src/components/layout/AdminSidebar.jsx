import { NavLink } from 'react-router-dom';
import {
    LayoutDashboard, Users, Pill, Stethoscope, ShieldCheck, Sliders,
    MessageSquare, Calendar, IndianRupee, Sparkles, Settings,
    ShieldAlert, ChevronLeft, ChevronRight, CreditCard, ShoppingBag
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

function cn(...inputs) {
    return twMerge(clsx(inputs));
}

// Scalable Configuration Pattern for Menus
const ADMIN_NAV_CONFIG = [
    { type: 'section', label: 'Management' },
    { to: "/", icon: <LayoutDashboard size={20} />, label: "System Hub" },
    { to: "/admin/doctors", icon: <Users size={20} />, label: "Doctor Accounts" },
    { type: 'section', label: 'Catalog' },
    { to: "/admin/catalog", icon: <Stethoscope size={20} />, label: "Clinical Catalog" },
    { to: "/admin/pharmacy", icon: <Pill size={20} />, label: "Pharmacy Governance" },
    { type: 'section', label: 'Policy' },
    { to: "/admin/plans", icon: <ShieldCheck size={20} />, label: "Plan Control" },
    { to: "/admin/governance", icon: <ShieldAlert size={20} />, label: "Governance" },
    { to: "/admin/enquiries", icon: <MessageSquare size={20} />, label: "Landing Enquiries" },
    { to: "/admin/settings", icon: <Sliders size={20} />, label: "System Policy" },
];

const DOCTOR_NAV_CONFIG = [
    { type: 'section', label: 'Dashboard' },
    { to: "/", icon: <LayoutDashboard size={20} />, label: "Overview" },
    { type: 'section', label: 'Clinical' },
    { to: "/patients", icon: <Users size={20} />, label: "Patients", moduleKey: "patient_registry" },
    { to: "/appointments", icon: <Calendar size={20} />, label: "Appointments", moduleKey: "appointments" },
    { to: "/services", icon: <Stethoscope size={20} />, label: "All Services", moduleKey: "clinical_services" },
    { type: 'section', label: 'Pharmacy' },
    { to: "/inventory", icon: <Pill size={20} />, label: "Stock Control", moduleKey: "pharmacy" },
    { type: 'section', label: 'Finance' },
    { to: "/finance/dashboard", icon: <IndianRupee size={20} />, label: "Finance Hub", moduleKey: "billing" },
    { to: "/billing", icon: <CreditCard size={20} />, label: "Transactions", moduleKey: "billing" },
    { to: "/expenses", icon: <ShoppingBag size={20} />, label: "Expenses", moduleKey: "expenses" },
    { type: 'section', label: 'System' },
    { to: "/insights", icon: <Sparkles size={20} />, label: "Growth AI", moduleKey: "growth_insights" },
    { to: "/settings", icon: <Settings size={20} />, label: "Clinic Settings", moduleKey: "settings" },
];

export default function AdminSidebar({ collapsed, toggle, isImpersonating }) {
    const { user, hasModule } = useAuth();
    const isAdmin = user?.role === 'super_admin';

    return (
        <aside className={cn(
            "bg-slate-900 border-r border-slate-800 flex flex-col fixed h-screen z-20 transition-all duration-300 ease-in-out shadow-2xl overflow-hidden",
            collapsed ? "w-20" : "w-64",
            isImpersonating ? "top-10 pb-10" : "top-0"
        )}>
            {/* Logo Area */}
            <div className={cn(
                "flex items-center p-6 bg-slate-950/50 backdrop-blur-md mb-2 transition-all duration-300 min-h-[80px]",
                collapsed ? "justify-center" : "justify-between"
            )}>
                <div className="flex items-center gap-3 overflow-hidden">
                    {user?.clinic_logo ? (
                        <img src={user.clinic_logo} alt="Logo" className="w-8 h-8 rounded-lg object-contain bg-white p-1 shrink-0" />
                    ) : (
                        <div className="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-black text-xs shrink-0 shadow-lg shadow-indigo-900/20">
                            {isAdmin ? 'A' : (user?.clinic_name?.charAt(0) || 'D')}
                        </div>
                    )}
                    {!collapsed && (
                        <h2 className="text-xl font-black text-white tracking-tighter truncate uppercase italic">
                            {isAdmin ? 'Aura Central' : (user?.clinic_name || 'Aura Dental')}
                        </h2>
                    )}
                </div>

                {!collapsed && (
                    <button
                        onClick={toggle}
                        className="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition-all hover:bg-slate-800"
                    >
                        <ChevronLeft size={16} />
                    </button>
                )}
            </div>

            {collapsed && (
                <div className="px-4 py-2 flex justify-center border-b border-slate-800/50 mb-2">
                    <button
                        onClick={toggle}
                        className="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-all"
                    >
                        <ChevronRight size={16} />
                    </button>
                </div>
            )}

            <nav className="flex-1 px-4 space-y-1 mt-4 overflow-y-auto custom-scrollbar overflow-x-hidden">
                {(isAdmin ? ADMIN_NAV_CONFIG : DOCTOR_NAV_CONFIG).map((item, idx) => {
                    if (item.type === 'section') {
                        if (collapsed) return <div key={idx} className="h-px bg-slate-800/50 mx-2 my-6" />;
                        return (
                            <div key={idx} className="px-4 mt-8 mb-3 whitespace-nowrap">
                                <p className="text-[10px] font-black uppercase tracking-widest text-slate-500">
                                    {item.label}
                                </p>
                            </div>
                        );
                    }

                    const showItem = !item.moduleKey || hasModule(item.moduleKey);
                    if (!showItem) return null;

                    return (
                        <NavItem
                            key={idx}
                            to={item.to}
                            icon={item.icon}
                            label={item.label}
                            collapsed={collapsed}
                        />
                    );
                })}
            </nav>

            <div className="p-4 border-t border-slate-800 bg-slate-950/30">
                <div className={cn(
                    "px-4 py-3 bg-slate-900/40 rounded-xl flex items-center gap-3 border border-slate-800/50 transition-all",
                    collapsed ? "justify-center px-2" : ""
                )}>
                    <div className="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center text-indigo-400 font-bold text-xs shadow-inner shrink-0 border border-slate-700">
                        {user?.name?.charAt(0)}
                    </div>
                    {!collapsed && (
                        <div className="flex-1 overflow-hidden">
                            <p className="text-xs font-bold text-slate-200 truncate">{user?.name}</p>
                            <p className="text-[10px] text-slate-500 truncate capitalize font-black tracking-widest">{user?.role?.replace('_', ' ')}</p>
                        </div>
                    )}
                </div>
            </div>
        </aside>
    );
}

function NavItem({ to, icon, label, collapsed }) {
    return (
        <NavLink
            to={to}
            className={({ isActive }) => cn(
                "flex items-center gap-3 px-4 py-3 rounded-lg transition-all font-semibold group relative whitespace-nowrap",
                isActive
                    ? "bg-slate-800 text-white shadow-lg shadow-slate-950/20"
                    : "text-slate-400 hover:bg-slate-800/60 hover:text-white text-md"
            )}
            title={collapsed ? label : ""}
        >
            <div className={cn(
                "flex shrink-0 transition-all duration-300 ease-in-out",
                collapsed ? "w-full justify-center" : ""
            )}>
                {icon}
            </div>

            <span className={cn(
                "transition-all duration-300 overflow-hidden",
                collapsed ? "max-w-0 opacity-0 invisible" : "max-w-[200px] opacity-100 visible"
            )}>
                {label}
            </span>

            {/* Tooltip for collapsed mode */}
            {collapsed && (
                <div className="absolute left-full ml-4 px-3 py-2 bg-slate-950 text-white text-[10px] font-black uppercase tracking-widest rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-all duration-200 translate-x-1 group-hover:translate-x-0 whitespace-nowrap z-50 shadow-2xl border border-slate-800">
                    {label}
                </div>
            )}
        </NavLink>
    );
}
