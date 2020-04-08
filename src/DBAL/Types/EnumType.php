<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use LogicException;
use function Zlikavac32\Enum\assertFqnIsEnumClass;
use Zlikavac32\Enum\Enum;
use Zlikavac32\Enum\EnumNotFoundException;

abstract class EnumType extends Type
{
    /**
     * @var string|Enum
     */
    private $enumClass;

    private bool $checkedForNameLengths = false;

    final public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $this->ensureStateIsValid();

        return $platform->getVarcharTypeDeclarationSQL([
            'length' => $this->columnLength()
        ]);
    }

    final public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $this->ensureStateIsValid();

        if (null === $value) {
            return null;
        }

        if (false === ($value instanceof $this->enumClass)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', $this->enumClass]);
        }

        $name = $value->name();

        return $name;
    }

    final public function convertToPHPValue($value, AbstractPlatform $platform): ?Enum
    {
        $this->ensureStateIsValid();

        if (null === $value) {
            return null;
        }

        if (false === is_string($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'string']);
        }

        try {
            return $this->enumClass::valueOf($value);
        } catch (EnumNotFoundException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
    }

    final public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        $this->ensureStateIsValid();

        return true;
    }

    private function ensureStateIsValid(): void
    {
        $this->ensureEnumClassIsSetAndValid();
        $this->ensureEnumLengthsAreValid();
    }

    private function ensureEnumClassIsSetAndValid(): void
    {
        if (is_string($this->enumClass)) {
            return ;
        }

        $enumClass = $this->enumClass();

        assertFqnIsEnumClass($enumClass);

        $this->enumClass = $enumClass;
    }

    private function ensureEnumLengthsAreValid(): void
    {
        if ($this->checkedForNameLengths) {
            return ;
        }

        $columnLength = $this->columnLength();

        foreach ($this->enumClass::values() as $element) {
            $this->assertElementNameLengthIsValid($element, $columnLength);
        }
    }

    private function assertElementNameLengthIsValid(Enum $element, int $columnLength): void
    {
        $elementName = $element->name();

        if (strlen($elementName) <= $columnLength) {
            return ;
        }

        throw new LogicException(
            sprintf(
                '%s::%s() is longer than %d characters',
                $this->enumClass,
                $elementName,
                $columnLength
            )
        );
    }

    abstract protected function enumClass(): string;

    /**
     * Column length used for variable character column definition that will hold the enum name as a value. Override
     * if 32 is to big or to small.
     */
    protected function columnLength(): int
    {
        return 32;
    }
}
