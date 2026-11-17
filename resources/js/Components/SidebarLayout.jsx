import { Link, router, usePage } from '@inertiajs/react';
import { BookOpen, LogOut, Menu, X } from 'lucide-react';
import { useState } from 'react';
import FlashMessages from './FlashMessages';
import ProcessingPill from './ProcessingPill';

/** Kerangka panel (sidebar + konten) yang dipakai AppLayout (penulis) dan AdminLayout (admin). */
export default function SidebarLayout({ roleLabel, nav, dark = false, children }) {
    const page = usePage();
    const { auth } = page.props;
    const [open, setOpen] = useState(false);

    const isActive = (href) => (href === '/' ? page.url === '/' : page.url.startsWith(href));
    const tone = dark ? 'bg-slate-900 text-slate-300' : 'border-r border-slate-200 bg-white text-slate-600';
    const activeClass = dark ? 'bg-indigo-500/20 text-white' : 'bg-indigo-50 text-indigo-700';
    const hoverClass = dark ? 'hover:bg-white/10' : 'hover:bg-slate-100';

    const sidebar = (
        <div className={`flex h-full flex-col ${tone}`}>
            <Link href="/" className={`flex items-center gap-2 px-6 py-5 font-serif text-xl font-bold ${dark ? 'text-white' : 'text-slate-900'}`}>
                <BookOpen className="h-6 w-6 text-indigo-500" />
                BlogVerse Pro
            </Link>
            <p className="px-6 pb-2 text-xs font-semibold opacity-70">{roleLabel}</p>
            <nav className="flex-1 space-y-1 px-3" onClick={() => setOpen(false)}>
                {nav.map(({ href, label, icon: Icon }) => (
                    <Link key={href} href={href} className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium ${isActive(href) ? activeClass : hoverClass}`}>
                        <Icon className="h-4 w-4" />
                        {label}
                    </Link>
                ))}
            </nav>
            <div className={`border-t p-4 ${dark ? 'border-white/10' : 'border-slate-200'}`}>
                <p className={`truncate text-sm font-semibold ${dark ? 'text-white' : 'text-slate-900'}`}>{auth.user.name}</p>
                <p className="truncate text-xs opacity-70">@{auth.user.username}</p>
                <button type="button" onClick={() => router.post('/logout')} className={`mt-3 flex w-full cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium ${hoverClass}`}>
                    <LogOut className="h-4 w-4" />
                    Keluar
                </button>
            </div>
        </div>
    );

    return (
        <div className="min-h-screen bg-slate-50">
            <ProcessingPill />

            <aside className="fixed inset-y-0 left-0 hidden w-64 lg:block">{sidebar}</aside>

            <header className="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
                <Link href="/" className="flex items-center gap-2 font-serif text-lg font-bold text-slate-900">
                    <BookOpen className="h-5 w-5 text-indigo-600" />
                    BlogVerse Pro
                </Link>
                <button type="button" aria-label="Buka menu" onClick={() => setOpen(true)} className="cursor-pointer rounded-lg p-2 hover:bg-slate-100">
                    <Menu className="h-6 w-6" />
                </button>
            </header>

            {open && (
                <div className="fixed inset-0 z-40 lg:hidden">
                    <div className="absolute inset-0 bg-slate-900/50" onClick={() => setOpen(false)} />
                    <aside className="absolute inset-y-0 left-0 w-64">
                        <button type="button" aria-label="Tutup menu" onClick={() => setOpen(false)} className="absolute right-2 top-3 z-10 cursor-pointer rounded-lg p-1.5 text-slate-400 hover:bg-white/10">
                            <X className="h-5 w-5" />
                        </button>
                        {sidebar}
                    </aside>
                </div>
            )}

            <main className="lg:pl-64">
                <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6">
                    <FlashMessages />
                    {children}
                </div>
            </main>
        </div>
    );
}
