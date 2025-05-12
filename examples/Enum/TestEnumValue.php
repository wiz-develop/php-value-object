<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Enum;

use WizDevelop\PhpValueObject\Enum\EnumValueFactory;
use WizDevelop\PhpValueObject\Enum\EnumValueObjectDefault;
use WizDevelop\PhpValueObject\Enum\IEnumValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '列挙型', description: '列挙型の値オブジェクトの例')]
enum TestEnumValue: string implements IEnumValue
{
    use EnumValueFactory;
    use EnumValueObjectDefault;

    case Value1 = 'Value1';
    case Value2 = 'Value2';
    case Value3 = 'Value3';
}
