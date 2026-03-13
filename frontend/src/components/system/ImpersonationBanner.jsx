import React from 'react';
import { useAuth } from '../../context/AuthContext';

export default function ImpersonationBanner() {
    const { isImpersonating, stopImpersonation } = useAuth();

    if (!isImpersonating) return null;

    return (
        <div className="bg-red-600 text-white text-center h-10 flex items-center justify-center px-4 shadow-md font-bold z-[100] fixed top-0 w-full left-0 gap-4">
            You are impersonating this doctor.
            <button
                onClick={stopImpersonation}
                className="bg-white text-red-600 px-3 py-1 rounded text-sm font-bold hover:bg-red-50 transition-colors shadow-sm"
            >
                Return to Admin
            </button>
        </div>
    );
}
