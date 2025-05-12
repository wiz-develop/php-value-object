<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Enum;

use WizDevelop\PhpValueObject\IValueObject;

/**
 * 列挙型の値オブジェクト インターフェース
 * @see WizDevelop\PhpValueObject\Enum\EnumValueFactory
 */
interface IEnumValue extends IValueObject, IEnumValueFactory
{
}
