<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;  
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;


use Spatie\Tags\Tag;
use App\Models\Blog;  

class BlogPosts extends Component
{
    public int $on_page = 27;  

    #[Url] 
    public ?string $tag = null;

    public bool $isLoading = false;
    public bool $noMorePosts = false;
    public int $loadCount = 0;

    public function mount()
    {
        // $this->tag = request()->get('tag') ?? null;
    }
  
    #[Computed]  
    public function posts(): Collection  
    {  
        $posts = Blog::orderBy('published_at', 'desc')
        ->when($this->tag, function ($query) {
            $query->withAnyTags($this->tag);
        })
        ->isPublished()
        ->take($this->on_page)
        ->get();

        $this->noMorePosts = $posts->count() < $this->on_page;

        $this->isLoading = false;

        $this->dispatch('postsLoaded');
        

        return $posts;
    }  
  
    public function loadMore(): void  
    {  
        $this->loadCount++;
        $this->on_page += 18;  
        $this->isLoading = true;
    }  

    public function navigateToTag(string $tagName): void
    {
        $this->tag = $tagName;
        $this->on_page = 27;

    }

    public function highlightTags(): Collection
    {
        return cache()->remember('highlight_tags', 3600, function () {
            // Get all posts in the last 4 months
            $posts = Blog::where('published_at', '>=', 
            now()->subMonths(8))
                ->where('image', '!=', null)
                ->isPublished()
                ->limit(60)
                ->get();

            $tags = $posts->pluck('tags')
                // ->flatten()
                ->map(fn($group) => $group->first());

            // Asociar imagen destacada a cada etiqueta usando los posts ya cargados
            $tags = $tags->map(function ($tag) use ($posts) {
                $post = $posts->first(function($post) use ($tag) {
                    return $post->tags->contains($tag);
                });


                if ($post) {
                    $tag->thumb = $post->thumb;
                }
                return $tag;
            });

            return $tags;
        });
    }
    
    public function render()
    {
        return view('livewire.blog-posts');
    }
}
