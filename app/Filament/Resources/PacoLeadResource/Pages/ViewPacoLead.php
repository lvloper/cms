<?php

declare(strict_types=1);

namespace App\Filament\Resources\PacoLeadResource\Pages;

use App\Filament\Resources\PacoLeadResource;
use App\Services\Paco\PacoConversationPresenter;
use Filament\Resources\Pages\ViewRecord;

final class ViewPacoLead extends ViewRecord
{
    protected static string $resource = PacoLeadResource::class;

    protected static ?string $title = 'Ver consulta';

    protected string $view = 'filament.admin.pages.paco-lead-view';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->getRecord()->load([
            'conversation.events',
            'conversation.campaign:id,code,name',
        ]);
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'lead' => $this->getRecord(),
            'conversationState' => app(PacoConversationPresenter::class)->state($this->getRecord()->conversation),
        ];
    }
}
