<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class PacoBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PacoIntentSeeder::class,
            PacoQuestionSeeder::class,
            PacoResponseBlockSeeder::class,
            PacoPlaybookSeeder::class,
            PacoServiceFitRuleSeeder::class,
            PacoCampaignSeeder::class,
        ]);
    }
}
