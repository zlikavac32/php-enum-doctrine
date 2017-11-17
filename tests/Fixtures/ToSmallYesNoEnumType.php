<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Fixtures;

use Zlikavac32\DoctrineEnum\DBAL\Types\EnumType;

class ToSmallYesNoEnumType extends EnumType
{
    protected function enumClass(): string
    {
        return YesNoEnum::class;
    }

    protected function columnLength(): int
    {
        return 2;
    }

    public function getName(): string
    {
        return 'enum_to_small_yes_no';
    }
}
