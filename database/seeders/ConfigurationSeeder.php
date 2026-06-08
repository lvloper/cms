<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuration;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        Configuration::updateOrCreate(
            ['key' => 'site-name'],
            [
                'type' => 'text',
                'value' => ['text' => env('APP_NAME', 'CMS Base')],
            ]
        );
    }
}
