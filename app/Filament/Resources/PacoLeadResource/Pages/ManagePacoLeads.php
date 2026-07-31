<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoLeadResource\Pages;

use App\Filament\Resources\PacoLeadResource;
use Filament\Resources\Pages\ManageRecords;

final class ManagePacoLeads extends ManageRecords
{
    protected static string $resource = PacoLeadResource::class;
}
