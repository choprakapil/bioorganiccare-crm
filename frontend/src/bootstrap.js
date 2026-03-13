import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
Pusher.logToConsole = true;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'q9huxk8s64ih6muky7tl',
    wsHost: window.location.hostname,
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws'],
    authEndpoint: import.meta.env.VITE_API_URL ? `${import.meta.env.VITE_API_URL}/broadcasting/auth` : 'http://localhost:8000/api/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        },
    },
});

console.log('Echo initialized');
