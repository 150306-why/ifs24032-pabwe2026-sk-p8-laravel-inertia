import Button from './Button';

export default function ConfirmDialog({ title, message, confirmLabel = 'Hapus', loading = false, onConfirm, onCancel }) {
    return (
        <div className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4" role="alertdialog" aria-modal="true" aria-label={title}>
            <div className="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 className="text-lg font-semibold text-slate-900">{title}</h3>
                <p className="mt-2 text-sm text-slate-600">{message}</p>
                <div className="mt-6 flex justify-end gap-3">
                    <Button variant="secondary" onClick={onCancel} disabled={loading}>Batal</Button>
                    <Button variant="danger" onClick={onConfirm} loading={loading}>{confirmLabel}</Button>
                </div>
            </div>
        </div>
    );
}
