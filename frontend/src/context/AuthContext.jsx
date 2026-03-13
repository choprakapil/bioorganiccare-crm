import { createContext, useContext, useState, useEffect } from 'react';
import api, { resetAuthFlag } from '../api/axios';
import toast from 'react-hot-toast';
import { useNavigate } from 'react-router-dom';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [enabledModules, setEnabledModules] = useState([]);
    const [loading, setLoading] = useState(true);
    const [isImpersonating, setIsImpersonating] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        if (localStorage.getItem('impersonating') === 'true') {
            setIsImpersonating(true);
        }
    }, []);

    useEffect(() => {
        const handler = () => {
            logout();
            navigate('/login');
        };
        window.addEventListener('auth-expired', handler);
        return () => window.removeEventListener('auth-expired', handler);
    }, []);

    useEffect(() => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            // Fetch user profile to verify token
            api.get('/me')
                .then(res => {
                    setUser(res.data.user);
                    setEnabledModules(res.data.enabled_modules || []);
                })
                .catch(() => {
                    localStorage.removeItem('auth_token');
                    setUser(null);
                    setEnabledModules([]);
                })
                .finally(() => setLoading(false));
        } else {
            setLoading(false);
        }
    }, []);

    const login = async (email, password) => {
        const response = await api.post('/login', { email, password });
        const { access_token } = response.data;
        localStorage.setItem('auth_token', access_token);

        // Strategy: Always fetch fresh profile after login to ensure 
        // consistent hydration of modules and doctor metadata.
        const profile = await refreshUser();
        return profile.user;
    };

    const logout = async (isExpired = false) => {
        try {
            // Only attempt server-side logout if the token is likely still valid
            if (!isExpired) {
                await api.post('/logout');
            }
        } catch (e) {
            console.warn('Logout request failed, cleaning local state anyway.');
        } finally {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('impersonating');
            localStorage.removeItem('impersonator_id');
            setIsImpersonating(false);
            setUser(null);
            setEnabledModules([]);
            resetAuthFlag(); // Unlock the axios circuit breaker
        }
    };

    const startImpersonation = async (doctorId) => {
        try {
            const res = await api.post(`/admin/impersonate/${doctorId}`);

            localStorage.setItem('impersonating', 'true');
            localStorage.setItem('impersonator_id', res.data.impersonator_id);
            localStorage.setItem('auth_token', res.data.token);

            await refreshUser();
            setIsImpersonating(true);
            navigate('/');
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to start impersonation');
        }
    };

    const stopImpersonation = async () => {
        try {
            const res = await api.post('/admin/stop-impersonation');

            localStorage.setItem('auth_token', res.data.token);

            localStorage.removeItem('impersonating');
            localStorage.removeItem('impersonator_id');

            await refreshUser();
            setIsImpersonating(false);
            navigate('/admin/doctors');
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to stop impersonation');
        }
    };

    const refreshUser = async () => {
        try {
            const res = await api.get('/me');
            setUser(res.data.user);
            setEnabledModules(res.data.enabled_modules || []);
            return res.data;
        } catch (err) {
            toast.error(err.response?.data?.message || 'Unexpected error occurred');
        }
    };

    useEffect(() => {
        if (user?.brand_color) {
            document.documentElement.style.setProperty('--primary', user.brand_color);
        } else {
            document.documentElement.style.setProperty('--primary', '#4f46e5'); // Default
        }
    }, [user?.brand_color]);

    useEffect(() => {
        if (!user || user.role === 'super_admin') return;

        const doctorId = user.role === 'staff'
            ? user.doctor_id
            : user.id;

        console.log(`Subscribing to doctor.${doctorId}`);

        const channel = window.Echo.private(`doctor.${doctorId}`);

        channel.listen('.plan.updated', (event) => {
            console.log('⚡ Plan Updated Event Received:', event);
            refreshUser();
        });

        return () => {
            channel.stopListening('.plan.updated');
            window.Echo.leaveChannel(`doctor.${doctorId}`);
        };
    }, [user]);

    const isPro = user?.plan?.name === 'Pro';

    const can = (feature) => {
        const planFeatures = user?.plan?.features || {};
        // Support nested modules structure or legacy flat structure
        const access = planFeatures.modules ? planFeatures.modules[feature] : planFeatures[feature];
        return access === true;
    };

    const hasModule = (key) => enabledModules.includes(key);

    return (
        <AuthContext.Provider value={{
            user,
            loading,
            authenticated: !!user,
            enabledModules,
            isImpersonating,
            hasModule,
            login,
            logout,
            startImpersonation,
            stopImpersonation,
            refreshUser,
            isPro,
            can
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
