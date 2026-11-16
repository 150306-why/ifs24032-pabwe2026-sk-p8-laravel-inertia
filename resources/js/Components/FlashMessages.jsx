import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { AlertCircle, CheckCircle2, X } from 'lucide-react';

export default function FlashMessages() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        setVisible(true);
    }, [flash]);

    if (!visible || (!flash.success && !flash.error)) return null;

    const isError = Boolean(flash.error);

    return (
        <div className={`mb-6 flex items-start gap-3 rounded-lg border px-4 py-3 text-sm ${isError ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'}`} role="alert">
            {isError ? <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" /> : <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" />}
            <div className="flex-1">
                <p>{flash.error ?? flash.success}</p>
                {flash.import_errors && (
                    <ul className="mt-2 list-disc space-y-0.5 pl-5">
                        {flash.import_errors.map((message, index) => (
                            <li key={index}>{message}</li>
                        ))}
                    </ul>
                )}
            </div>
            <button type="button" aria-label="Tutup pesan" onClick={() => setVisible(false)} className="cursor-pointer rounded p-0.5 hover:bg-black/5">
                <X className="h-4 w-4" />
            </button>
        </div>
    );
}
