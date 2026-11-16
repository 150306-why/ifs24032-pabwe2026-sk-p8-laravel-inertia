import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Download, Search } from 'lucide-react';
import { useState } from 'react';
import AdminPostTable from '@/Components/AdminPostTable';
import { buttonClass } from '@/Components/Button';
import { inputClass } from '@/Components/Field';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/utils/format';

export default function UserPostList({ user, posts, filters }) {
    const [q, setQ] = useState(filters.q);
    const apply = (params) => router.get(`/admin/users/${user.id}/posts`, params, { preserveState: true, replace: true });

    return (
        <>
            <Head title={`Artikel ${user.name}`} />
            <Link href="/admin/users" className="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-400 hover:underline">
                <ArrowLeft className="h-4 w-4" /> Kembali ke pengguna
            </Link>

            <div className="mt-4 flex flex-wrap items-start justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5">
                <div>
                    <h1 className="font-serif text-2xl font-bold text-slate-900">{user.name}</h1>
                    <p className="text-sm text-slate-500">@{user.username} · {user.email}</p>
                    <p className="mt-1 text-sm text-slate-600">Peran: {user.role === 'admin' ? 'Admin' : 'Penulis'} · {user.posts_count} artikel · bergabung {formatDate(user.created_at)}</p>
                </div>
                <a href={`/admin/users/${user.id}/posts/export`} className={buttonClass('primary')}><Download className="h-4 w-4" /> Ekspor ke Excel</a>
            </div>

            <form onSubmit={(e) => { e.preventDefault(); apply({ q, status: filters.status }); }} className="mt-6 flex flex-wrap gap-2">
                <div className="relative min-w-60 flex-1">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Cari artikel pengguna ini..." aria-label="Cari artikel" className={`${inputClass} pl-9`} />
                </div>
                <select aria-label="Filter status" value={filters.status} onChange={(e) => apply({ q: filters.q, status: e.target.value })} className={`${inputClass} w-auto`}>
                    <option value="">Semua status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
                <button type="submit" className={buttonClass('secondary')}>Cari</button>
            </form>

            <div className="mt-4"><AdminPostTable posts={posts.data} showAuthor={false} /></div>
            <Pagination links={posts.links} />
        </>
    );
}

UserPostList.layout = (page) => <AdminLayout>{page}</AdminLayout>;
