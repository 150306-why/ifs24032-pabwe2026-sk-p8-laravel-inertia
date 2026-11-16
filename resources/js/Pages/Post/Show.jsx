import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ImageOff, Pencil, Send, Trash2, Undo2, Upload } from 'lucide-react';
import { useState } from 'react';
import Button from '@/Components/Button';
import ConfirmDialog from '@/Components/ConfirmDialog';
import MarkdownViewer from '@/Components/MarkdownViewer';
import PostFormModal from '@/Components/PostFormModal';
import StatusBadge from '@/Components/StatusBadge';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/utils/format';

export default function Show({ post }) {
    const [editing, setEditing] = useState(false);
    const [confirming, setConfirming] = useState(false);
    const [busy, setBusy] = useState(false);

    const uploadCover = (event) => {
        const cover = event.target.files[0];
        if (cover) router.post(`/dashboard/posts/${post.id}/cover`, { cover }, { forceFormData: true, preserveScroll: true });
        event.target.value = '';
    };

    return (
        <>
            <Head title={post.title} />

            <Link href="/dashboard/posts" className="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-700 hover:underline">
                <ArrowLeft className="h-4 w-4" /> Kembali ke daftar
            </Link>

            <div className="mt-4 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div className="flex items-center gap-3"><StatusBadge published={post.is_published} /><span className="text-sm text-slate-500">{post.category}</span></div>
                    <h1 className="mt-2 font-serif text-3xl font-bold text-slate-900">{post.title}</h1>
                    <p className="mt-1 text-sm text-slate-500">Dibuat {formatDate(post.created_at)} · Publikasi {formatDate(post.published_at)}</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Button variant="secondary" onClick={() => router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true })}>
                        {post.is_published ? <><Undo2 className="h-4 w-4" /> Jadikan Draft</> : <><Send className="h-4 w-4" /> Publikasikan</>}
                    </Button>
                    <Button variant="secondary" onClick={() => setEditing(true)}><Pencil className="h-4 w-4" /> Edit</Button>
                    <Button variant="danger" onClick={() => setConfirming(true)}><Trash2 className="h-4 w-4" /> Hapus</Button>
                </div>
            </div>

            <div className="mt-6 rounded-xl border border-slate-200 bg-white p-6">
                {post.cover_url ? <img src={post.cover_url} alt={post.title} className="mb-6 max-h-96 w-full rounded-lg object-cover" /> : <p className="mb-6 rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Artikel ini belum memiliki cover.</p>}

                <div className="mb-6 flex flex-wrap gap-2">
                    <label className="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <Upload className="h-4 w-4" /> {post.cover_url ? 'Ganti cover' : 'Unggah cover'}
                        <input type="file" accept="image/jpeg,image/png,image/webp" className="hidden" onChange={uploadCover} />
                    </label>
                    {post.cover_url && (
                        <Button variant="secondary" onClick={() => router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true })}><ImageOff className="h-4 w-4" /> Hapus cover</Button>
                    )}
                </div>

                <MarkdownViewer content={post.content} />
            </div>

            {editing && <PostFormModal post={post} onClose={() => setEditing(false)} />}
            {confirming && (
                <ConfirmDialog
                    title="Hapus artikel?"
                    message={`Artikel “${post.title}” akan dihapus permanen.`}
                    loading={busy}
                    onCancel={() => setConfirming(false)}
                    onConfirm={() => router.delete(`/dashboard/posts/${post.id}`, { onStart: () => setBusy(true), onFinish: () => setBusy(false) })}
                />
            )}
        </>
    );
}

Show.layout = (page) => <AppLayout>{page}</AppLayout>;
