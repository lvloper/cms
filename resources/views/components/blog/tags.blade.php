<div class="flex gap-2">
    @foreach ($blog->tags as $tag)
        <a href="{{ $blog->tagRoute($tag) }}" class="px-3 py-1 text-white rounded-tl-lg rounded-br-lg bg-secondary">{{ $tag->name }}</a>
    @endforeach
</div>