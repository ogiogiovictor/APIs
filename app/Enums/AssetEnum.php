<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**  
 * @method static self DT_eleven()
 * @method static self DT_thirty_three()
 */

final class AssetEnum extends Enum
{
    protected static function labels(): array{
        return [
            'DT_eleven' => 'Distribution Sub Station 11KV_415V',
            'DT_thirty_three' => 'Distribution Sub Station 33KV_415V',
        ];
    }

    protected static function values(): array{
        return [
            'DT_eleven' => 'Distribution Sub Station 11KV_415V',
            'DT_thirty_three' => 'Distribution Sub Station 33KV_415V',
        ];
    }




    
}
