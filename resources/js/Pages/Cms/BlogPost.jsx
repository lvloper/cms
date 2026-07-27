import { Head } from '@inertiajs/react'
import DefaultLayout from '@/Layouts/Default'

export default function BlogPost({ post, route }) {
    return (
        <>
            <Head title={post?.title} />
            <DefaultLayout route={route}>
                <article className="py-12 md:py-16">
                    <div className="container mx-auto px-4">
                        <div className="mx-auto max-w-3xl">
                            {post?.image && (
                                <img
                                    src={post.image}
                                    alt={post.title}
                                    className="w-full h-64 md:h-96 object-cover rounded-lg mb-8"
                                />
                            )}
                            <h1 className="text-3xl md:text-5xl font-bold text-gray-900 mb-4">{post?.title}</h1>
                            {post?.created_at && (
                                <time className="text-sm text-gray-500">
                                    {new Date(post.created_at).toLocaleDateString('es-AR', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })}
                                </time>
                            )}
                            {post?.content && (
                                <div
                                    className="prose prose-lg max-w-none mt-8 text-gray-700"
                                    dangerouslySetInnerHTML={{ __html: post.content }}
                                />
                            )}
                        </div>
                    </div>
                </article>
            </DefaultLayout>
        </>
    )
}
