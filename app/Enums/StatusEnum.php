<?php

namespace App\Enums;


enum StatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DECLINED = 'declined';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case UNCLEAN = 'unclean';
    case INCOMPLETE = 'incomplete';
    case PROCESSING = 'processing';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::DECLINED => 'Declined',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::UNCLEAN => 'Unclean',
            self::INCOMPLETE => 'Incomplete',
            self::PROCESSING => 'Processing',
        };
    }
}


// use Spatie\Enum\Laravel\Enum;

// final class StatusEnum extends Enum
// {
// }
