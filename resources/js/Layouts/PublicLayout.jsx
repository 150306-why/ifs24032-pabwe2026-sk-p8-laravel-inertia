import { Link, router, usePage } from '@inertiajs/react';
import { BookOpen, Menu, X } from 'lucide-react';
import { useState } from 'react';
import FlashMessages from '@/Components/FlashMessages';
import ProcessingPill from '@/Components/ProcessingPill';
import Button, { buttonClass } from '@/Components/Button';

const links = [
    { href: '/', label: 'Beranda' },
    { href: '/articles', label: 'Artikel' },
];

export default function PublicLayout({ children }) {
    const page = usePage();
    const { auth } = page.props;
    const url = page.url;
    const [open, setOpen] = useState(false);
    const isActive = (href) => (href === '/' ? url === '/' : url.startsWith(href));

    return (
        <div className="flex min-h-screen flex-col bg-white">
            <ProcessingPill />

            {/* Sticky Top Navbar (tanpa sidebar) */}
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                    <Link href="/" className="flex items-center gap-2 font-serif text-xl font-bold text-slate-900">
                        <BookOpen className="h-6 w-6 text-indigo-600" />
                        BlogVerse Pro
                    </Link>

                    <nav className="hidden items-center gap-6 md:flex">
                        {links.map((link) => (
                            <Link key={link.href} href={link.href} className={`text-sm font-medium ${isActive(link.href) ? 'text-indigo-700' : 'text-slate-600 hover:text-slate-900'}`}>
                                {link.label}
                            </Link>
                        ))}
                    </nav>

                    <div className="hidden items-center gap-2 md:flex">
                        {auth.user ? (
                            <>
                                <Link href={auth.user.home} className={buttonClass('secondary')}>Dashboard</Link>
                                <Button variant="ghost" onClick={() => router.post('/logout')}>Keluar</Button>
                            </>
                        ) : (
                            <>
                                <Link href="/login" id="nav-login" className={buttonClass('ghost')}>Login</Link>
                                <Link href="/register" id="nav-register" className={buttonClass('primary')}>Register</Link>
                            </>
                        )}
                    </div>

                    <button type="button" aria-label="Buka menu" onClick={() => setOpen((value) => !value)} className="cursor-pointer rounded-lg p-2 text-slate-700 hover:bg-slate-100 md:hidden">
                        {open ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
                    </button>
                </div>

                {open && (
                    <div className="space-y-1 border-t border-slate-200 bg-white px-4 py-3 md:hidden" onClick={() => setOpen(false)}>
                        {links.map((link) => (
                            <Link key={link.href} href={link.href} className="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{link.label}</Link>
                        ))}
                        {auth.user ? (
                            <>
                                <Link href={auth.user.home} className="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Dashboard</Link>
                                <button type="button" onClick={() => router.post('/logout')} className="block w-full cursor-pointer rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">Keluar</button>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Login</Link>
                                <Link href="/register" className="block rounded-lg px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">Register</Link>
                            </>
                        )}
                    </div>
                )}
            </header>

            <main className="flex-1">
                <div className="mx-auto max-w-6xl px-4 pt-6 sm:px-6">
                    <FlashMessages />
                </div>
                {children}
            </main>

            {/* Footer editorial */}
            <footer className="mt-16 border-t border-slate-200 bg-slate-50">
                <div className="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3">
                    <div>
                        <p className="font-serif text-xl font-bold text-slate-900">BlogVerse Pro</p>
                        <p className="mt-2 max-w-xs text-sm leading-relaxed text-slate-600">
                            Ruang menulis dan membaca untuk gagasan yang layak dibagikan.
                        </p>
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-slate-900">Jelajahi</p>
                        <ul className="mt-3 space-y-2 text-sm text-slate-600">
                            <li><Link href="/" className="hover:text-indigo-700">Beranda</Link></li>
                            <li><Link href="/articles" className="hover:text-indigo-700">Semua artikel</Link></li>
                        </ul>
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-slate-900">Mulai menulis</p>
                        <ul className="mt-3 space-y-2 text-sm text-slate-600">
                            <li><Link href="/login" className="hover:text-indigo-700">Login</Link></li>
                            <li><Link href="/register" className="hover:text-indigo-700">Buat akun penulis</Link></li>
                        </ul>
                    </div>
                </div>
                <div className="border-t border-slate-200 py-4 text-center text-xs text-slate-500">
                    © {new Date().getFullYear()} BlogVerse Pro. Seluruh hak cipta dilindungi.
                </div>
            </footer>
        </div>
    );
}
