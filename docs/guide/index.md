# 概要

PHP Value Object は、ドメイン駆動設計における値オブジェクトパターンを PHP で実装するためのライブラリです。

## 値オブジェクトとは

値オブジェクトは、ドメイン駆動設計 (DDD) における重要な概念の 1 つです。エンティティとは異なり、識別子を持たず、その値によって等価性が判断されます。

例えば、メールアドレス、金額、日付などは値オブジェクトとして表現するのに適しています。

## このライブラリの特徴

### 不変性

一度作成された値オブジェクトは変更できません。これにより、予期しない副作用を防ぎ、コードの信頼性が向上します。

```php
$date = LocalDate::of(2025, 5, 14);
$tomorrow = $date->addDays(1); // 新しいインスタンスが返される
// $date は変更されない
```

### 自己検証

値オブジェクトは常に有効な状態を保証します。不正な値は作成時に拒否されるため、ドメインルールが確実に守られます。

```php
// メールアドレスの形式が正しくなければエラー
$result = EmailAddress::tryFrom("invalid-email");
if ($result->isErr()) {
    $error = $result->unwrapErr();
}
```

### 型安全性

厳格な型チェックにより、予期しない型の値が混入することを防ぎます。PHP 8.4 の readonly クラスを活用し、型の安全性を最大限に高めています。

### 値による等価性

同じ値を持つオブジェクトは等価とみなされます。参照ではなく値で比較します。

```php
$email1 = EmailAddress::from("test@example.com");
$email2 = EmailAddress::from("test@example.com");
$email1->equals($email2); // true
```

## 提供される値オブジェクト

### 基本型

| カテゴリ | クラス | 説明 |
|----------|--------|------|
| Boolean | `BooleanValue` | 真偽値 |
| String | `StringValue` | 文字列 |
| String | `EmailAddress` | メールアドレス |
| String | `Ulid` | ULID |
| Number | `IntegerValue` | 整数 |
| Number | `PositiveIntegerValue` | 正の整数 |
| Number | `NegativeIntegerValue` | 負の整数 |
| Number | `DecimalValue` | 小数 |
| Number | `PositiveDecimalValue` | 正の小数 |
| Number | `NegativeDecimalValue` | 負の小数 |
| DateTime | `LocalDate` | 日付 |
| DateTime | `LocalTime` | 時刻 |
| DateTime | `LocalDateTime` | 日時 |
| DateTime | `LocalDateRange` | 日付範囲 |

### コレクション

| クラス | 説明 |
|--------|------|
| `ArrayList` | 順序付きリスト |
| `Map` | キーと値のマップ |
| `Pair` | キーと値のペア |
| `ValueObjectList` | 値オブジェクトのリスト |

### Enum

| クラス | 説明 |
|--------|------|
| `EnumValue` | PHP Enum を値オブジェクトとして扱う |

## 次のステップ

- [インストール](/guide/installation) - ライブラリのインストール方法
- [コンセプト](/guide/concepts) - 値オブジェクトパターンの詳細
- [クイックスタート](/guide/quick-start) - 基本的な使い方
