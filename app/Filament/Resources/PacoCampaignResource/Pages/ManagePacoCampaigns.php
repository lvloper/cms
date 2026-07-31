<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoCampaignResource\Pages;

use App\Filament\Resources\PacoCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePacoCampaigns extends ManageRecords
{
    protected static string $resource = PacoCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nueva campaña')];
    }
}
