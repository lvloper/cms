<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Route;
use App\Enums\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_route_returns_404_when_not_set_up()
    {
        $response = $this->get('/');
        // Home route doesn't exist in fresh DB, so 404 is expected
        $response->assertStatus(404);
    }

    public function test_page_can_be_created_with_route()
    {
        $page = Page::create(['name' => 'Test Page', 'blocks' => []]);

        $route = Route::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'full_slug' => 'test-page',
            'status' => Status::Published,
            'routable_type' => Page::class,
            'routable_id' => $page->id,
        ]);

        $this->assertDatabaseHas('pages', ['name' => 'Test Page']);
        $this->assertDatabaseHas('routes', ['slug' => 'test-page', 'full_slug' => 'test-page']);
    }

    public function test_published_page_is_accessible()
    {
        $page = Page::create(['name' => 'Published Page', 'blocks' => []]);

        Route::create([
            'title' => 'Published Page',
            'slug' => 'published-page',
            'full_slug' => 'published-page',
            'status' => Status::Published,
            'routable_type' => Page::class,
            'routable_id' => $page->id,
        ]);

        $response = $this->get('/published-page');
        $response->assertStatus(200);
    }

    public function test_draft_page_returns_404()
    {
        $page = Page::create(['name' => 'Draft Page', 'blocks' => []]);

        Route::create([
            'title' => 'Draft Page',
            'slug' => 'draft-page',
            'full_slug' => 'draft-page',
            'status' => Status::Draft,
            'routable_type' => Page::class,
            'routable_id' => $page->id,
        ]);

        $response = $this->get('/draft-page');
        $response->assertStatus(404);
    }

    public function test_route_full_slug_is_computed_for_nested_routes()
    {
        $parentPage = Page::create(['name' => 'Parent', 'blocks' => []]);
        $parent = Route::create([
            'title' => 'Parent',
            'slug' => 'parent',
            'full_slug' => 'parent',
            'status' => Status::Published,
            'routable_type' => Page::class,
            'routable_id' => $parentPage->id,
        ]);

        $childPage = Page::create(['name' => 'Child', 'blocks' => []]);
        $child = Route::create([
            'title' => 'Child',
            'slug' => 'child',
            'full_slug' => 'parent/child',
            'parent_id' => $parent->id,
            'status' => Status::Published,
            'routable_type' => Page::class,
            'routable_id' => $childPage->id,
        ]);

        $response = $this->get('/parent/child');
        $response->assertStatus(200);
    }

    public function test_deleting_page_also_deletes_route()
    {
        $page = Page::create(['name' => 'To Delete', 'blocks' => []]);

        Route::create([
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'full_slug' => 'to-delete',
            'status' => Status::Published,
            'routable_type' => Page::class,
            'routable_id' => $page->id,
        ]);

        $this->assertDatabaseHas('routes', ['slug' => 'to-delete']);

        $page->delete();

        $this->assertDatabaseMissing('routes', ['slug' => 'to-delete']);
    }

    public function test_sitemap_returns_xml()
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
    }

    public function test_nonexistent_route_returns_404()
    {
        $response = $this->get('/this-page-does-not-exist');
        $response->assertStatus(404);
    }
}
