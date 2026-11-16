import { Head, router } from '@inertiajs/react';
import { Download, Eye, FileCheck2, FilePen, FileText, Pencil, Plus, Search, Send, Sparkles, Trash2, Undo2, Upload } from 'lucide-react';
import { useState } from 'react';
import ActionMenu, { MenuItem } from '@/Components/ActionMenu';
import AiGeneratorModal from '@/Components/AiGeneratorModal';
import Button, { buttonClass } from '@/Components/Button';
import ConfirmDialog from '@/Components/ConfirmDialog';
import ImportModal from '@/Components/ImportModal';
import Pagination from '@/Components/Pagination';
import PostFormModal from '@/Components/PostFormModal';
import StatusBadge from '@/Components/StatusBadge';
import AppLayout from '@/Layouts/AppLayout';
import { inputClass } from '@/Components/Field';
import { formatDate } from '@/utils/format';

function StatCard({ icon: Icon, label, value, tone }) {
    return (
        <div className="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div className={`rounded-lg p-3 ${tone}`}><Icon className="h-6 w-6" /></div>
            <div>
                <p className="text-sm text-slate-500">{label}</p>
                <p className="text-3xl font-bold text-slate-900">{value}</p>
            </div>
        </div>
    );
}

export default function Index({ posts, stats, filters }) {
    const [modal, setModal] = useState(null); // 'create' | 'ai' | 'import' | null
    const [editingId, setEditingId] = useState(null);
    const [deleteTarget, setDeleteTarget] = useState(null);
    const [deleting, setDeleting] = useState(false);
    const [busyId, setBusyId] = useState(null);
    const [q, setQ] = useState(filters.q);

    const editing = posts.data.find((post) => post.id === editingId);

    const apply = (params) => router.get('/dashboard/posts', params, { preserveState: true, replace: true });

    const togglePublish = (post) =>
        router.patch(`/dashboard/posts/${post.id}/publish`, {}, {
            preserveScroll: true,
            onStart: () => setBusyId(post.id),
            onFinish: () => setBusyId(null),
        });

    const confirmDelete = () =>
        router.delete(`/dashboard/posts/${deleteTarget.id}`, {
            preserveScroll: true,
            onStart: () => setDeleting(true),
            onFinish: () => setDeleting(false),
            onSuccess: () => setDeleteTarget(null),
        });

    return (
        <>
            <Head title="Artikel Saya" />

            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 className="font-serif text-3xl font-bold text-slate-900">Dashboard Penulis</h1>
                    <p className="mt-1 text-sm text-slate-600">Kelola artikel Anda: tulis, terbitkan, impor, dan ekspor.</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <a href="/dashboard/posts/export" className={buttonClass('secondary')}><Download className="h-4 w-4" /> Ekspor Excel</a>
                    <Button variant="secondary" onClick={() => setModal('import')}><Upload className="h-4 w-4" /> Impor Excel</Button>
                    <Button variant="secondary" onClick={() => setModal('ai')}><Sparkles className="h-4 w-4" /> Generate AI</Button>
                    <Button onClick={() => setModal('create')}><Plus className="h-4 w-4" /> Tambah Artikel</Button>
                </div>
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-3">
                <StatCard icon={FileText} label="Total Artikel" value={stats.total} tone="bg-indigo-50 text-indigo-600" />
                <StatCard icon={FileCheck2} label="Total Selesai" value={stats.published} tone="bg-emerald-50 text-emerald-600" />
                <StatCard icon={FilePen} label="Belum Selesai" value={stats.draft} tone="bg-amber-50 text-amber-600" />
            </div>

            <form onSubmit={(e) => { e.preventDefault(); apply({ q, status: filters.status }); }} className="mt-8 flex flex-wrap gap-2">
                <div className="relative min-w-60 flex-1">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Cari artikel saya..." aria-label="Cari artikel saya" className={`${inputClass} pl-9`} />
                </div>
                <select aria-label="Filter status" value={filters.status} onChange={(e) => apply({ q: filters.q, status: e.target.value })} className={`${inputClass} w-auto`}>
                    <option value="">Semua status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
                <Button type="submit" variant="secondary">Cari</Button>
            </form>

            {/* List View */}
            <div className="mt-4 rounded-xl border border-slate-200 bg-white">
                <div className="hidden grid-cols-12 gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold text-slate-500 lg:grid">
                    <span className="col-span-4">Judul</span>
                    <span className="col-span-2">Kategori</span>
                    <span className="col-span-1">Status</span>
                    <span className="col-span-1">Dibuat</span>
                    <span className="col-span-1">Publikasi</span>
                    <span className="col-span-2 text-right">Status cepat</span>
                    <span className="col-span-1 text-right">Aksi</span>
                </div>

                {posts.data.length === 0 && (
                    <p className="p-12 text-center text-sm text-slate-500">Belum ada artikel. Klik “Tambah Artikel” untuk mulai menulis.</p>
                )}

                {posts.data.map((post) => (
                    <div key={post.id} className="grid grid-cols-1 items-center gap-2 border-b border-slate-100 px-5 py-4 last:border-b-0 lg:grid-cols-12 lg:gap-4">
                        <div className="lg:col-span-4">
                            <p className="font-semibold text-slate-900">{post.title}</p>
                            <p className="line-clamp-1 text-xs text-slate-500">{post.excerpt}</p>
                        </div>
                        <p className="text-sm text-slate-600 lg:col-span-2">{post.category}</p>
                        <div className="lg:col-span-1"><StatusBadge published={post.is_published} /></div>
                        <p className="text-sm text-slate-600 lg:col-span-1">{formatDate(post.created_at)}</p>
                        <p className="text-sm text-slate-600 lg:col-span-1">{formatDate(post.published_at)}</p>
                        <div className="lg:col-span-2 lg:text-right">
                            <Button variant="secondary" className="px-3 py-1.5" loading={busyId === post.id} onClick={() => togglePublish(post)}>
                                {post.is_published ? <><Undo2 className="h-4 w-4" /> Jadikan Draft</> : <><Send className="h-4 w-4" /> Publikasikan</>}
                            </Button>
                        </div>
                        <div className="flex justify-end lg:col-span-1">
                            <ActionMenu>
                                <MenuItem icon={Eye} href={`/dashboard/posts/${post.id}`}>Detail</MenuItem>
                                <MenuItem icon={Pencil} onClick={() => { setEditingId(post.id); setModal('edit'); }}>Edit</MenuItem>
                                <MenuItem icon={Trash2} danger onClick={() => setDeleteTarget(post)}>Hapus</MenuItem>
                            </ActionMenu>
                        </div>
                    </div>
                ))}
            </div>
            <Pagination links={posts.links} />

            {modal === 'create' && <PostFormModal onClose={() => setModal(null)} />}
            {modal === 'edit' && editing && <PostFormModal post={editing} onClose={() => setModal(null)} />}
            {modal === 'ai' && <AiGeneratorModal onClose={() => setModal(null)} />}
            {modal === 'import' && <ImportModal onClose={() => setModal(null)} />}

            {deleteTarget && (
                <ConfirmDialog
                    title="Hapus artikel?"
                    message={`Artikel “${deleteTarget.title}” akan dihapus permanen beserta cover-nya.`}
                    loading={deleting}
                    onConfirm={confirmDelete}
                    onCancel={() => setDeleteTarget(null)}
                />
            )}
        </>
    );
}

Index.layout = (page) => <AppLayout>{page}</AppLayout>;
