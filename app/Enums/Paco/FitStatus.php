<?php

declare(strict_types=1);

namespace App\Enums\Paco;

enum FitStatus: string
{
    case Supported = 'supported';
    case Conditional = 'conditional';
    case Unsupported = 'unsupported';
    case Unknown = 'unknown';
}
