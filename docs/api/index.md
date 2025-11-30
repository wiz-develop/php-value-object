# API リファレンス

PHP Value Object ライブラリの全クラスの API リファレンスです。

## 概要

すべての値オブジェクトは以下の共通インターフェースを実装しています。

### IValueObject

```php
interface IValueObject extends JsonSerializable
{
    public function equals(IValueObject $other): bool;
}
```

## Boolean

| クラス | 説明 |
|--------|------|
| [BooleanValue](/api/boolean/boolean-value) | 真偽値を扱う値オブジェクト |

## String

| クラス | 説明 |
|--------|------|
| [StringValue](/api/string/string-value) | 汎用文字列値オブジェクト |
| [EmailAddress](/api/string/email-address) | メールアドレス値オブジェクト |
| [Ulid](/api/string/ulid) | ULID 値オブジェクト |

## Number

### 整数

| クラス | 説明 |
|--------|------|
| [IntegerValue](/api/number/integer-value) | 任意の整数値 |
| [PositiveIntegerValue](/api/number/positive-integer-value) | 正の整数値 |
| [NegativeIntegerValue](/api/number/negative-integer-value) | 負の整数値 |

### 小数

| クラス | 説明 |
|--------|------|
| [DecimalValue](/api/number/decimal-value) | 任意の小数値 |
| [PositiveDecimalValue](/api/number/positive-decimal-value) | 正の小数値 |
| [NegativeDecimalValue](/api/number/negative-decimal-value) | 負の小数値 |

## DateTime

| クラス | 説明 |
|--------|------|
| [LocalDate](/api/datetime/local-date) | 日付 |
| [LocalTime](/api/datetime/local-time) | 時刻 |
| [LocalDateTime](/api/datetime/local-datetime) | 日時 |
| [LocalDateRange](/api/datetime/local-date-range) | 日付範囲 |

## Collection

| クラス | 説明 |
|--------|------|
| [ArrayList](/api/collection/array-list) | 順序付きリスト |
| [Map](/api/collection/map) | キーと値のマップ |
| [Pair](/api/collection/pair) | キーと値のペア |
| [ValueObjectList](/api/collection/value-object-list) | 値オブジェクトのリスト |

## Enum

| クラス | 説明 |
|--------|------|
| [EnumValue](/api/enum/enum-value) | Enum を値オブジェクトとして扱う |

## 共通のファクトリメソッド

ほとんどの値オブジェクトは以下のファクトリメソッドを持ちます。

| メソッド | 戻り値 | 説明 |
|----------|--------|------|
| `from($value)` | `static` | 値からインスタンスを作成 |
| `tryFrom($value)` | `Result<static, ValueObjectError>` | 検証付きで作成 |
| `fromNullable($value)` | `Option<static>` | null 許容で作成 |
| `tryFromNullable($value)` | `Result<Option<static>, ValueObjectError>` | 検証 + null 許容 |

## エラー型

値オブジェクトのバリデーションエラーは `ValueObjectError` クラスで表現されます。

```php
interface IErrorValue
{
    public function getCode(): string;
    public function getMessage(): string;
    public function getDetails(): array;
}
```
