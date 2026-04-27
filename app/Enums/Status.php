<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasIcon;


enum Status: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Reviewing = 'reviewing';
    case Published = 'published';
    case Archived = 'archived';
    case Hidden = 'hidden';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Reviewing => 'En revisión',
            self::Published => 'Publicado',
            self::Archived => 'Archivado',
            self::Hidden => 'Oculto',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Reviewing => 'warning',
            self::Published => 'success',
            self::Archived => 'danger',
            self::Hidden => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-m-pencil',
            self::Reviewing => 'heroicon-m-eye',
            self::Published => 'heroicon-m-check',
            self::Archived => 'heroicon-m-x-mark',
            self::Hidden => 'heroicon-m-eye-slash',
        };
    }

}