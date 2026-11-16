import { useForm, usePage } from '@inertiajs/react';
import Button from './Button';
import Field, { inputClass } from './Field';
import FullScreenModal from './FullScreenModal';

export default function ImportModal({ onClose }) {
    const { flash } = usePage().props;
    const form = useForm({ file: null });

    const submit = (event) => {
        event.preventDefault();
        form.post('/dashboard/posts/import', {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                if (!page.props.flash.import_errors) onClose();
            },
        });
    };

    return (
        <FullScreenModal
            title="Impor Artikel dari Excel"
            onClose={onClose}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" form="import-form" loading={form.processing} disabled={!form.data.file}>Impor</Button>
                </>
            }
        >
            <form id="import-form" onSubmit={submit} className="max-w-2xl space-y-6">
                <div className="rounded-lg border border-indigo-100 bg-indigo-50/60 p-4 text-sm text-slate-700">
                    <p className="font-semibold text-slate-900">Format file (.xlsx / .xls, maksimal 2 MB)</p>
                    <p className="mt-1">Baris pertama adalah header. Kolom wajib: <b>Judul</b>, <b>Kategori</b>, <b>Konten</b> (Markdown). Kolom opsional: <b>Status</b> (Draft atau Published, default Draft).</p>
                    <p className="mt-1">Jika ada satu baris tidak valid, seluruh impor dibatalkan dan tidak ada data yang disimpan. Tip: ekspor artikel Anda untuk melihat contoh format.</p>
                </div>

                <Field label="File Excel" htmlFor="import-file" error={form.errors.file}>
                    <input id="import-file" type="file" accept=".xlsx,.xls" className={inputClass} onChange={(e) => form.setData('file', e.target.files[0] ?? null)} />
                </Field>

                {flash.import_errors && (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                        <p className="font-semibold">{flash.error}</p>
                        <ul className="mt-2 list-disc space-y-0.5 pl-5">
                            {flash.import_errors.map((message, index) => <li key={index}>{message}</li>)}
                        </ul>
                    </div>
                )}
            </form>
        </FullScreenModal>
    );
}
