# PositiveIntegerValue

正の整数値 (1 以上) のみを扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\Number\PositiveIntegerValue
```

## 継承関係

```
IntegerValueBase
└── PositiveIntegerValue
```

## 制約

| 項目 | 値 |
|------|-----|
| 最小値 | 1 |
| 最大値 | `PHP_INT_MAX` |

## プロパティ

### value

```php
public readonly int $value
```

正の整数値を保持します。

## ファクトリメソッド

IntegerValue と同じファクトリメソッドを持ちます。

### from

```php
public static function from(int $value): static
```

正の整数からインスタンスを作成します。0 以下の場合は例外が発生します。

```php
$positive = PositiveIntegerValue::from(42);
```

### tryFrom

```php
public static function tryFrom(int $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。

```php
// 有効
$result = PositiveIntegerValue::tryFrom(42);
$result->isOk(); // true

// 無効 (0 以下)
$result = PositiveIntegerValue::tryFrom(0);
$result->isErr(); // true

$result = PositiveIntegerValue::tryFrom(-1);
$result->isErr(); // true
```

## 算術演算の注意点

算術演算の結果が 0 以下になる場合はエラーになります。

```php
$a = PositiveIntegerValue::from(5);
$b = PositiveIntegerValue::from(10);

// 結果が負になる
$result = $a->trySub($b);
$result->isErr(); // true
```

## 使用例

### 数量

```php
use WizDevelop\PhpValueObject\Number\PositiveIntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '数量')]
final readonly class Quantity extends PositiveIntegerValue
{
}

$qty = Quantity::from(10);
```

## 関連

- [IntegerValue](/api/number/integer-value)
- [NegativeIntegerValue](/api/number/negative-integer-value)
