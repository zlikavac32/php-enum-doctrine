<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Fixtures;

use Zlikavac32\Enum\Enum;

/**
 * @method static YesNoEnum YES
 * @method static YesNoEnum NO
 */
abstract class YesNoEnum extends Enum
{
    protected static function enumerate(): array
    {
        return [
            'NO' => new class extends YesNoEnum {

            },
            'YES' => new class extends YesNoEnum {

            }
        ];
    }
}
