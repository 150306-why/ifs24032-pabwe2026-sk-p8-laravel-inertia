import { useEffect } from 'react';
import { X } from 'lucide-react';

/** Modal layar penuh. Header (indigo gelap) dan footer (abu-abu) berbeda warna agar kontras. */
export default function FullScreenModal({ title, onClose, footer, children }) {
    useEffect(() => {
        const onKey = (event) => event.key === 'Escape' && onClose();
        document.addEventListener('keydown', onKey);
        document.body.style.overflow = 'hidden';

        return () => {
            document.removeEventListener('keydown', onKey);
            document.body.style.overflow = '';
        };
    }, [onClose]);

    return (
        <div className="fixed inset-0 z-50 flex h-screen w-screen flex-col bg-white" role="dialog" aria-modal="true" aria-label={title} data-modal="fullscreen">
            <header className="flex items-center justify-between bg-indigo-800 px-6 py-4 text-white">
                <h2 className="text-lg font-semibold">{title}</h2>
                <button type="button" onClick={onClose} aria-label="Tutup" className="cursor-pointer rounded-lg p-1.5 hover:bg-white/15">
                    <X className="h-5 w-5" />
                </button>
            </header>
            <div className="flex-1 overflow-y-auto bg-white px-6 py-6">
                <div className="mx-auto max-w-5xl">{children}</div>
            </div>
            <footer className="flex justify-end gap-3 border-t border-slate-300 bg-slate-100 px-6 py-4">{footer}</footer>
        </div>
    );
}
