import { Head, Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { buttonClass } from '@/Components/Button';
import { inputClass } from '@/Components/Field';
import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate } from '@/utils/format';

export default function UserList({ users, filters }) {
    const [q, setQ] = useState(filters.q);

    return (
        <>
            <Head title="Pengguna" />
            <h1 className="font-serif text-3xl font-bold text-slate-900">Pengguna</h1>
            <p className="mt-1 text-sm text-slate-600">Seluruh pengguna terdaftar ({users.total}).</p>

            <form onSubmit={(e) => { e.preventDefault(); router.get('/admin/users', { q }, { preserveState: true, replace: true }); }} className="mt-6 flex max-w-md gap-2">
                <div className="relative flex-1">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Cari nama, username, email..." aria-label="Cari pengguna" className={`${inputClass} pl-9`} />
                </div>
                <button type="submit" className={buttonClass('secondary')}>Cari</button>
            </form>

            <div className="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table className="w-full min-w-[640px] text-left text-sm">
                    <thead className="border-b border-slate-200 bg-slate-50 text-xs text-slate-500">
                        <tr>
                            <th className="px-5 py-3 font-semibold">Pengguna</th>
                            <th className="px-5 py-3 font-semibold">Email</th>
                            <th className="px-5 py-3 font-semibold">Peran</th>
                            <th className="px-5 py-3 font-semibold">Artikel</th>
                            <th className="px-5 py-3 font-semibold">Bergabung</th>
                            <th className="px-5 py-3" />
                        </tr>
                    </thead>
                    <tbody>
                        {users.data.map((user) => (
                            <tr key={user.id} className="border-b border-slate-100 last:border-b-0">
                                <td className="px-5 py-3"><p className="font-semibold text-slate-900">{user.name}</p><p className="text-xs text-slate-500">@{user.username}</p></td>
                                <td className="px-5 py-3 text-slate-600">{user.email}</td>
                                <td className="px-5 py-3"><span className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${user.role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'}`}>{user.role === 'admin' ? 'Admin' : 'Penulis'}</span></td>
                                <td className="px-5 py-3 text-slate-600">{user.posts_count}</td>
                                <td className="px-5 py-3 text-slate-600">{formatDate(user.created_at)}</td>
                                <td className="px-5 py-3 text-right"><Link href={`/admin/users/${user.id}/posts`} className="font-semibold text-indigo-700 hover:underline">Lihat artikel</Link></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {users.data.length === 0 && <p className="p-10 text-center text-sm text-slate-500">Tidak ada pengguna yang cocok.</p>}
            </div>
            <Pagination links={users.links} />
        </>
    );
}

UserList.layout = (page) => <AdminLayout>{page}</AdminLayout>;
