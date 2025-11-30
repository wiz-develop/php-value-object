# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

PHP 8.4+ 向けの値オブジェクトライブラリ。不変性、自己検証、型安全性、値による等価性を持つドメイン値オブジェクトを提供する。

## 開発コマンド

```bash
# テスト実行
./vendor/bin/phpunit

# 単一テストファイル実行
./vendor/bin/phpunit tests/Unit/String/StringValueTest.php

# 単一テストメソッド実行
./vendor/bin/phpunit --filter testMethodName

# 静的解析（level: max）
./vendor/bin/phpstan analyse

# コードフォーマット
./vendor/bin/php-cs-fixer fix

# ドキュメント開発サーバー
cd docs && npm run docs:dev

# ドキュメント文章校正
cd docs && npm run textlint:fix
```

## アーキテクチャ

### 値オブジェクト階層構造

各値オブジェクトは以下の3層で構成される：

1. ファクトリインターフェース（`I*Factory`）- `from()`, `tryFrom()`, `fromNullable()` などの生成メソッド定義
2. ファクトリ trait（`*Factory`）- ファクトリメソッドのデフォルト実装
3. 基底クラス（`*Base`）- 値の保持と共通メソッド
4. 具象クラス - 継承して利用するクラス

例：`StringValue` の場合
- `src/String/Base/IStringValueFactory.php` - インターフェース
- `src/String/Base/StringValueFactory.php` - trait
- `src/String/Base/StringValueBase.php` - 基底クラス
- `src/String/StringValue.php` - 具象クラス

### ファクトリパターン

- `from($value)` - 直接インスタンス生成（検証失敗時は例外）
- `tryFrom($value)` - `Result<T, IErrorValue>` を返す（php-monad 使用）
- `fromNullable($value)` - `Option<T>` を返す（null 許容）
- `tryFromNullable($value)` - `Result<Option<T>, IErrorValue>` を返す

### 名前空間構造

```
WizDevelop\PhpValueObject\
├── Boolean/     # BooleanValue
├── String/      # StringValue, EmailAddress, Ulid
├── Number/
│   ├── Integer/ # IntegerValue 系の基底クラス
│   └── Decimal/ # DecimalValue 系の基底クラス（BCMath 使用）
├── DateTime/    # LocalDate, LocalTime, LocalDateTime, LocalDateRange
├── Collection/  # ArrayList, Map, Pair, ValueObjectList
├── Enum/        # EnumValueFactory
└── Error/       # IErrorValue, 各種エラークラス
```

### 値オブジェクト拡張

カスタム値オブジェクトは既存クラスを継承し、制約メソッドをオーバーライドする：

```php
#[ValueObjectMeta(name: '商品コード')]
final readonly class ProductCode extends StringValue
{
    #[Override]
    final public static function minLength(): int { return 5; }

    #[Override]
    final public static function maxLength(): int { return 5; }

    #[Override]
    final protected static function regex(): string { return '/^P[0-9]{4}$/'; }
}
```

### 依存ライブラリ

- `wiz-develop/php-monad` - Result/Option 型（エラーハンドリング）
- `wiz-develop/php-cs-fixer-config` - コードスタイル設定
