import { router, useForm, usePage } from '@inertiajs/react';
import { ImageOff } from 'lucide-react';
import Button from './Button';
import Field, { inputClass } from './Field';
import FullScreenModal from './FullScreenModal';
import MarkdownEditor from './MarkdownEditor';

/** Full Screen Modal untuk Tambah / Ubah artikel (TOAST UI Editor). */
export default function PostFormModal({ post = null, onClose }) {
    const { categories } = usePage().props;
    const editing = Boolean(post);

    const form = useForm({
        title: post?.title ?? '',
        category: post?.category ?? '',
        content: post?.content ?? '',
        is_published: post?.is_published ?? false,
        cover: null,
    });
    const { data, setData, processing, errors } = form;

    const submit = (event) => {
        event.preventDefault();

        form.transform((payload) => {
            const body = { ...payload, is_published: payload.is_published ? 1 : 0 };
            if (!body.cover) delete body.cover;
            if (editing) body._method = 'put'; // PHP tidak membaca multipart pada PUT
            return body;
        });

        form.post(editing ? `/dashboard/posts/${post.id}` : '/dashboard/posts', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    const removeCover = () => router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true, preserveState: true });

    return (
        <FullScreenModal
            title={editing ? 'Ubah Artikel' : 'Tambah Artikel'}
            onClose={onClose}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={processing}>Batal</Button>
                    <Button type="submit" form="post-form" loading={processing}>Simpan</Button>
                </>
            }
        >
            <form id="post-form" onSubmit={submit} className="space-y-6">
                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Field label="Judul" htmlFor="post-title" error={errors.title}>
                            <input id="post-title" type="text" className={inputClass} value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="Judul artikel" />
                        </Field>
                    </div>
                    <Field label="Status" htmlFor="post-status" error={errors.is_published}>
                        <select id="post-status" className={inputClass} value={data.is_published ? '1' : '0'} onChange={(e) => setData('is_published', e.target.value === '1')}>
                            <option value="0">Draft</option>
                            <option value="1">Published</option>
                        </select>
                    </Field>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Field label="Kategori" htmlFor="post-category" error={errors.category}>
                        <input id="post-category" type="text" list="post-categories" className={inputClass} value={data.category} onChange={(e) => setData('category', e.target.value)} placeholder="Pilih atau ketik kategori" />
                        <datalist id="post-categories">{categories.map((name) => <option key={name} value={name} />)}</datalist>
                    </Field>
                    <Field label="Cover artikel" htmlFor="post-cover" error={errors.cover} hint="JPG, PNG, atau WebP. Maksimal 2 MB.">
                        <input id="post-cover" type="file" accept="image/jpeg,image/png,image/webp" className={`${inputClass} file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1 file:text-indigo-700`} onChange={(e) => setData('cover', e.target.files[0] ?? null)} />
                    </Field>
                </div>

                {editing && post.cover_url && (
                    <div className="flex items-center gap-4 rounded-lg border border-slate-200 p-3">
                        <img src={post.cover_url} alt="Cover saat ini" className="h-16 w-28 rounded object-cover" />
                        <p className="flex-1 text-sm text-slate-600">Cover saat ini. Pilih file baru untuk menggantinya.</p>
                        <Button variant="secondary" onClick={removeCover}><ImageOff className="h-4 w-4" /> Hapus cover</Button>
                    </div>
                )}

                <Field label="Konten (Markdown)" error={errors.content}>
                    <MarkdownEditor value={data.content} onChange={(value) => setData('content', value)} />
                </Field>
            </form>
        </FullScreenModal>
    );
}
