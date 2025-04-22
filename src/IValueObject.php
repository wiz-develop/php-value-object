<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use JsonSerializable;
use Stringable;

/**
 * すべての値オブジェクトの基底インターフェース
 * @see ValueObjectDefault
 */
interface IValueObject extends Stringable, JsonSerializable
{
    public function equals(self $other): bool;
}
