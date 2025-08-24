import axios from 'axios';
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.axios = axios;
window.Pusher = Pusher;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === "https",
    enabledTransports: ["ws", "wss"],
});

// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
//     forceTLS: false,
// });
