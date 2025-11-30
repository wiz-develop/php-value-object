# カスタム数値値オブジェクト

IntegerValue や DecimalValue を継承してカスタム数値値オブジェクトを作成する方法を解説します。

## オーバーライド可能なメソッド

### 整数値

| メソッド | 戻り値 | 説明 |
|----------|--------|------|
| `min()` | `int` | 最小値 |
| `max()` | `int` | 最大値 |
| `isValid()` | `Result` | カスタムバリデーション |

### 小数値

| メソッド | 戻り値 | 説明 |
|----------|--------|------|
| `min()` | `\BcMath\Number` | 最小値 |
| `max()` | `\BcMath\Number` | 最大値 |
| `scale()` | `int` | 小数点以下の桁数 |
| `isValid()` | `Result` | カスタムバリデーション |

## 整数値の例

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

    public function isAdult(): bool
    {
        return $this->value >= 18;
    }

    public function isMinor(): bool
    {
        return $this->value < 18;
    }

    public function isSenior(): bool
    {
        return $this->value >= 65;
    }
}
```

### 使用例

```php
$age = Age::from(25);
$age->isAdult();  // true
$age->isSenior(); // false

// 範囲外
$result = Age::tryFrom(-1);
$result->isErr(); // true

$result = Age::tryFrom(200);
$result->isErr(); // true
```

### 数量

```php
#[ValueObjectMeta(name: '数量')]
final readonly class Quantity extends PositiveIntegerValue
{
    #[Override]
    public static function max(): int
    {
        return 99999;
    }
}
```

### パーセンテージ

```php
#[ValueObjectMeta(name: 'パーセンテージ')]
final readonly class Percentage extends IntegerValue
{
    #[Override]
    public static function min(): int
    {
        return 0;
    }

    #[Override]
    public static function max(): int
    {
        return 100;
    }

    public function toDecimal(): float
    {
        return $this->value / 100;
    }

    public function format(): string
    {
        return $this->value . '%';
    }
}
```

### 評価スコア

```php
#[ValueObjectMeta(name: '評価スコア')]
final readonly class Rating extends IntegerValue
{
    #[Override]
    public static function min(): int
    {
        return 1;
    }

    #[Override]
    public static function max(): int
    {
        return 5;
    }

    public function getStars(): string
    {
        return str_repeat('★', $this->value) .
               str_repeat('☆', 5 - $this->value);
    }
}
```

## 小数値の例

### 価格

```php
use Override;
use WizDevelop\PhpValueObject\Number\Decimal\PositiveDecimalValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;
use BcMath\Number;

#[ValueObjectMeta(name: '価格')]
final readonly class Price extends PositiveDecimalValue
{
    #[Override]
    public static function scale(): int
    {
        return 2; // 小数点以下2桁
    }

    /**
     * 税込み価格を計算
     */
    public function withTax(float $taxRate = 0.1): self
    {
        $taxMultiplier = new Number(strval(1 + $taxRate));
        return self::from($this->value * $taxMultiplier);
    }

    /**
     * 割引後の価格を計算
     */
    public function withDiscount(int $discountPercent): self
    {
        $multiplier = new Number(strval(1 - $discountPercent / 100));
        return self::from($this->value * $multiplier);
    }

    /**
     * 通貨形式でフォーマット
     */
    public function format(string $locale = 'ja_JP'): string
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency((float)$this->value, 'JPY');
    }
}
```

### 使用例

```php
$price = Price::from(new Number("1000.00"));

// 税込み
$withTax = $price->withTax(); // 1100.00

// 10%割引
$discounted = $price->withDiscount(10); // 900.00

// フォーマット
echo $price->format(); // ¥1,000
```

### 緯度・経度

```php
#[ValueObjectMeta(name: '緯度')]
final readonly class Latitude extends DecimalValue
{
    #[Override]
    public static function min(): Number
    {
        return new Number("-90");
    }

    #[Override]
    public static function max(): Number
    {
        return new Number("90");
    }

    #[Override]
    public static function scale(): int
    {
        return 6;
    }
}

#[ValueObjectMeta(name: '経度')]
final readonly class Longitude extends DecimalValue
{
    #[Override]
    public static function min(): Number
    {
        return new Number("-180");
    }

    #[Override]
    public static function max(): Number
    {
        return new Number("180");
    }

    #[Override]
    public static function scale(): int
    {
        return 6;
    }
}
```

### 為替レート

```php
#[ValueObjectMeta(name: '為替レート')]
final readonly class ExchangeRate extends PositiveDecimalValue
{
    #[Override]
    public static function scale(): int
    {
        return 4;
    }

    public function convert(Price $amount): Price
    {
        return Price::from($amount->value * $this->value);
    }
}
```

## カスタム演算メソッド

値オブジェクトにドメイン固有の演算メソッドを追加できます。

### ポイント

```php
#[ValueObjectMeta(name: 'ポイント')]
final readonly class Points extends IntegerValue
{
    #[Override]
    public static function min(): int
    {
        return 0;
    }

    #[Override]
    public static function max(): int
    {
        return 999999;
    }

    /**
     * ポイントを付与
     */
    public function grant(int $amount): self
    {
        return self::from($this->value + $amount);
    }

    /**
     * ポイントを使用
     */
    public function use(int $amount): Result
    {
        if ($this->value < $amount) {
            return Result\err(ValueObjectError::number()->invalidRange(
                className: static::class,
                min: 0,
                max: $this->value,
                value: $amount,
            ));
        }

        return Result\ok(self::from($this->value - $amount));
    }

    /**
     * 円換算
     */
    public function toYen(int $rate = 1): int
    {
        return $this->value * $rate;
    }
}
```

## 関連

- [IntegerValue API](/api/number/integer-value)
- [DecimalValue API](/api/number/decimal-value)
- [カスタム文字列](/extension/custom-string)
