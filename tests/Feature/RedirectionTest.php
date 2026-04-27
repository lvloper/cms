<?php

namespace Tests\Feature;

use App\Models\Redirection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RedirectionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_redirection_middleware_redirects_correctly()
    {
        // Crear una redirección de prueba
        $redirection = Redirection::create([
            'old_url' => '/test-old-page',
            'new_url' => '/test-new-page',
            'redirect_code' => 301,
            'is_active' => true,
            'description' => 'Test redirection'
        ]);

        // Hacer una petición a la URL antigua
        $response = $this->get('/test-old-page');

        // Verificar que se redirecciona correctamente
        $response->assertRedirect('/test-new-page');
        $response->assertStatus(301);
    }

    public function test_external_redirection_works()
    {
        // Crear redirección externa
        $redirection = Redirection::create([
            'old_url' => '/external-test',
            'new_url' => 'https://example.com',
            'redirect_code' => 302,
            'is_active' => true,
            'description' => 'External redirection test'
        ]);

        $response = $this->get('/external-test');
        
        $response->assertRedirect('https://example.com');
        $response->assertStatus(302);
    }

    public function test_inactive_redirection_is_ignored()
    {
        // Crear redirección inactiva
        $redirection = Redirection::create([
            'old_url' => '/inactive-redirect',
            'new_url' => '/some-page',
            'redirect_code' => 301,
            'is_active' => false,
            'description' => 'Inactive redirection'
        ]);

        $response = $this->get('/inactive-redirect');
        
        // No debe redireccionar, debe devolver 404
        $response->assertStatus(404);
    }

    public function test_redirection_preserves_query_parameters()
    {
        $redirection = Redirection::create([
            'old_url' => '/old-with-params',
            'new_url' => '/new-with-params',
            'redirect_code' => 301,
            'is_active' => true,
            'description' => 'Query param test'
        ]);

        $response = $this->get('/old-with-params?utm_source=test&page=2');
        
        // Query parameters may be reordered by PHP's http_build_query; verify the redirect
        // destination is correct regardless of parameter order.
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/new-with-params', $location);
        $this->assertStringContainsString('utm_source=test', $location);
        $this->assertStringContainsString('page=2', $location);
    }

    public function test_redirection_cache_is_used()
    {
        $redirection = Redirection::create([
            'old_url' => '/cached-redirect',
            'new_url' => '/cached-destination',
            'redirect_code' => 301,
            'is_active' => true,
            'description' => 'Cache test'
        ]);

        // Primera petición
        $response1 = $this->get('/cached-redirect');
        $response1->assertRedirect('/cached-destination');

        // Verificar que se creó el cache
        $cacheKey = "redirection:" . md5('/cached-redirect');
        $this->assertTrue(Cache::has($cacheKey));

        // Segunda petición debería usar cache
        $response2 = $this->get('/cached-redirect');
        $response2->assertRedirect('/cached-destination');
    }

    public function test_cache_is_cleared_when_redirection_is_updated()
    {
        $redirection = Redirection::create([
            'old_url' => '/cache-update-test',
            'new_url' => '/original-destination',
            'redirect_code' => 301,
            'is_active' => true,
            'description' => 'Cache update test'
        ]);

        // Primera petición para crear cache
        $response1 = $this->get('/cache-update-test');
        $response1->assertRedirect('/original-destination');

        $cacheKey = "redirection:" . md5('/cache-update-test');
        $this->assertTrue(Cache::has($cacheKey));

        // Actualizar la redirección
        $redirection->update(['new_url' => '/updated-destination']);

        // Cache debería haberse limpiado
        $this->assertFalse(Cache::has($cacheKey));

        // Nueva petición debería usar la URL actualizada
        $response2 = $this->get('/cache-update-test');
        $response2->assertRedirect('/updated-destination');
    }

    public function test_trailing_slash_normalization()
    {
        $redirection = Redirection::create([
            'old_url' => '/trailing-slash-test',
            'new_url' => '/destination',
            'redirect_code' => 301,
            'is_active' => true,
            'description' => 'Trailing slash test'
        ]);

        // Probar sin trailing slash
        $response1 = $this->get('/trailing-slash-test');
        $response1->assertRedirect('/destination');

        // Probar con trailing slash
        $response2 = $this->get('/trailing-slash-test/');
        $response2->assertRedirect('/destination');
    }

    public function test_redirection_model_attributes()
    {
        $redirection = Redirection::create([
            'old_url' => '/model-test',
            'new_url' => 'https://external.com',
            'redirect_code' => 301,
            'is_active' => true,
            'description' => 'Model test'
        ]);

        // Test is_external attribute
        $this->assertTrue($redirection->is_external);

        // Test formatted_old_url attribute
        $this->assertEquals('/model-test', $redirection->formatted_old_url);

        // Test full_new_url attribute
        $this->assertEquals('https://external.com', $redirection->full_new_url);

        // Test internal URL
        $internalRedirection = Redirection::create([
            'old_url' => '/internal-test',
            'new_url' => '/internal-destination',
            'redirect_code' => 301,
            'is_active' => true,
        ]);

        $this->assertFalse($internalRedirection->is_external);
        $this->assertEquals(url('/internal-destination'), $internalRedirection->full_new_url);
    }

    public function test_redirect_codes_helper()
    {
        $codes = Redirection::getRedirectCodes();
        
        $this->assertIsArray($codes);
        $this->assertArrayHasKey(301, $codes);
        $this->assertArrayHasKey(302, $codes);
        $this->assertEquals('301 - Movido permanentemente', $codes[301]);
    }
}