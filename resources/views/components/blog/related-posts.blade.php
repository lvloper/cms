@php
    $related = $blog->related();
@endphp

@if($related->count() > 0)
<div>
    <x-common.line-title size="xl" title="Te puede interesar" containerClass="container-notice" />

    <div class="pt-12 pb-20 bg-gray-100 container-notice">
        
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" 
        x-data 
        x-masonry>
            @foreach($related as $item)
                <div class="masonry-item">
                    <x-blog.card :item="$item" />
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif