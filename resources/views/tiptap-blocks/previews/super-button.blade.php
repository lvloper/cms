@php
    $uid = uniqid();
@endphp
<div x-on:mouseover="matchHeight();"
x-data="{ 
loading: true,
changeIframeContent() { 
    const iframe = $refs.iframePreview;
    const main = iframe.contentDocument.querySelector('#main');
    const content = $refs.blockPreviewer.innerHTML;

    if (main) {
        main.innerHTML = content;

        if (iframe.contentWindow.Alpine) {
            iframe.contentWindow.Alpine.start();
            iframe.contentWindow.Alpine.initTree(main);
        }
    }
},
matchHeight() {
    const el = $refs.iframePreview.contentDocument.querySelector('#main .block-preview');

    if(  el  ) {

        const mb = parseInt(window.getComputedStyle(el).getPropertyValue('margin-bottom'));
        const h = el.offsetHeight + mb;

        $refs.iframePreview.style.height =  h + 'px';
    }
}
}" x-init="
$refs.iframePreview.onload = () => {
    loading = false;
    changeIframeContent();
    matchHeight();
};

$wire.$watch('data', () => {
    setTimeout(() => { changeIframeContent(); matchHeight(); }, 10);
});

$refs.iframePreview.contentWindow.addEventListener('resize', () => {
    matchHeight();
});

$refs.iframePreview.contentDocument.querySelector('#main').addEventListener('resize', () => {
  console.dir('resize');
    matchHeight();
});
">
    <div x-ref="blockPreviewer" class="hidden">
        @include('tiptap-blocks.rendered.button')
    </div>
    <iframe id="iframe{{ $uid }}" x-cloak x-show="!loading" x-ref="iframePreview"
        src="{{ route('preview.blocks') }}" frameborder="0"
        style="width: 100%; height: 100px; overflow: hidden;"></iframe>
</div>