import StatusBadge from './StatusBadge';
import { formatDate } from '@/utils/format';

/** Tabel artikel READ-ONLY untuk panel admin (tanpa aksi ubah/hapus). */
export default function AdminPostTable({ posts, showAuthor = true }) {
    return (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table className="w-full min-w-[720px] text-left text-sm">
                <thead className="border-b border-slate-200 bg-slate-50 text-xs text-slate-500">
                    <tr>
                        <th className="px-5 py-3 font-semibold">Judul</th>
                        {showAuthor && <th className="px-5 py-3 font-semibold">Penulis</th>}
                        <th className="px-5 py-3 font-semibold">Kategori</th>
                        <th className="px-5 py-3 font-semibold">Status</th>
                        <th className="px-5 py-3 font-semibold">Dibuat</th>
                        <th className="px-5 py-3 font-semibold">Publikasi</th>
                    </tr>
                </thead>
                <tbody>
                    {posts.map((post) => (
                        <tr key={post.id} className="border-b border-slate-100 last:border-b-0">
                            <td className="px-5 py-3">
                                {post.is_published ? (
                                    <a href={`/articles/${post.slug}`} target="_blank" rel="noreferrer" className="font-semibold text-slate-900 hover:text-indigo-700">{post.title}</a>
                                ) : (
                                    <span className="font-semibold text-slate-900">{post.title}</span>
                                )}
                            </td>
                            {showAuthor && <td className="px-5 py-3 text-slate-600">{post.user?.name}</td>}
                            <td className="px-5 py-3 text-slate-600">{post.category}</td>
                            <td className="px-5 py-3"><StatusBadge published={post.is_published} /></td>
                            <td className="px-5 py-3 text-slate-600">{formatDate(post.created_at)}</td>
                            <td className="px-5 py-3 text-slate-600">{formatDate(post.published_at)}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
            {posts.length === 0 && <p className="p-10 text-center text-sm text-slate-500">Tidak ada artikel yang cocok.</p>}
        </div>
    );
}
