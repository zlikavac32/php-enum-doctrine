<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Fixtures;

use Zlikavac32\DoctrineEnum\DBAL\Types\EnumType;
use Zlikavac32\Enum\Enum;
use Zlikavac32\Enum\UnhandledEnumException;

class ToSmallCustomRepresentationEnumType extends EnumType
{

    protected function enumClass(): string
    {
        return YesNoEnum::class;
    }

    protected function enumToDatabaseValue(Enum $enum): string
    {
        switch ($enum) {
            case YesNoEnum::NO():
                return 'noooo';
            case YesNoEnum::YES():
                return 'yes';
            default:
                throw new UnhandledEnumException($enum);
        }
    }

    protected function columnLength(): int
    {
        return 4;
    }

    public function getName(): string
    {
        return 'enum_to_small_custom_representation';
    }
}
