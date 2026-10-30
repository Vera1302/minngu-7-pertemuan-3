import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'your-pusher-key', // bisa kosong untuk development
    wsHost: window.location.hostname,
    wsPort: 8080,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});
