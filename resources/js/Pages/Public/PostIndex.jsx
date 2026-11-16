import { Head, Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import Pagination from '@/Components/Pagination';
import PostCard from '@/Components/PostCard';
import PublicLayout from '@/Layouts/PublicLayout';

export default function PostIndex({ posts, filters, publicCategories }) {
    const [q, setQ] = useState(filters.q);

    const apply = (params) => router.get('/articles', params, { preserveState: true, replace: true });

    const submit = (event) => {
        event.preventDefault();
        apply({ q, category: filters.category });
    };

    return (
        <>
            <Head title="Artikel" />
            <section className="mx-auto max-w-6xl px-4 pt-10 sm:px-6">
                <h1 className="font-serif text-4xl font-bold text-slate-900">Artikel</h1>
                <p className="mt-2 text-slate-600">Temukan bacaan berdasarkan judul, isi, atau kategori.</p>

                <form onSubmit={submit} className="mt-6 flex max-w-xl gap-2">
                    <div className="relative flex-1">
                        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Cari artikel..." aria-label="Cari artikel" className="block w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    </div>
                    <button type="submit" className="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Cari</button>
                </form>

                <div className="mt-5 flex flex-wrap gap-2">
                    <button type="button" onClick={() => apply({ q: filters.q })} className={`cursor-pointer rounded-full border px-4 py-1.5 text-sm font-medium ${filters.category === '' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 text-slate-700 hover:border-indigo-400'}`}>Semua</button>
                    {publicCategories.map((category) => (
                        <button key={category} type="button" onClick={() => apply({ q: filters.q, category })} className={`cursor-pointer rounded-full border px-4 py-1.5 text-sm font-medium ${filters.category === category ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 text-slate-700 hover:border-indigo-400'}`}>
                            {category}
                        </button>
                    ))}
                </div>

                {posts.data.length === 0 ? (
                    <div className="mt-10 rounded-xl border border-dashed border-slate-300 p-12 text-center text-slate-500">
                        Tidak ada artikel yang cocok. <Link href="/articles" className="font-semibold text-indigo-700 hover:underline">Reset pencarian</Link>
                    </div>
                ) : (
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {posts.data.map((post) => <PostCard key={post.id} post={post} />)}
                    </div>
                )}
                <Pagination links={posts.links} />
            </section>
        </>
    );
}

PostIndex.layout = (page) => <PublicLayout>{page}</PublicLayout>;
