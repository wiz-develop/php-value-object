# DecimalValue

任意の小数値を扱う値オブジェクトです。BCMath を使用した高精度計算をサポートします。

## 名前空間

```php
WizDevelop\PhpValueObject\Number\DecimalValue
```

## 継承関係

```
DecimalValueBase
└── DecimalValue
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`
- `IArithmetic`
- `IComparison`
- `IDecimalValueFactory`

## プロパティ

### value

```php
public readonly \BcMath\Number $value
```

BCMath の Number オブジェクトを保持します。

## オーバーライド可能なメソッド

継承クラスで以下をオーバーライドしてカスタマイズできます。

| メソッド | デフォルト値 | 説明 |
|----------|--------------|------|
| `min()` | なし | 最小値 |
| `max()` | なし | 最大値 |
| `scale()` | なし | 小数点以下の桁数 |

## ファクトリメソッド

### from

```php
public static function from(\BcMath\Number $value): static
```

BcMath\Number からインスタンスを作成します。

```php
use BcMath\Number;

$decimal = DecimalValue::from(new Number("3.14159"));
```

### tryFrom

```php
public static function tryFrom(\BcMath\Number $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。

```php
use BcMath\Number;

$result = DecimalValue::tryFrom(new Number("3.14159"));
if ($result->isOk()) {
    $decimal = $result->unwrap();
}
```

### fromNullable

```php
public static function fromNullable(?\BcMath\Number $value): Option<static>
```

null 許容でインスタンスを作成します。

### zero

```php
public static function zero(): static
```

0 のインスタンスを作成します。

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

値が正かどうかを判定します。

### isNegative

```php
public function isNegative(): bool
```

値が負かどうかを判定します。

## 算術演算メソッド

すべての算術演算メソッドは新しいインスタンスを返します。

### add / tryAdd

```php
public function add(self $other): static
public function tryAdd(self $other): Result<static, ValueObjectError>
```

加算します。

```php
use BcMath\Number;

$a = DecimalValue::from(new Number("10.5"));
$b = DecimalValue::from(new Number("5.3"));
$sum = $a->add($b); // 15.8
```

### sub / trySub

```php
public function sub(self $other): static
public function trySub(self $other): Result<static, ValueObjectError>
```

減算します。

### mul / tryMul

```php
public function mul(self $other): static
public function tryMul(self $other): Result<static, ValueObjectError>
```

乗算します。

### div / tryDiv

```php
public function div(self $other): static
public function tryDiv(self $other): Result<static, ValueObjectError>
```

除算します。ゼロ除算はエラーになります。

## 比較メソッド

### gt / gte / lt / lte

IntegerValue と同様の比較メソッドを持ちます。

### equals

```php
public function equals(IValueObject $other): bool
```

等価かどうかを判定します。

## カスタマイズ例

### 価格

```php
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;
use BcMath\Number;

#[ValueObjectMeta(name: '価格')]
final readonly class Price extends DecimalValue
{
    #[Override]
    public static function scale(): int
    {
        return 2; // 小数点以下2桁
    }

    public function withTax(float $taxRate = 0.1): self
    {
        $taxMultiplier = new Number(strval(1 + $taxRate));
        return self::from($this->value * $taxMultiplier);
    }
}
```

## 関連

- [Number チュートリアル](/tutorial/number)
- [PositiveDecimalValue](/api/number/positive-decimal-value)
- [NegativeDecimalValue](/api/number/negative-decimal-value)
