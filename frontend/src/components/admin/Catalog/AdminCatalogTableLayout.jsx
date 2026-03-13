import React from 'react';

// AdminCatalogTableLayout.jsx
export default function AdminCatalogTableLayout({ title, stats, filters, actions, tableContent }) {
    return (
        <div className="p-6">
            <h1 className="text-2xl font-bold mb-4">{title}</h1>
            {stats && <div className="mb-4">{stats}</div>}
            {filters && <div className="mb-4">{filters}</div>}

            <div className="bg-white border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden mb-6">
                <div className="p-4 flex justify-between items-center border-b border-slate-100">
                    <h2 className="text-lg font-semibold px-4">Catalog List</h2>
                    <div className="flex gap-2">
                        {actions}
                    </div>
                </div>
                <div className="w-full overflow-x-auto">
                    {tableContent}
                </div>
            </div>
        </div>
    );
}
