<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Unit\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;
use stdClass;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\InvalidEnumType;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\ToSmallYesNoEnumType;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum;
use Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnumType;

class EnumTypeTest extends TestCase
{
    const ENUM_YES_NO = 'enum_yes_no';
    const ENUM_TO_SMALL_YES_NO = 'enum_to_small_yes_no';
    const ENUM_INVALID = 'enum_invalid';

    /**
     * @var AbstractPlatform
     */
    private $platform;

    protected function setUp()
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

    public function testThatCommentHintIsRequired(): void
    {
        $this->assertTrue(
            Type::getType(self::ENUM_YES_NO)
                ->requiresSQLCommentHint($this->platform)
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum::YES() is longer than 2 characters
     */
    public function testThatToSmallColumnThrowsException(): void
    {
        Type::getType(self::ENUM_TO_SMALL_YES_NO)
            ->requiresSQLCommentHint($this->platform);
    }

    /**
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     * @expectedExceptionMessage Could not convert PHP value '1' of type 'integer' to type 'enum_yes_no'. Expected one
     *     of the following types: null, string
     */
    public function testThatInvalidValueToPhpThrowsException(): void
    {
        Type::getType(self::ENUM_YES_NO)
            ->convertToPHPValue(1, $this->platform);
    }

    /**
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     * @expectedExceptionMessage Could not convert database value "I_DO_NOT_EXIST" to Doctrine Type enum_yes_no
     */
    public function testThatInvalidEnumNameToPhpThrowsException(): void
    {
        Type::getType(self::ENUM_YES_NO)
            ->convertToPHPValue('I_DO_NOT_EXIST', $this->platform);
    }

    /**
     * @expectedException \Doctrine\DBAL\Types\ConversionException
     * @expectedExceptionMessage Could not convert PHP value of type 'stdClass' to type 'enum_yes_no'. Expected one of
     *     the following types: null, Zlikavac32\DoctrineEnum\Tests\Fixtures\YesNoEnum
     */
    public function testThatInvalidValueToDatabaseThrowsException(): void
    {
        Type::getType(self::ENUM_YES_NO)
            ->convertToDatabaseValue(new stdClass(), $this->platform);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage stdClass does not have Zlikavac32\Enum\Enum as it's parent
     */
    public function testThatGetSqlDeclarationChecksForInvalidEnumType(): void
    {
        Type::getType(self::ENUM_INVALID)
            ->getSQLDeclaration([], $this->platform);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage stdClass does not have Zlikavac32\Enum\Enum as it's parent
     */
    public function testThatConvertToDatabaseValueChecksForInvalidEnumType(): void
    {
        Type::getType(self::ENUM_INVALID)
            ->convertToDatabaseValue(1, $this->platform);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage stdClass does not have Zlikavac32\Enum\Enum as it's parent
     */
    public function testThatConvertToPhpValueChecksForInvalidEnumType(): void
    {
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
