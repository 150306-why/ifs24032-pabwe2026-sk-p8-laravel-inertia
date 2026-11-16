import { Head, Link, router, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import PostCard from '@/Components/PostCard';
import { buttonClass } from '@/Components/Button';
import PublicLayout from '@/Layouts/PublicLayout';
import { formatDate } from '@/utils/format';

export default function Home({ latest, featured, categories }) {
    const { auth } = usePage().props;
    const [q, setQ] = useState('');

    const search = (event) => {
        event.preventDefault();
        router.get('/articles', { q });
    };

    return (
        <>
            <Head title="Beranda" />

            {/* Hero banner */}
            <section className="border-b border-slate-200 bg-gradient-to-b from-indigo-50/70 to-white">
                <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
                    <h1 className="max-w-3xl font-serif text-4xl font-bold leading-tight text-slate-900 sm:text-6xl">
                        Tulisan yang layak dibaca, ruang untuk yang suka menulis.
                    </h1>
                    <p className="mt-5 max-w-xl text-lg leading-relaxed text-slate-600">
                        BlogVerse Pro mempertemukan penulis dan pembaca. Baca bebas tanpa akun, atau daftar untuk menerbitkan artikel Anda sendiri.
                    </p>

                    <div className="mt-8 flex flex-wrap gap-3">
                        {auth.user ? (
                            <Link href={auth.user.home} className={buttonClass('primary', 'px-6 py-3')}>Ke dashboard</Link>
                        ) : (
                            <>
                                <Link href="/login" className={buttonClass('primary', 'px-6 py-3')}>Login</Link>
                                <Link href="/register" className={buttonClass('secondary', 'px-6 py-3')}>Register</Link>
                            </>
                        )}
                    </div>

                    <form onSubmit={search} className="mt-10 flex max-w-xl items-center gap-2 rounded-xl border border-slate-300 bg-white p-1.5 shadow-sm">
                        <Search className="ml-3 h-5 w-5 text-slate-400" />
                        <input type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Cari judul atau isi artikel..." className="flex-1 bg-transparent px-2 py-2 text-sm focus:outline-none" aria-label="Cari artikel" />
                        <button type="submit" className="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Cari</button>
                    </form>
                </div>
            </section>

            {categories.length > 0 && (
                <section className="mx-auto max-w-6xl px-4 pt-12 sm:px-6">
                    <h2 className="font-serif text-2xl font-bold text-slate-900">Kategori</h2>
                    <div className="mt-4 flex flex-wrap gap-2">
                        {categories.map((category) => (
                            <Link key={category.category} href={`/articles?category=${encodeURIComponent(category.category)}`} className="rounded-full border border-slate-300 bg-white px-4 py-1.5 text-sm font-medium text-slate-700 hover:border-indigo-400 hover:text-indigo-700">
                                {category.category} <span className="text-slate-400">{category.total}</span>
                            </Link>
                        ))}
                    </div>
                </section>
            )}

            {featured.length > 0 && (
                <section className="mx-auto max-w-6xl px-4 pt-14 sm:px-6">
                    <h2 className="font-serif text-2xl font-bold text-slate-900">Artikel pilihan</h2>
                    <div className="mt-6 grid gap-6 md:grid-cols-3">
                        {featured.map((post) => (
                            <article key={post.id} className="flex flex-col rounded-xl border border-indigo-100 bg-indigo-50/40 p-6">
                                <span className="w-fit rounded-full bg-white px-2.5 py-0.5 text-xs font-semibold text-indigo-700">{post.category}</span>
                                <h3 className="mt-3 font-serif text-2xl font-bold leading-snug text-slate-900">
                                    <Link href={`/articles/${post.slug}`} className="hover:text-indigo-700">{post.title}</Link>
                                </h3>
                                <p className="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-slate-600">{post.excerpt}</p>
                                <p className="mt-4 text-xs text-slate-500">{post.user?.name} — {formatDate(post.published_at)}</p>
                            </article>
                        ))}
                    </div>
                </section>
            )}

            <section className="mx-auto max-w-6xl px-4 pt-14 sm:px-6">
                <div className="flex items-end justify-between">
                    <h2 className="font-serif text-2xl font-bold text-slate-900">Artikel terbaru</h2>
                    <Link href="/articles" className="text-sm font-semibold text-indigo-700 hover:underline">Lihat semua</Link>
                </div>
                {latest.length === 0 ? (
                    <p className="mt-6 rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-500">Belum ada artikel yang dipublikasikan.</p>
                ) : (
                    <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {latest.map((post) => <PostCard key={post.id} post={post} />)}
                    </div>
                )}
            </section>
        </>
    );
}

Home.layout = (page) => <PublicLayout>{page}</PublicLayout>;
