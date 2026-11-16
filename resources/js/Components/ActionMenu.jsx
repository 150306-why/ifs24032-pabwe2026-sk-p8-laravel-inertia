import { Link } from '@inertiajs/react';
import { EllipsisVertical } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

/** Dropdown tindakan dengan tombol vertical dots (⋮). */
export default function ActionMenu({ children }) {
    const [open, setOpen] = useState(false);
    const wrapper = useRef(null);

    useEffect(() => {
        const onClick = (event) => {
            if (wrapper.current && !wrapper.current.contains(event.target)) setOpen(false);
        };
        document.addEventListener('mousedown', onClick);
        return () => document.removeEventListener('mousedown', onClick);
    }, []);

    return (
        <div ref={wrapper} className="relative">
            <button type="button" aria-label="Menu tindakan" aria-haspopup="menu" aria-expanded={open} onClick={() => setOpen((value) => !value)} className="cursor-pointer rounded-full p-2 text-slate-500 hover:bg-slate-100">
                <EllipsisVertical className="h-5 w-5" />
            </button>
            {open && (
                <div role="menu" onClick={() => setOpen(false)} className="absolute right-0 z-30 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                    {children}
                </div>
            )}
        </div>
    );
}

export function MenuItem({ icon: Icon, href, onClick, danger = false, children }) {
    const className = `flex w-full cursor-pointer items-center gap-2 px-4 py-2 text-left text-sm ${danger ? 'text-red-600 hover:bg-red-50' : 'text-slate-700 hover:bg-slate-50'}`;
    const content = (
        <>
            <Icon className="h-4 w-4" />
            {children}
        </>
    );

    return href ? (
        <Link href={href} role="menuitem" className={className}>{content}</Link>
    ) : (
        <button type="button" role="menuitem" onClick={onClick} className={className}>{content}</button>
    );
}
