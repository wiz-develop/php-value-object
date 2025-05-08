<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\String;

use WizDevelop\PhpValueObject\String\Ulid;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * UlidValue抽象クラスのテスト用実装
 * 単にUlidValueを継承するだけのシンプルなクラス
 */
#[ValueObjectMeta(displayName: 'ULID', description: 'ULIDのテスト実装')]
final readonly class TestUlidValue extends Ulid
{
    // 追加実装なし - 基本クラスから継承
}
