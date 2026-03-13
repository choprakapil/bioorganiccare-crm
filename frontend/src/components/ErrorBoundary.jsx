import React from 'react';

class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        console.error("Critical Runtime Error:", error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="min-h-screen flex items-center justify-center bg-slate-50 p-6">
                    <div className="max-w-md w-full bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-12 text-center animate-in fade-in zoom-in duration-300">
                        <div className="w-20 h-20 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500 mx-auto mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" /><path d="M12 9v4" /><path d="M12 17h.01" /></svg>
                        </div>
                        <h2 className="text-2xl font-black text-slate-800 tracking-tight mb-2">Something went wrong</h2>
                        <p className="text-slate-500 font-medium mb-8">The workspace encountered an unexpected state. Your data is safe.</p>
                        <button
                            onClick={() => window.location.reload()}
                            className="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-primary-dark transition-all shadow-xl shadow-primary/20 active:scale-95"
                        >
                            Reload Workspace
                        </button>
                    </div>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
