import { Link } from '@inertiajs/react';
import { formatDate } from '@/utils/format';

/** `headingLevel` mengikuti hierarki heading halaman (h1 → h2 → h3) agar lolos audit aksesibilitas. */
export default function PostCard({ post, headingLevel = 3 }) {
    const Heading = `h${headingLevel}`;

    return (
        <article className="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white">
            <Link href={`/articles/${post.slug}`} className="block aspect-[16/9] overflow-hidden bg-slate-100">
                {post.cover_url ? (
                    <img src={post.cover_url} alt={post.title} className="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" />
                ) : (
                    <div className="flex h-full items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 font-serif text-3xl text-indigo-300">BV</div>
                )}
            </Link>
            <div className="flex flex-1 flex-col p-5">
                <Link href={`/articles?category=${encodeURIComponent(post.category)}`} className="w-fit rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                    {post.category}
                </Link>
                <Heading className="mt-3 font-serif text-xl font-bold leading-snug text-slate-900">
                    <Link href={`/articles/${post.slug}`} className="hover:text-indigo-700">{post.title}</Link>
                </Heading>
                <p className="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-slate-600">{post.excerpt}</p>
                <p className="mt-4 text-xs text-slate-500">
                    {post.user?.name} — {formatDate(post.published_at)}
                </p>
            </div>
        </article>
    );
}
