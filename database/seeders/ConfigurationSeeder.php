<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuration;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    //php artisan db:seed --class=ConfigurationSeeder
    public function run(): void
    {
        // Configuraciones compartidas entre menú móvil y desktop
        Configuration::updateOrCreate(
            ['key' => 'donation-link'],
            [
                'type' => 'url',
                'value' => [
                    'route' => [
                        'route_id' => '0',
                        'external_url' => 'https://asociate.huesped.org.ar/vos2/?utm_source=Web&utm_medium=link&utm_campaign=banner-violeta&utm_content=',
                        'new_window' => true
                    ]
                ]
            ]
        );

        Configuration::updateOrCreate(
            ['key' => 'menu-mobile-text'],
            [
                'type' => 'rich_text',
                'value' => ['rich_content' => 'Te necesitamos <br> más que nunca']
            ]
        );

        Configuration::updateOrCreate(
            ['key' => 'menu-mobile-button-text'],
            [
                'type' => 'text',
                'value' => ['text' => 'Hace tu donación']
            ]
        );

        // Configuraciones específicas del menú desktop
        Configuration::updateOrCreate(
            ['key' => 'menu-desktop-text'],
            [
                'type' => 'rich_text',
                'value' => ['rich_content' => 'Te necesitamos más que nunca']
            ]
        );

        Configuration::updateOrCreate(
            ['key' => 'menu-desktop-button-text'],
            [
                'type' => 'text',
                'value' => ['text' => 'Quiero donar']
            ]
        );


    }
}
