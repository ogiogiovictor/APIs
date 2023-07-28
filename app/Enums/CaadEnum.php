<?php

namespace App\Enums;


enum CaadEnum: string
{
    case PENDING = '0';
    case APPROVED_BY_DISTRICT_ACCOUNTANT = '1';
    case APPROVED_BY_BUSINESS_HUB_MANAGER = '2';
    case APPROVED_BY_AUDIT = '3';
    case APPROVED_BY_REGIONAL_MANAGER = '4';
    case APPROVED_BY_HCS = '5';
    case APPROVED_BY_CCO = '6';
    case APPROVED_BY_MD = '7';
    case ADMIN = '8'; // If ADMIN needs to have the same value as APPROVED_BY_DISTRICT_ACCOUNTANT, make sure to change it to '1' as well.
    case BILLING = '9';


    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED_BY_DISTRICT_ACCOUNTANT => 'Approved by District Accountant',
            self::APPROVED_BY_BUSINESS_HUB_MANAGER => 'Approved by Business Hub Manager',
            self::APPROVED_BY_AUDIT => 'Approved by Audit',
            self::APPROVED_BY_REGIONAL_MANAGER => 'Approved by Regional Manager',
            self::APPROVED_BY_HCS => 'Approved by HCS',
            self::APPROVED_BY_CCO => 'Approved by CCO',
            self::APPROVED_BY_MD => 'Approved by MD',
            self::ADMIN => 'Approved by Admin',
            self::BILLING => 'Completed and Customer Account Updated by Billing',
        };
    }
}


// use Spatie\Enum\Laravel\Enum;

// final class StatusEnum extends Enum
// {
// }
