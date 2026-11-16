import { Head, router } from '@inertiajs/react';
import { Download, Search } from 'lucide-react';
import { useState } from 'react';
import AdminPostTable from '@/Components/AdminPostTable';
import { buttonClass } from '@/Components/Button';
import { inputClass } from '@/Components/Field';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';

export default function PostList({ posts, filters, allCategories }) {
    const [q, setQ] = useState(filters.q);
    const apply = (params) => router.get('/admin/posts', params, { preserveState: true, replace: true });

    return (
        <>
            <Head title="Semua Artikel" />
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 className="font-serif text-3xl font-bold text-slate-900">Semua Artikel</h1>
                    <p className="mt-1 text-sm text-slate-600">Pemantauan seluruh artikel ({posts.total}). Mode baca saja.</p>
                </div>
                <a href="/admin/posts/export" className={buttonClass('primary')}><Download className="h-4 w-4" /> Ekspor semua ke Excel</a>
            </div>

            <form onSubmit={(e) => { e.preventDefault(); apply({ q, status: filters.status, category: filters.category }); }} className="mt-6 flex flex-wrap gap-2">
                <div className="relative min-w-60 flex-1">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Cari judul atau isi..." aria-label="Cari artikel" className={`${inputClass} pl-9`} />
                </div>
                <select aria-label="Filter kategori" value={filters.category} onChange={(e) => apply({ q: filters.q, status: filters.status, category: e.target.value })} className={`${inputClass} w-auto`}>
                    <option value="">Semua kategori</option>
                    {allCategories.map((name) => <option key={name} value={name}>{name}</option>)}
                </select>
                <select aria-label="Filter status" value={filters.status} onChange={(e) => apply({ q: filters.q, status: e.target.value, category: filters.category })} className={`${inputClass} w-auto`}>
                    <option value="">Semua status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
                <button type="submit" className={buttonClass('secondary')}>Cari</button>
            </form>

            <div className="mt-4"><AdminPostTable posts={posts.data} /></div>
            <Pagination links={posts.links} />
        </>
    );
}

PostList.layout = (page) => <AdminLayout>{page}</AdminLayout>;
