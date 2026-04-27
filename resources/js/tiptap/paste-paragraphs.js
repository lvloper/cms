import { Extension } from '@tiptap/core'
import { Fragment, Slice, DOMParser } from 'prosemirror-model'
import { Plugin, PluginKey } from 'prosemirror-state'

console.log('Tiptap PasteAsParagraphs extension loaded')

const PasteAsParagraphs = Extension.create({
    name: 'pasteAsParagraphs',
    priority: 1000,
    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('paste-as-paragraphs'),
                props: {
                    handlePaste: (view, event, slice) => {
                        const html = event.clipboardData?.getData('text/html') || ''
                        const text = event.clipboardData?.getData('text/plain') || ''

                        if (!html && !text) return false
                        if (!html && !text.includes('\n')) return false

                        event.preventDefault()
                        const schema = view.state.schema

                        let cleanedHTML = html
                        if (html) cleanedHTML = cleanHTMLBeforeParse(html)

                        let parsedSlice = slice
                        if (cleanedHTML && html) {
                            try {
                                const tempDiv = document.createElement('div')
                                tempDiv.innerHTML = cleanedHTML
                                const parser = DOMParser.fromSchema(schema)
                                const fragment = parser.parse(tempDiv)
                                parsedSlice = new Slice(fragment.content, 0, 0)
                            } catch (e) {
                                console.warn('Parse failed, using original slice', e)
                            }
                        }

                        const paragraphType = schema.nodes.paragraph
                        const processedBlocks = []

                        parsedSlice.content.forEach((node) => {
                            const cleanedNode = stripUnwantedMarks(node, schema)

                            let block = cleanedNode.isBlock
                                ? cleanedNode
                                : paragraphType.create(null, Fragment.fromArray([cleanedNode]))

                            // filter children: drop hardBreak and whitespace-only text
                            const kept = []
                            block.content.forEach((child) => {
                                if (child.type.name === 'hardBreak') return
                                if (child.isText && !child.text.trim()) return
                                kept.push(stripUnwantedMarks(child, schema))
                            })

                            if (!kept.length) return

                            const rebuilt = block.type.create(block.attrs, Fragment.fromArray(kept))
                            if (isEmptyBlock(rebuilt)) return

                            processedBlocks.push(rebuilt)
                        })

                        if (!processedBlocks.length) return true

                        const fragment = Fragment.fromArray(processedBlocks)
                        const newSlice = new Slice(fragment, 0, 0)

                        const tr = view.state.tr.replaceSelection(newSlice).scrollIntoView()
                        view.dispatch(tr)
                        return true
                    },
                },
            }),
        ]
    },
})

// Remove inline styles/classes and empty blocks from raw HTML
function cleanHTMLBeforeParse(html) {
    const tempDiv = document.createElement('div')
    tempDiv.innerHTML = html

    tempDiv.querySelectorAll('[style]').forEach((el) => el.removeAttribute('style'))
    tempDiv.querySelectorAll('[class]').forEach((el) => el.removeAttribute('class'))
    tempDiv.querySelectorAll('br.ProseMirror-trailingBreak, br.ProseMirror-separator').forEach((el) => el.remove())

    let changed = true
    while (changed) {
        changed = false
        tempDiv.querySelectorAll('p,div,h1,h2,h3,h4,h5,h6,li').forEach((el) => {
            // keep if media exists
            if (el.querySelector('img,iframe,video,object,embed')) return

            const text = (el.textContent || '').replace(/[\s\u00A0\u200B]+/g, '')
            const onlyBrOrWs = Array.from(el.childNodes).every((n) => {
                if (n.nodeType === Node.TEXT_NODE) {
                    return !n.textContent || n.textContent.replace(/[\s\u00A0\u200B]+/g, '').length === 0
                }
                if (n.nodeType === Node.ELEMENT_NODE) {
                    return n.nodeName === 'BR'
                }
                return true
            })

            if (!text && onlyBrOrWs) {
                el.remove()
                changed = true
            }
        })
    }

    return tempDiv.innerHTML
}

function isEmptyBlock(node) {
    if (!node.isBlock) return false

    let hasMedia = false
    node.content.forEach((child) => {
        if (!child.isText && child.type.name !== 'hardBreak') hasMedia = true
    })
    if (hasMedia) return false

    const text = (node.textContent || '').replace(/[\s\u00A0\u200B]+/g, '')
    return text.length === 0
}

function stripUnwantedMarks(node, schema) {
    if (node.isText) {
        const allowed = ['bold', 'italic', 'underline', 'strike', 'link']
        const filtered = node.marks.filter((m) => allowed.includes(m.type.name))
        return node.mark(filtered)
    }

    if (node.content && node.content.size > 0) {
        const children = []
        node.content.forEach((child) => children.push(stripUnwantedMarks(child, schema)))
        return node.copy(Fragment.fromArray(children))
    }

    return node
}

export default PasteAsParagraphs
