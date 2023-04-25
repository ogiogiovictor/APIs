<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**  
 * @method static self FT_eleven()
 * @method static self FT_thirty_three()
 */

final class FeederEnum extends Enum
{

    protected static function values(): array{
        return [
            'FT_eleven' => '11KV Feeder',
            'FT_thirty_three' => '33KV Feeder',
        ];
    }

}
