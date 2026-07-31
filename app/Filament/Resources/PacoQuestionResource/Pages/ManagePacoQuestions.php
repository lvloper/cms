<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoQuestionResource\Pages;

use App\Filament\Resources\PacoQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

final class ManagePacoQuestions extends ManageRecords
{
    protected static string $resource = PacoQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nueva pregunta')];
    }
}
