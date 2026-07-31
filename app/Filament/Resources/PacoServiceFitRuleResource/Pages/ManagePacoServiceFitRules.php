<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoServiceFitRuleResource\Pages;

use App\Filament\Resources\PacoServiceFitRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePacoServiceFitRules extends ManageRecords
{
    protected static string $resource = PacoServiceFitRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nueva regla')];
    }
}
