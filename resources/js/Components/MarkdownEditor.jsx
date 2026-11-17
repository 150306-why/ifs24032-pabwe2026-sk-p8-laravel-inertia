import { useEffect, useRef } from 'react';
import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';

/** TOAST UI Editor (Markdown). Nilai awal dibaca sekali; perubahan dikirim lewat onChange. */
export default function MarkdownEditor({ value, onChange, height = '420px', placeholder = 'Tulis artikel dalam Markdown...' }) {
    const container = useRef(null);
    const onChangeRef = useRef(onChange);
    onChangeRef.current = onChange;

    useEffect(() => {
        const editor = new Editor({
            el: container.current,
            height,
            initialEditType: 'markdown',
            previewStyle: 'vertical',
            initialValue: value || '',
            placeholder,
            usageStatistics: false,
            events: { change: () => onChangeRef.current(editor.getMarkdown()) },
        });

        // Toolbar TOAST UI menjadwalkan re-render saat resize (throttle 200 ms). Bila destroy() dipanggil
        // sebelum timer itu jalan, muncul error "removeChild". Tunda destroy sampai timer selesai.
        return () => setTimeout(() => editor.destroy(), 300);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return <div ref={container} />;
}
