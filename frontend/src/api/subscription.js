import api from './axios';

// Subscription API Methods
export const getMySubscription = async () => {
    const response = await api.get('/subscription/me');
    return response.data;
};
