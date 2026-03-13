import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { getMySubscription } from '../api/subscription';
import toast from 'react-hot-toast';

const SubscriptionContext = createContext(null);

export const useSubscription = () => {
    const context = useContext(SubscriptionContext);
    if (!context) {
        throw new Error('useSubscription must be used within a SubscriptionProvider');
    }
    return context;
};

export const SubscriptionProvider = ({ children }) => {
    const [subscription, setSubscription] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isBlocked, setIsBlocked] = useState(false);

    // Check if user is logged in before fetching
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const isDoctor = user.role === 'doctor';

    const fetchSubscription = useCallback(async () => {
        if (!token || !isDoctor) {
            setLoading(false);
            return;
        }

        try {
            setLoading(true);
            const data = await getMySubscription();

            // Format Data if needed, or store raw
            setSubscription(data);
            setIsBlocked(data.usage?.is_blocked || false);

            // Check warnings immediately
            if (data.lifecycle.status === 'past_due') {
                toast('Your subscription is past due. Please renew soon.', {
                    icon: '⚠️',
                    duration: 5000,
                });
            }
        } catch (error) {
            toast.error(error.response?.data?.message || 'Unexpected error occurred');
        } finally {
            setLoading(false);
        }
    }, [token, isDoctor]);

    useEffect(() => {
        fetchSubscription();

        // Listen for Global Events from Axios Interceptor
        const handleBlocked = (e) => {
            setIsBlocked(true);
            toast.error(e.detail || "Subscription inactive. Action blocked.");
        };

        const handleLimit = (e) => {
            toast.error(e.detail || "Plan limit reached.");
        };

        window.addEventListener('subscription-blocked', handleBlocked);
        window.addEventListener('plan-limit-reached', handleLimit);

        return () => {
            window.removeEventListener('subscription-blocked', handleBlocked);
            window.removeEventListener('plan-limit-reached', handleLimit);
        };
    }, [fetchSubscription]);

    return (
        <SubscriptionContext.Provider value={{
            subscription,
            loading,
            isBlocked,
            refreshSubscription: fetchSubscription
        }}>
            {children}
        </SubscriptionContext.Provider>
    );
};
