<?php

namespace App\Filament\Forms\Components;

use Wallacemartinss\FilamentIconPicker\Forms\Components\IconPickerField;

class IconPicker
{
    public static function make(string $name = 'icon'): IconPickerField
    {
        return IconPickerField::make($name)
            ->allowedSets(['lucide'])
            ->modalSize('5xl')
            ->searchable();
    }

    public static function social(string $name = 'icon'): IconPickerField
    {
        return IconPickerField::make($name)
            ->allowedSets(['fontawesome-brands', 'fontawesome-solid'])
            ->modalSize('5xl')
            ->searchable();
    }
}
