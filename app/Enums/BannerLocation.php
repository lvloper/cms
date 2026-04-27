<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum BannerLocation: string implements HasLabel, HasColor, HasIcon
{
    case Sidebar = 'sidebar';
    case Novedades = 'novedades';
    case Popup = 'popup';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Sidebar => 'Barra lateral',
            self::Novedades => 'Novedades',
            self::Popup => 'Popup',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Sidebar => 'info',
            self::Novedades => 'success',
            self::Popup => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Sidebar => 'heroicon-m-bars-3',
            self::Novedades => 'heroicon-m-megaphone',
            self::Popup => 'heroicon-m-bell',
        };
    }

    public function getWidth(): int
    {
        return match ($this) {
            self::Sidebar => 500,
            self::Novedades => 1280,
            self::Popup => 500,
        };
    }

    public function getHeight(): int
    {
        return match ($this) {
            self::Sidebar => 300,
            self::Novedades => 350,
            self::Popup => 500,
        };
    }
}
