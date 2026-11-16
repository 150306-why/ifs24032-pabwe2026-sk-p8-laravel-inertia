import { useEffect, useRef } from 'react';
import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';

/** TOAST UI Viewer: merender Markdown (HTML berbahaya disanitasi oleh Viewer). */
export default function MarkdownViewer({ content }) {
    const container = useRef(null);

    useEffect(() => {
        const viewer = new Viewer({ el: container.current, initialValue: content || '', usageStatistics: false });
        return () => viewer.destroy();
    }, [content]);

    return <div ref={container} className="markdown-body" />;
}
