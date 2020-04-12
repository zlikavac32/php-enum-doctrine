<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Unit\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\CustomRepresentationEnumType;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\InvalidEnumType;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\ToSmallCustomRepresentationEnumType;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\ToSmallYesNoEnumType;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnumType;

class EnumTypeTest extends TestCase
{
    const ENUM_YES_NO = 'enum_yes_no';
    const ENUM_TO_SMALL_YES_NO = 'enum_to_small_yes_no';
    const ENUM_INVALID = 'enum_invalid';
    const ENUM_TO_SMALL_CUSTOM_REPRESENTATION = 'enum_to_small_custom_representation';
    const ENUM_CUSTOM_REPRESENTATION = 'enum_custom_representation';

    /**
     * @var AbstractPlatform
     */
    private $platform;

    protected function setUp(): void
    {
        if (false === Type::hasType(self::ENUM_YES_NO)) {
            Type::addType(self::ENUM_YES_NO, YesNoEnumType::class);
        }
        if (false === Type::hasType(self::ENUM_TO_SMALL_YES_NO)) {
            Type::addType(self::ENUM_TO_SMALL_YES_NO, ToSmallYesNoEnumType::class);
        }
        if (false === Type::hasType(self::ENUM_INVALID)) {
            Type::addType(self::ENUM_INVALID, InvalidEnumType::class);
        }
        if (false === Type::hasType(self::ENUM_TO_SMALL_CUSTOM_REPRESENTATION)) {
            Type::addType(self::ENUM_TO_SMALL_CUSTOM_REPRESENTATION, ToSmallCustomRepresentationEnumType::class);
        }
        if (false === Type::hasType(self::ENUM_CUSTOM_REPRESENTATION)) {
            Type::addType(self::ENUM_CUSTOM_REPRESENTATION, CustomRepresentationEnumType::class);
        }

        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testThatConvertNullToPhpReturnsNull(): void
    {
        $this->assertSame(
            null,
            Type::getType(self::ENUM_YES_NO)
                ->convertToPHPValue(null, $this->platform)
        );
    }

    public function testThatConvertNameToPhpReturnsInstance(): void
    {
        $this->assertSame(
            YesNoEnum::YES(),
            Type::getType(self::ENUM_YES_NO)
                ->convertToPHPValue('YES', $this->platform)
        );
    }

    public function testThatConvertNullToDatabaseReturnsNull(): void
    {
        $this->assertSame(
            null,
            Type::getType(self::ENUM_YES_NO)
                ->convertToDatabaseValue(null, $this->platform)
        );
    }

    public function testThatConvertInstanceToDatabaseReturnsName(): void
    {
        $this->assertSame(
            'YES',
            Type::getType(self::ENUM_YES_NO)
                ->convertToDatabaseValue(YesNoEnum::YES(), $this->platform)
        );
    }

    public function testThatConvertInstanceToDatabaseReturnsCustomRepresentation(): void
    {
        $this->assertSame(
            'yes',
            Type::getType(self::ENUM_CUSTOM_REPRESENTATION)
                ->convertToDatabaseValue(YesNoEnum::YES(), $this->platform)
        );
        $this->assertSame(
            'noooo',
            Type::getType(self::ENUM_CUSTOM_REPRESENTATION)
                ->convertToDatabaseValue(YesNoEnum::NO(), $this->platform)
        );
    }

    public function testThatCommentHintIsRequired(): void
    {
        $this->assertTrue(
            Type::getType(self::ENUM_YES_NO)
                ->requiresSQLCommentHint($this->platform)
        );
    }

    public function testThatToSmallColumnThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum::YES() is longer than 2 characters');

        Type::getType(self::ENUM_TO_SMALL_YES_NO)
            ->requiresSQLCommentHint($this->platform);
    }

    public function testThatToSmallColumnForCustomRepresentationThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum::noooo() is longer than 4 characters');

        Type::getType(self::ENUM_TO_SMALL_CUSTOM_REPRESENTATION)
            ->requiresSQLCommentHint($this->platform);
    }

    public function testThatInvalidValueToPhpThrowsException(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value \'1\' of type \'integer\' to type \'enum_yes_no\'. Expected one of the following types: null, string');

        Type::getType(self::ENUM_YES_NO)
            ->convertToPHPValue(1, $this->platform);
    }

    public function testThatInvalidEnumNameToPhpThrowsException(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert database value "I_DO_NOT_EXIST" to Doctrine Type enum_yes_no');

        Type::getType(self::ENUM_YES_NO)
            ->convertToPHPValue('I_DO_NOT_EXIST', $this->platform);
    }

    public function testThatInvalidValueToDatabaseThrowsException(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Could not convert PHP value of type \'stdClass\' to type \'enum_yes_no\'. Expected one of the following types: null, Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum');

        Type::getType(self::ENUM_YES_NO)
            ->convertToDatabaseValue(new stdClass(), $this->platform);
    }

    public function testThatGetSqlDeclarationChecksForInvalidEnumType(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('stdClass does not have Zlikavac32\Enum\Enum as it\'s parent');

        Type::getType(self::ENUM_INVALID)
            ->getSQLDeclaration([], $this->platform);
    }

    public function testThatConvertToDatabaseValueChecksForInvalidEnumType(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('stdClass does not have Zlikavac32\Enum\Enum as it\'s parent');

        Type::getType(self::ENUM_INVALID)
            ->convertToDatabaseValue(1, $this->platform);
    }

    public function testThatConvertToPhpValueChecksForInvalidEnumType(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('stdClass does not have Zlikavac32\Enum\Enum as it\'s parent');

        Type::getType(self::ENUM_INVALID)
            ->convertToPHPValue(1, $this->platform);
    }

    public function testThatSqlDeclarationCorrect(): void
    {
        $this->platform->method('getVarcharTypeDeclarationSQL')
            ->with(
                [
                    'length' => 32,
                ]
            )
            ->willReturn('VARCHAR(32)');

        $this->assertSame(
            "VARCHAR(32)",
            Type::getType(self::ENUM_YES_NO)
                ->getSQLDeclaration(
                    [
                        'name' => 'some_column',
                    ],
                    $this->platform
                )
        );
    }
}
