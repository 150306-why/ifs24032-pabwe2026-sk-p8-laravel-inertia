import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import MarkdownViewer from '@/Components/MarkdownViewer';
import PostCard from '@/Components/PostCard';
import PublicLayout from '@/Layouts/PublicLayout';
import { formatDate } from '@/utils/format';

export default function PostShow({ post, related }) {
    return (
        <>
            <Head title={post.title} />
            <article className="mx-auto max-w-3xl px-4 pt-8 sm:px-6">
                <Link href="/articles" className="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-700 hover:underline">
                    <ArrowLeft className="h-4 w-4" /> Kembali ke daftar artikel
                </Link>

                <Link href={`/articles?category=${encodeURIComponent(post.category)}`} className="mt-6 block w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{post.category}</Link>
                <h1 className="mt-3 font-serif text-4xl font-bold leading-tight text-slate-900 sm:text-5xl">{post.title}</h1>
                <p className="mt-4 text-sm text-slate-500">Oleh <span className="font-medium text-slate-700">{post.user?.name}</span> — {formatDate(post.published_at)}</p>

                {post.cover_url && <img src={post.cover_url} alt={post.title} className="mt-8 w-full rounded-xl object-cover" />}

                <div className="mt-8"><MarkdownViewer content={post.content} /></div>
            </article>

            {related.length > 0 && (
                <section className="mx-auto mt-16 max-w-6xl px-4 sm:px-6">
                    <h2 className="font-serif text-2xl font-bold text-slate-900">Bacaan terkait</h2>
                    <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {related.map((item) => <PostCard key={item.id} post={item} />)}
                    </div>
                </section>
            )}
        </>
    );
}

PostShow.layout = (page) => <PublicLayout>{page}</PublicLayout>;
