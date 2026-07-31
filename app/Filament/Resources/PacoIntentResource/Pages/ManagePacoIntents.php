<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoIntentResource\Pages;

use App\Filament\Resources\PacoIntentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePacoIntents extends ManageRecords
{
    protected static string $resource = PacoIntentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nueva intención')];
    }
}
