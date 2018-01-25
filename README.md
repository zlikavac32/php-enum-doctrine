# PHP Enum Doctrine

[![Build Status](https://travis-ci.org/zlikavac32/php-enum-doctrine.svg?branch=master)](https://travis-ci.org/zlikavac32/php-enum-doctrine) [![Latest Stable Version](https://poser.pugx.org/zlikavac32/php-enum-doctrine/v/stable)](https://packagist.org/packages/zlikavac32/php-enum-doctrine) [![License](https://poser.pugx.org/zlikavac32/php-enum-doctrine/license)](https://packagist.org/packages/zlikavac32/php-enum-doctrine) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zlikavac32/php-enum-doctrine/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zlikavac32/php-enum-doctrine/?branch=master) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/f8fad3e112734573b3fbb2ca05f24bf5)](https://www.codacy.com/app/zlikavac32/php-enum-doctrine?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=zlikavac32/php-enum-doctrine&amp;utm_campaign=Badge_Grade)

Doctrine support for [zlikavac32/php-enum](https://github.com/zlikavac32/php-enum).

## Table of contents

1. [Installation](#installation)
1. [Usage](#usage)
    1. [Custom column length](#custom-column-length)
1. [Limitations](#limitations)
1. [Further work](#further-work)

## Installation

Recommended installation is through Composer.

```
composer require zlikavac32/php-enum-doctrine
```

## Usage

Assumption is that there exists a valid enum `\YesNoEnum`.

Create a new type that extends `\Zlikavac32\DoctrineEnum\DBAL\Types\EnumType`.

```php
use Zlikavac32\DoctrineEnum\DBAL\Types\EnumType;

class YesNoEnumType extends EnumType 
{
    // ...
}
```

Next, define `protected function enumClass(): string`. This method should return FQN of the enum class that this type exposes to the Doctrine.

```php
protected function enumClass(): string
{
    return \YesNoEnum::class;
}
```

Define Doctrine method `public function getName(): string` that defines type's name.

```php
public function getName(): string
{
    return 'enum_yes_no';
}
```

And that's it. Only thing left to do is to register the type using 


```php
\Doctrine\DBAL\Types\Type::addType('enum_yes_no', \YesNoEnumType::class);
```

You can now use `enum_yes_no` type.

```php
/**
 * @Column(type="enum_yes_no", nullable=true)
 * @var \YesNoEnum|null
 */
private $yesNo;
```

For more info on the custom Doctrine mapping types, check [official documentation](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#custom-mapping-types).

### Custom column length

Internally this library uses `varchar` type with the maximum length of `32`. If you want to fit the length to your own needs, just override method `protected function columnLength(): int`.

```php
protected function columnLength(): int
{
    return 16;
}
```

Note that on types first usage, all enum elements names are checked against specified column length. If a name longer than maximum length is detected, a `\LogicException` is thrown.

## Limitations

This library does not use platform dependent types like `enum` in `MySQL` or custom types in `PostgresSQL`. Instead, `varchar` is used.

Reasons for this are:

- `Doctrine` can not diff enum contents because that's types intrinsic property
- for `PostgresSQL` we can't diff column because type is not in Doctrine control
- column constraints can not be used because they break `ALTER` syntax

If you know how to avoid any of this, please let me know.

## Further work

Figure out how to overcome issues in [Limitations](#limitations).
