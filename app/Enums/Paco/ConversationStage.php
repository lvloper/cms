<?php

declare(strict_types=1);

namespace App\Enums\Paco;

enum ConversationStage: string
{
    case New = 'new';
    case UnderstandingNeed = 'understanding_need';
    case ContactRequired = 'contact_required';
    case TrustBuilding = 'trust_building';
    case Qualifying = 'qualifying';
    case ReadyToClose = 'ready_to_close';
    case ClosedPendingReview = 'closed_pending_review';
    case ClosedAbandoned = 'closed_abandoned';
    case Blocked = 'blocked';
}
