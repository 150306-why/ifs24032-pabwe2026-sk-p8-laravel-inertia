import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

// Lazy-load per halaman: setiap halaman (dan library beratnya, mis. TOAST UI Editor)
// dipecah ke chunk sendiri sehingga JS/CSS awal jauh lebih kecil.
const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    title: (title) => (title ? `${title} · BlogVerse Pro` : 'BlogVerse Pro'),
    resolve: (name) => pages[`./Pages/${name}.jsx`](),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    // Top Progress Bar saat navigasi.
    progress: { color: '#4f46e5', showSpinner: false },
});
