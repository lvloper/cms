// Custom paste handler for TipTap editor - intercepts paste at DOM level.
// Normalizes pasted text: collapses multiple newlines, strips fonts.

(function() {
    'use strict';

    function getPlainText(event) {
        const plain = event.clipboardData?.getData('text/plain');
        if (plain) return plain;

        const html = event.clipboardData?.getData('text/html');
        if (!html) return null;

        try {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            return doc.body.innerText || null;
        } catch (e) {
            return null;
        }
    }

    function normalizeText(text) {
        return text
            .replace(/\r\n/g, '\n')
            .replace(/\n{2,}/g, '\n')  // multiple blank lines => single break
            .replace(/^\n+/, '')        // trim leading
            .replace(/\n+$/, '');       // trim trailing
    }

    function handlePaste(event) {
        // Only intercept if we're in a TipTap editor
        const target = event.target;
        const editor = target.closest('.tiptap-prosemirror-wrapper, .ProseMirror');
        if (!editor) return;

        const text = getPlainText(event);
        if (!text || !/\r?\n/.test(text)) return;

        event.preventDefault();
        event.stopPropagation();

        const normalized = normalizeText(text);

        // Insert as plain text, the editor will handle newlines
        // Using execCommand as fallback for maximum compatibility
        if (document.queryCommandSupported && document.queryCommandSupported('insertText')) {
            document.execCommand('insertText', false, normalized);
        } else {
            // Fallback: dispatch input event
            const inputEvent = new InputEvent('beforeinput', {
                inputType: 'insertText',
                data: normalized,
                bubbles: true,
                cancelable: true,
            });
            target.dispatchEvent(inputEvent);
        }
    }

    // Attach listener to document, capturing phase to intercept before editor
    document.addEventListener('paste', handlePaste, true);

    console.log('TipTap paste handler loaded');
})();
