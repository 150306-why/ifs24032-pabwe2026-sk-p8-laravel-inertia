import { router, useForm, usePage } from '@inertiajs/react';
import { Sparkles, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import Button from './Button';
import Field, { inputClass } from './Field';
import FullScreenModal from './FullScreenModal';
import MarkdownEditor from './MarkdownEditor';

/** Generator artikel OpenAI: hasil dapat diedit lalu disimpan sebagai Draft. */
export default function AiGeneratorModal({ onClose }) {
    const { flash, categories, ai, errors } = usePage().props;
    const [results, setResults] = useState([]);
    const [saving, setSaving] = useState(false);
    const form = useForm({ topic: '', category: categories[0] ?? '', count: 1 });

    useEffect(() => {
        if (flash.ai_results) setResults(flash.ai_results);
    }, [flash.ai_results]);

    const generate = (event) => {
        event.preventDefault();
        form.post('/dashboard/posts/ai-generate', { preserveScroll: true, preserveState: true });
    };

    const update = (index, key, value) => setResults((items) => items.map((item, i) => (i === index ? { ...item, [key]: value } : item)));

    const save = () => {
        router.post('/dashboard/posts/ai-save', { articles: results }, {
            preserveScroll: true,
            preserveState: true,
            onStart: () => setSaving(true),
            onFinish: () => setSaving(false),
            onSuccess: onClose,
        });
    };

    const saveErrors = Object.entries(errors).filter(([key]) => key.startsWith('articles'));

    return (
        <FullScreenModal
            title="Generate Artikel dengan AI"
            onClose={onClose}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={saving}>Batal</Button>
                    <Button onClick={save} loading={saving} disabled={results.length === 0}>Simpan sebagai draft</Button>
                </>
            }
        >
            <form onSubmit={generate} className="grid gap-4 md:grid-cols-12">
                <div className="md:col-span-5">
                    <Field label="Topik artikel" htmlFor="ai-topic" error={form.errors.topic}>
                        <input id="ai-topic" type="text" className={inputClass} value={form.data.topic} onChange={(e) => form.setData('topic', e.target.value)} placeholder="Contoh: manfaat tidur cukup bagi mahasiswa" />
                    </Field>
                </div>
                <div className="md:col-span-3">
                    <Field label="Kategori" htmlFor="ai-category" error={form.errors.category}>
                        <input id="ai-category" type="text" list="ai-categories" className={inputClass} value={form.data.category} onChange={(e) => form.setData('category', e.target.value)} />
                        <datalist id="ai-categories">{categories.map((name) => <option key={name} value={name} />)}</datalist>
                    </Field>
                </div>
                <div className="md:col-span-2">
                    <Field label="Jumlah" htmlFor="ai-count" error={form.errors.count} hint={`Maks. ${ai.max}`}>
                        <input id="ai-count" type="number" min="1" max={ai.max} className={inputClass} value={form.data.count} onChange={(e) => form.setData('count', e.target.value)} />
                    </Field>
                </div>
                <div className="flex items-end md:col-span-2">
                    <Button type="submit" loading={form.processing} className="w-full"><Sparkles className="h-4 w-4" /> Generate</Button>
                </div>
            </form>

            {form.processing && <p className="mt-6 text-sm text-slate-500">AI sedang menulis artikel. Proses ini bisa memakan waktu hingga satu menit...</p>}

            {saveErrors.length > 0 && (
                <ul className="mt-6 list-disc rounded-lg border border-red-200 bg-red-50 py-3 pl-8 pr-4 text-sm text-red-800" role="alert">
                    {saveErrors.map(([key, message]) => <li key={key}>{message}</li>)}
                </ul>
            )}

            <div className="mt-8 space-y-8">
                {results.map((article, index) => (
                    <section key={index} className="rounded-xl border border-slate-200 p-5">
                        <div className="flex items-start gap-3">
                            <div className="grid flex-1 gap-4 md:grid-cols-3">
                                <div className="md:col-span-2">
                                    <Field label={`Judul artikel ${index + 1}`} htmlFor={`ai-title-${index}`}>
                                        <input id={`ai-title-${index}`} type="text" className={inputClass} value={article.title} onChange={(e) => update(index, 'title', e.target.value)} />
                                    </Field>
                                </div>
                                <Field label="Kategori" htmlFor={`ai-cat-${index}`}>
                                    <input id={`ai-cat-${index}`} type="text" className={inputClass} value={article.category} onChange={(e) => update(index, 'category', e.target.value)} />
                                </Field>
                            </div>
                            <Button variant="ghost" aria-label="Buang artikel ini" className="mt-6 text-red-600" onClick={() => setResults((items) => items.filter((_, i) => i !== index))}>
                                <Trash2 className="h-4 w-4" />
                            </Button>
                        </div>
                        <div className="mt-4">
                            <MarkdownEditor value={article.content} onChange={(value) => update(index, 'content', value)} height="360px" />
                        </div>
                    </section>
                ))}
            </div>
        </FullScreenModal>
    );
}
