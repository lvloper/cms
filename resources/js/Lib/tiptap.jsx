import { generateHTML } from '@tiptap/html'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'

const extensions = [
    StarterKit.configure({
        heading: { levels: [1, 2, 3, 4, 5, 6] },
    }),
    Link.configure({
        openOnClick: false,
        HTMLAttributes: { class: 'text-primary underline' },
    }),
]

export function renderTiptap(content) {
    if (!content) return ''
    if (typeof content === 'string') return content
    if (!Array.isArray(content)) return ''

    try {
        return generateHTML(
            { type: 'doc', content },
            extensions,
        )
    } catch (e) {
        console.error('Tiptap render error:', e)
        return ''
    }
}

export function TiptapContent({ content, className }) {
    const html = renderTiptap(content)

    if (!html) return null

    return (
        <div
            className={className ?? 'prose prose-lg max-w-none text-gray-700 [&_p]:mb-4 [&_p:last-child]:mb-0 [&_a]:text-primary [&_a]:underline [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-1'}
            dangerouslySetInnerHTML={{ __html: html }}
        />
    )
}
