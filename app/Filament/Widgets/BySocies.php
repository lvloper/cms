<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class BySocies extends Widget
{
    protected string $view = 'filament.admin.widgets.by-socies';

    public static function getSort(): int
    {
        return static::$sort ?? -1;
    }

    public static function isLazy(): bool
    {
        return false;
    }
}
