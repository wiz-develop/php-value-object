# IntegerValue

任意の整数値を扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\Number\IntegerValue
```

## 継承関係

```
IntegerValueBase
└── IntegerValue
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`
- `IArithmetic`
- `IComparison`
- `IIntegerValueFactory`

## プロパティ

### value

```php
public readonly int $value
```

整数値を保持します。

## オーバーライド可能なメソッド

継承クラスで以下をオーバーライドしてカスタマイズできます。

| メソッド | デフォルト値 | 説明 |
|----------|--------------|------|
| `min()` | `PHP_INT_MIN` | 最小値 |
| `max()` | `PHP_INT_MAX` | 最大値 |

## ファクトリメソッド

### from

```php
public static function from(int $value): static
```

整数からインスタンスを作成します。

```php
$int = IntegerValue::from(42);
```

### tryFrom

```php
public static function tryFrom(int $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。

```php
$result = IntegerValue::tryFrom(42);
if ($result->isOk()) {
    $int = $result->unwrap();
}
```

### fromNullable

```php
public static function fromNullable(?int $value): Option<static>
```

null 許容でインスタンスを作成します。

### tryFromNullable

```php
public static function tryFromNullable(?int $value): Result<Option<static>, ValueObjectError>
```

検証付き + null 許容でインスタンスを作成します。

### zero

```php
public static function zero(): static
```

0 のインスタンスを作成します。

```php
$zero = IntegerValue::zero();
```

## 状態確認メソッド

### isZero

```php
public function isZero(): bool
```

値が 0 かどうかを判定します。

### isPositive

```php
public function isPositive(): bool
```

値が正 (0 より大きい) かどうかを判定します。

### isNegative

```php
public function isNegative(): bool
```

値が負 (0 より小さい) かどうかを判定します。

## 算術演算メソッド

すべての算術演算メソッドは新しいインスタンスを返します。

### add / tryAdd

```php
public function add(self $other): static
public function tryAdd(self $other): Result<static, ValueObjectError>
```

加算します。

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(5);
$sum = $a->add($b); // 15
```

### sub / trySub

```php
public function sub(self $other): static
public function trySub(self $other): Result<static, ValueObjectError>
```

減算します。

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(5);
$diff = $a->sub($b); // 5
```

### mul / tryMul

```php
public function mul(self $other): static
public function tryMul(self $other): Result<static, ValueObjectError>
```

乗算します。

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(5);
$product = $a->mul($b); // 50
```

### div / tryDiv

```php
public function div(self $other): static
public function tryDiv(self $other): Result<static, ValueObjectError>
```

除算します (整数除算、切り捨て)。ゼロ除算はエラーになります。

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(3);
$quotient = $a->div($b); // 3

// ゼロ除算
$zero = IntegerValue::zero();
$result = $a->tryDiv($zero);
$result->isErr(); // true
```

## 比較メソッド

### gt

```php
public function gt(self $other): bool
```

より大きいかどうか (>)。

### gte

```php
public function gte(self $other): bool
```

以上かどうか (>=)。

### lt

```php
public function lt(self $other): bool
```

より小さいかどうか (<)。

### lte

```php
public function lte(self $other): bool
```

以下かどうか (<=)。

### equals

```php
public function equals(IValueObject $other): bool
```

等価かどうかを判定します。

## カスタマイズ例

### 年齢

```php
use Override;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '年齢')]
final readonly class Age extends IntegerValue
{
    #[Override]
    public static function min(): int
    {
        return 0;
    }

    #[Override]
    public static function max(): int
    {
        return 150;
    }
}
```

## 関連

- [Number チュートリアル](/tutorial/number)
- [PositiveIntegerValue](/api/number/positive-integer-value)
- [NegativeIntegerValue](/api/number/negative-integer-value)
