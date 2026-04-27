<?php

namespace App\Filament\Resources\Bases;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Status;

class ListBase extends ListRecords
{


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {

        $model = Static::getModel();

        $tabs[] = Tab::make('all')
            ->label('Todos')
            ->badgeColor('primary')
            ->modifyQueryUsing(fn (Builder $query) => $query)
            ->badge(fn (): int => $model::count());

        $statuses = Status::cases();

        foreach ($statuses as $status) {
            $tabs[] = Tab::make($status->name)
                ->label($status->getLabel())
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('route', fn (Builder $query) => $query->where('status', $status->value)))
                ->badge(fn (): int => $model::whereHas('route', fn (Builder $query) => $query->where('status', $status->value))->count());
        }

        return $tabs;
    }
}
