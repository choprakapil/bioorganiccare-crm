import axios from 'axios';
import { handleApiError } from '../utils/errorHandler';

export const API_BASE_URL = import.meta.env.DEV
    ? 'http://127.0.0.1:8000'
    : '/api';

const api = axios.create({
    baseURL: API_BASE_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

api.interceptors.request.use((config) => {
    const authToken = localStorage.getItem('auth_token');


    const impersonating = localStorage.getItem('impersonating');
    if (impersonating === 'true') {
        config.headers['X-Impersonating'] = 'true';
    }

    if (authToken) {
        config.headers.Authorization = `Bearer ${authToken}`;
    }

    return config;
});

let isLoggingOut = false;

export const resetAuthFlag = () => {
    isLoggingOut = false;
};

api.interceptors.response.use(
    (response) => {
        // ... previous logic
        const warning = response.headers['x-subscription-warning'];
        if (warning) {
            window.dispatchEvent(new CustomEvent('subscription-warning', { detail: warning }));
        }
        return response;
    },
    (error) => {
        if (error.response) {
            const { status, data } = error.response;

            // 401: Unauthorized - Prevent recursive loop
            if (status === 401 && !isLoggingOut) {
                isLoggingOut = true;
                localStorage.removeItem('auth_token');
                window.dispatchEvent(new Event('auth-expired'));
            }

            // 402: Payment Required (Subscription Expired/Limit Reached for Write Op)
            if (status === 402) {
                window.dispatchEvent(new CustomEvent('subscription-blocked', {
                    detail: data.message || "Subscription inactive. Please contact admin."
                }));
            }

            // 422: Unprocessable Entity (Often validation or logic limits)
            if (status === 422 && data.message && data.message.toLowerCase().includes('limit')) {
                window.dispatchEvent(new CustomEvent('plan-limit-reached', {
                    detail: data.message
                }));
                // Also toast directly here for immediate feedback
                import('react-hot-toast').then(({ default: toast }) => {
                    toast.error(data.message, { duration: 5000, icon: '🚀' });
                });
            }

            // 403: Specific Limit Reached Code (Legacy Support)
            if (status === 403 && data?.code === 'LIMIT_REACHED') {
                import('react-hot-toast').then(({ default: toast }) => {
                    toast.error(data.message, { duration: 5000, icon: '🚀' });
                });
            }
        }
        handleApiError(error);
        return Promise.reject(error);
    }
);

export default api;
