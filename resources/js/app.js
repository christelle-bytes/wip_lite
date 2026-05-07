import '../css/app.css';
import axios from 'axios';
// import 'primevue/resources/themes/lara-light-blue/theme.css';
// le chemin de thème peut ne pas exister dans votre version PrimeVue.
// On garde le thème fourni par PrimeUIX (Aura) plus le CSS de base PrimeVue.

// import 'primevue/resources/primevue.min.css';
// PrimeVue 4.5.x n’expose pas forcément ce fichier minifié dans resources.
// import 'primevue/resources/primevue.css';
// Chemin CSS primevue peut ne pas exister selon la version/pack.
// On utilise PrimeUIX (Aura) + primeicons + Tailwind.

import 'primeicons/primeicons.css';
import 'primevue/config';

window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// import './bootstrap';
 
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
// adding
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';
 
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
 
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: '.dark',
                    }
                }
            })
            .use(ToastService)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});