<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Fixtures;

use Zlikavac32\DoctrineEnum\DBAL\Types\EnumType;

class YesNoEnumType extends EnumType
{
    protected function enumClass(): string
    {
        return YesNoEnum::class;
    }

    public function getName(): string
    {
        return 'enum_yes_no';
    }
}
