<?php

namespace App\Filament\Forms\Components;

use Guava\IconPicker\Forms\Components\IconPicker as GuavaIconPicker;

class IconPicker
{
    public static function make(string $name = 'icon'): GuavaIconPicker
    {
        return GuavaIconPicker::make($name)
            ->sets(['lucide']);
    }

    public static function social(string $name = 'icon'): GuavaIconPicker
    {
        return GuavaIconPicker::make($name)
            ->sets(['fontawesome-brands', 'fontawesome-solid']);
    }
}
