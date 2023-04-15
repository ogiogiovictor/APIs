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
            'FT_eleven' => '11kv Feeder',
            'FT_thirty_three' => '33kv Feeder',
        ];
    }

}
