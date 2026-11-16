import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Spinner from './Spinner';

/** Floating Status Pill: tampil selama tindakan (non-GET) diproses. */
export default function ProcessingPill() {
    const [active, setActive] = useState(false);

    useEffect(() => {
        const offStart = router.on('start', (event) => {
            if (event.detail.visit.method !== 'get') setActive(true);
        });
        const offFinish = router.on('finish', () => setActive(false));

        return () => {
            offStart();
            offFinish();
        };
    }, []);

    if (!active) return null;

    return (
        <div role="status" className="fixed bottom-6 left-1/2 z-[100] flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-lg">
            <Spinner />
            Memproses tindakan...
        </div>
    );
}
