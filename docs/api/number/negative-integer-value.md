# NegativeIntegerValue

負の整数値 (-1 以下) のみを扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\Number\NegativeIntegerValue
```

## 継承関係

```
IntegerValueBase
└── NegativeIntegerValue
```

## 制約

| 項目 | 値 |
|------|-----|
| 最小値 | `PHP_INT_MIN` |
| 最大値 | -1 |

## プロパティ

### value

```php
public readonly int $value
```

負の整数値を保持します。

## ファクトリメソッド

IntegerValue と同じファクトリメソッドを持ちます。

### from

```php
public static function from(int $value): static
```

負の整数からインスタンスを作成します。0 以上の場合は例外が発生します。

```php
$negative = NegativeIntegerValue::from(-42);
```

### tryFrom

```php
public static function tryFrom(int $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。

```php
// 有効
$result = NegativeIntegerValue::tryFrom(-42);
$result->isOk(); // true

// 無効 (0 以上)
$result = NegativeIntegerValue::tryFrom(0);
$result->isErr(); // true

$result = NegativeIntegerValue::tryFrom(1);
$result->isErr(); // true
```

## 算術演算の注意点

算術演算の結果が 0 以上になる場合はエラーになります。

```php
$a = NegativeIntegerValue::from(-5);
$b = NegativeIntegerValue::from(-10);

// 結果が正になる
$result = $a->trySub($b); // -5 - (-10) = 5
$result->isErr(); // true
```

## 関連

- [IntegerValue](/api/number/integer-value)
- [PositiveIntegerValue](/api/number/positive-integer-value)
