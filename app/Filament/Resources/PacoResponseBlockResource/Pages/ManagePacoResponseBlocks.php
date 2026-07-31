<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoResponseBlockResource\Pages;

use App\Filament\Resources\PacoResponseBlockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePacoResponseBlocks extends ManageRecords
{
    protected static string $resource = PacoResponseBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo bloque')];
    }
}
