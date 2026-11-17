import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { options } from 'preact';

// React (yang digantikan Preact) selalu menjalankan useEffect tertunda sebelum memproses event berikutnya.
// Preact defaultnya menunda effect sampai setelah paint, sehingga useForm Inertia (yang menyinkronkan
// data lewat useEffect) bisa memakai data basi dan kehilangan karakter saat mengetik cepat.
// Jalankan effect sebagai microtask tepat setelah render agar perilakunya sama dengan React.
options.requestAnimationFrame = (callback) => queueMicrotask(callback);

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
