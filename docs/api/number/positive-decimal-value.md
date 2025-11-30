# PositiveDecimalValue

正の小数値のみを扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\Number\PositiveDecimalValue
```

## 継承関係

```
DecimalValueBase
└── PositiveDecimalValue
```

## 制約

値は 0 より大きい必要があります。

## プロパティ

### value

```php
public readonly \BcMath\Number $value
```

正の小数値を保持します。

## ファクトリメソッド

DecimalValue と同じファクトリメソッドを持ちます。

### from

```php
public static function from(\BcMath\Number $value): static
```

正の小数からインスタンスを作成します。0 以下の場合は例外が発生します。

```php
use BcMath\Number;

$positive = PositiveDecimalValue::from(new Number("3.14"));
```

### tryFrom

```php
public static function tryFrom(\BcMath\Number $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。

```php
use BcMath\Number;

// 有効
$result = PositiveDecimalValue::tryFrom(new Number("3.14"));
$result->isOk(); // true

// 無効 (0 以下)
$result = PositiveDecimalValue::tryFrom(new Number("0"));
$result->isErr(); // true

$result = PositiveDecimalValue::tryFrom(new Number("-1.5"));
$result->isErr(); // true
```

## 算術演算の注意点

算術演算の結果が 0 以下になる場合はエラーになります。

## 関連

- [DecimalValue](/api/number/decimal-value)
- [NegativeDecimalValue](/api/number/negative-decimal-value)
