import { useState, useEffect } from 'react';
import api from '../api/axios';
import toast from 'react-hot-toast';
import { handleApiError } from '../utils/errorHandler';
import { ShoppingBag, Search, Plus, Trash2, Calendar, TrendingDown, DollarSign, Filter } from 'lucide-react';

export default function Expenses() {
    const [expenses, setExpenses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showAddModal, setShowAddModal] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [categoryFilter, setCategoryFilter] = useState('All');

    // Form State
    const [formData, setFormData] = useState({
        category: 'Rent',
        amount: '',
        date: new Date().toISOString().split('T')[0],
        description: ''
    });

    const categories = ['Rent', 'Utilities', 'Staff Salary', 'Medical Supplies', 'Marketing', 'Maintenance', 'Insurance', 'Other'];

    useEffect(() => {
        fetchExpenses();
    }, []);

    const fetchExpenses = async () => {
        try {
            const res = await api.get('/expenses');
            setExpenses(Array.isArray(res.data) ? res.data : res.data?.data ?? []);
        } catch (err) {
            toast.error('Failed to load expenses');
        } finally {
            setLoading(false);
        }
    };

    const handleCreate = async (e) => {
        e.preventDefault();

        try {
            const payload = {
                ...formData,
                expense_date: formData.date,
            };

            delete payload.date;

            await api.post('/expenses', payload);

            toast.success('Expense recorded');
            setShowAddModal(false);
            setFormData({
                category: 'Rent',
                amount: '',
                date: new Date().toISOString().split('T')[0],
                description: ''
            });

        } catch (err) {
            return;
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Permanent delete. Are you sure?')) return;
        try {
            await api.delete(`/admin/delete/expense/${id}/archive`);
            toast.success('Deleted');
            fetchExpenses();
        } catch (err) {
            toast.error('Delete failed');
        }
    };

    const filteredExpenses = (Array.isArray(expenses) ? expenses : []).filter(ex => {
        const matchesSearch = ex.category?.toLowerCase().includes(searchQuery.toLowerCase()) ||
            ex.description?.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesCategory = categoryFilter === 'All' || ex.category === categoryFilter;
        return matchesSearch && matchesCategory;
    });

    const totalMonthly = filteredExpenses.reduce((acc, curr) => acc + parseFloat(curr.amount), 0);

    return (
        <div className="space-y-6">
            <header className="flex justify-between items-end">
                <div>
                    <h1 className="text-3xl font-extrabold text-slate-800">Clinic Expenses</h1>
                    <p className="text-slate-500">Track and categorize every penny spent on your clinic operations.</p>
                </div>
                <button
                    onClick={() => setShowAddModal(true)}
                    className="bg-primary text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-primary-dark transition-all shadow-lg shadow-primary/20"
                >
                    <Plus size={20} />
                    Record Expense
                </button>
            </header>

            {/* Stats Summary */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
                    <div className="p-4 bg-red-50 rounded-2xl text-red-600"><TrendingDown size={32} /></div>
                    <div>
                        <p className="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Filtered Spending</p>
                        <p className="text-3xl font-black text-slate-800">₹{totalMonthly.toLocaleString()}</p>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex items-center gap-4">
                    <div className="p-4 bg-primary/5 rounded-2xl text-primary"><ShoppingBag size={32} /></div>
                    <div>
                        <p className="text-xs font-bold text-slate-400 uppercase tracking-wider">Operations Overhead</p>
                        <p className="text-3xl font-black text-slate-800">{filteredExpenses.length} Records</p>
                    </div>
                </div>
            </div>

            {/* Filters */}
            <div className="flex flex-col md:flex-row gap-4">
                <div className="relative flex-1">
                    <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                    <input
                        type="text"
                        placeholder="Search descriptions..."
                        className="w-full pl-12 pr-4 py-3 rounded-2xl border border-slate-200 outline-none focus:ring-2 focus:ring-primary shadow-sm transition-all"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                </div>
                <div className="flex items-center gap-2 bg-white px-4 py-1 rounded-2xl border border-slate-200 shadow-sm">
                    <Filter size={18} className="text-slate-400" />
                    <select
                        className="bg-transparent py-2 outline-none font-bold text-slate-700 cursor-pointer"
                        value={categoryFilter}
                        onChange={(e) => setCategoryFilter(e.target.value)}
                    >
                        <option>All</option>
                        {categories.map(c => <option key={c} value={c}>{c}</option>)}
                    </select>
                </div>
            </div>

            {/* Expense List */}
            <div className="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="bg-slate-50/50 border-b border-slate-100">
                                <th className="px-6 py-5 font-black text-slate-500 text-xs uppercase tracking-widest">Date</th>
                                <th className="px-6 py-5 font-black text-slate-500 text-xs uppercase tracking-widest">Category</th>
                                <th className="px-6 py-5 font-black text-slate-500 text-xs uppercase tracking-widest">Description</th>
                                <th className="px-6 py-5 font-black text-slate-500 text-xs uppercase tracking-widest">Amount</th>
                                <th className="px-6 py-5 font-black text-slate-500 text-xs uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-50">
                            {loading ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-slate-400">Syncing ledgers...</td></tr>
                            ) : filteredExpenses.length === 0 ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-slate-400 font-medium italic">No expenses found for this criteria.</td></tr>
                            ) : (
                                filteredExpenses.map(ex => (
                                    <tr key={ex.id} className="hover:bg-slate-50/30 transition-colors group">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-2 text-slate-600 font-medium">
                                                <Calendar size={14} className="text-slate-400" />
                                                {new Date(ex.date).toLocaleDateString()}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                                {ex.category}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="text-slate-700 font-medium">{ex.description || '-'}</div>
                                        </td>
                                        <td className="px-6 py-4 font-black text-slate-800">₹{parseFloat(ex.amount).toLocaleString()}</td>
                                        <td className="px-6 py-4 text-right">
                                            <button
                                                onClick={() => handleDelete(ex.id)}
                                                className="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all opacity-0 group-hover:opacity-100"
                                            >
                                                <Trash2 size={18} />
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Add Modal */}
            {showAddModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-lg rounded-[2.5rem] p-10 shadow-2xl">
                        <h2 className="text-3xl font-black text-slate-800 mb-8">Record New Expense</h2>
                        <form onSubmit={handleCreate} className="space-y-6">
                            <div>
                                <label className="block text-sm font-black text-slate-700 mb-2">Category</label>
                                <select
                                    value={formData.category} onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                                    className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold bg-slate-50"
                                >
                                    {categories.map(c => <option key={c} value={c}>{c}</option>)}
                                </select>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-black text-slate-700 mb-2">Amount (₹)</label>
                                    <input
                                        type="number" required value={formData.amount} onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                                        placeholder="0.00"
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-black text-slate-700 mb-2">Billing Date</label>
                                    <input
                                        type="date" required value={formData.date} onChange={(e) => setFormData({ ...formData, date: e.target.value })}
                                        className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold"
                                    />
                                </div>
                            </div>
                            <div>
                                <label className="block text-sm font-black text-slate-700 mb-2">Description / Memo</label>
                                <textarea
                                    value={formData.description} onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                    placeholder="What was this expenditure for?"
                                    className="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-medium h-24"
                                ></textarea>
                            </div>
                            <div className="flex justify-end gap-4 pt-4">
                                <button type="button" onClick={() => setShowAddModal(false)} className="px-8 py-4 font-black text-slate-400 hover:bg-slate-100 rounded-2xl transition-all">Cancel</button>
                                <button type="submit" className="px-10 py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark shadow-2xl shadow-primary/30 transition-all">Record Transaction</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
