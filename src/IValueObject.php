<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use JsonSerializable;

/**
 * すべての値オブジェクトの基底インターフェース
 * @see ValueObjectDefault
 */
interface IValueObject extends JsonSerializable
{
    public function equals(self $other): bool;
}
