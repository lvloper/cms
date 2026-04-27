<?php

namespace Database\Seeders;

use App\Models\Redirection;
use Illuminate\Database\Seeder;

class RedirectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $redirections = [
            [
                'old_url' => '/old-page',
                'new_url' => '/new-page',
                'redirect_code' => 301,
                'is_active' => true,
                'description' => 'Redirección de página antigua a nueva',
            ],
            [
                'old_url' => '/contact-us',
                'new_url' => '/contacto',
                'redirect_code' => 301,
                'is_active' => true,
                'description' => 'Redirección de inglés a español',
            ],
            [
                'old_url' => '/temp-redirect',
                'new_url' => '/temporary-page',
                'redirect_code' => 302,
                'is_active' => true,
                'description' => 'Redirección temporal para mantenimiento',
            ],
            [
                'old_url' => '/external-link',
                'new_url' => 'https://google.com',
                'redirect_code' => 301,
                'is_active' => true,
                'description' => 'Redirección a sitio externo',
            ],
            [
                'old_url' => '/disabled-redirect',
                'new_url' => '/some-page',
                'redirect_code' => 301,
                'is_active' => false,
                'description' => 'Redirección deshabilitada para pruebas',
            ],
        ];

        foreach ($redirections as $redirection) {
            Redirection::create($redirection);
        }
    }
}