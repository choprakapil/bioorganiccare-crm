import React from 'react';

const AdminPageHeader = ({ title, description, actions }) => {
    return (
        <div className="space-y-6 mb-8">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-black text-slate-900 tracking-tight">
                        {title}
                    </h1>
                    {description && (
                        <p className="text-slate-500 font-medium mt-1 max-w-2xl">
                            {description}
                        </p>
                    )}
                </div>
                {actions && (
                    <div className="flex items-center gap-3 shrink-0">
                        {actions}
                    </div>
                )}
            </div>
            <div className="h-px w-full bg-slate-200/60 shadow-sm" />
        </div>
    );
};

export default AdminPageHeader;
