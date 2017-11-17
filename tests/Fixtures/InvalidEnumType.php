<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Fixtures;

use stdClass;
use Zlikavac32\DoctrineEnum\DBAL\Types\EnumType;

class InvalidEnumType extends EnumType
{
    protected function enumClass(): string
    {
        return stdClass::class;
    }

    public function getName(): string
    {
        return 'enum_invalid';
    }
}
