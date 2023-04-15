<?php

namespace App\Enums;

enum AuthorityEnum: string
{
    case HEADQUATERS = 'hq';
    case REGION = 'region';
    case BUSINESSHUB = 'business_hub';
    case SERVICECENTER = 'service_center';

    public function label(): string
    {
        return match($this) {
            self::HEADQUATERS => 'hq',
            self::REGION => 'region',
            self::BUSINESSHUB => 'business_hub',
            self::SERVICECENTER => 'service_center',
        };
    }
}


// use Spatie\Enum\Laravel\Enum;

// final class AuthorityEnum extends Enum
// {
// }