import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    const label = (link, index) => (index === 0 ? '‹' : index === links.length - 1 ? '›' : link.label);

    return (
        <nav className="mt-8 flex flex-wrap justify-center gap-1" aria-label="Paginasi">
            {links.map((link, index) => {
                const base = 'min-w-9 rounded-lg border px-3 py-1.5 text-center text-sm';
                if (!link.url) return <span key={index} className={`${base} border-slate-200 text-slate-300`}>{label(link, index)}</span>;
                return (
                    <Link key={index} href={link.url} preserveScroll className={`${base} cursor-pointer ${link.active ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'}`}>
                        {label(link, index)}
                    </Link>
                );
            })}
        </nav>
    );
}
