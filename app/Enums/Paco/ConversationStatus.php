<?php

declare(strict_types=1);

namespace App\Enums\Paco;

enum ConversationStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Blocked = 'blocked';
}
