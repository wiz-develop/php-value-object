<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use JsonSerializable;
use Stringable;

/**
 * すべての値オブジェクトの基底インターフェース
 * @see WizDevelop\PhpValueObject\ValueObjectDefault
 */
interface IValueObject extends Stringable, JsonSerializable
{
    /**
     * 値オブジェクトの等価性を比較する
     * @param  static $other 比較対象の値オブジェクト
     * @return bool   等しい場合はtrue、そうでない場合はfalse
     */
    public function equals(self $other): bool;
}
