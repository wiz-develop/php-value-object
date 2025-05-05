<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Boolean;

use WizDevelop\PhpValueObject\Boolean\BooleanValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * BooleanValue抽象クラスのテスト用実装
 * 単にBooleanValueを継承するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: '真偽値')]
final readonly class TestBooleanValue extends BooleanValue
{
    // 追加実装なし - 基本クラスから継承
}
