import React from 'react';
import { useNavigate } from 'react-router-dom';

const AccessDenied = () => {
    const navigate = useNavigate();

    return (
        <div className="flex h-screen flex-col items-center justify-center bg-gray-50 text-center">
            <div className="mb-4 rounded-full bg-red-100 p-4">
                <svg className="h-12 w-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m0 0v2m0-2h2m-2 0H8m4-6V4m0 0v8m0-8h2m-2 0H8m4 6V4" />
                </svg>
            </div>
            <h1 className="mb-2 text-3xl font-bold text-gray-900">Access Denied</h1>
            <p className="mb-8 text-gray-600">You do not have permission to view this page.</p>
            <button
                onClick={() => navigate('/')}
                className="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Go to Dashboard
            </button>
        </div>
    );
};

export default AccessDenied;
