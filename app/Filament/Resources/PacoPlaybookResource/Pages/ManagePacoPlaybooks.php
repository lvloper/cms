<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoPlaybookResource\Pages;

use App\Filament\Resources\PacoPlaybookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePacoPlaybooks extends ManageRecords
{
    protected static string $resource = PacoPlaybookResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo playbook')];
    }
}
