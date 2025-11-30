# Number

数値系の値オブジェクトについて解説します。

## 整数値オブジェクト

### IntegerValue

任意の整数値を扱う基本クラスです。

```php
use WizDevelop\PhpValueObject\Number\Integer\IntegerValue;

// 作成
$int = IntegerValue::from(42);

// 値の取得
$value = $int->value; // 42

// ゼロの作成
$zero = IntegerValue::zero();
```

### 状態の確認

```php
$positive = IntegerValue::from(42);
$negative = IntegerValue::from(-10);
$zero = IntegerValue::zero();

$positive->isZero();     // false
$positive->isPositive(); // true
$positive->isNegative(); // false

$negative->isPositive(); // false
$negative->isNegative(); // true
```

### PositiveIntegerValue

正の整数のみを扱う値オブジェクトです。

```php
use WizDevelop\PhpValueObject\Number\Integer\PositiveIntegerValue;

// 正の整数のみ有効
$positive = PositiveIntegerValue::from(42);

// 0 以下はエラー
$result = PositiveIntegerValue::tryFrom(0);
$result->isErr(); // true

$result = PositiveIntegerValue::tryFrom(-1);
$result->isErr(); // true
```

### NegativeIntegerValue

負の整数のみを扱う値オブジェクトです。

```php
use WizDevelop\PhpValueObject\Number\Integer\NegativeIntegerValue;

// 負の整数のみ有効
$negative = NegativeIntegerValue::from(-42);

// 0 以上はエラー
$result = NegativeIntegerValue::tryFrom(0);
$result->isErr(); // true
```

## 小数値オブジェクト

### DecimalValue

BCMath を使用した高精度な小数値を扱います。

```php
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use BcMath\Number;

// BcMath\Number から作成
$decimal = DecimalValue::from(new Number("3.14159"));

// 値の取得
$value = $decimal->value; // BcMath\Number
```

### PositiveDecimalValue / NegativeDecimalValue

```php
use WizDevelop\PhpValueObject\Number\Decimal\PositiveDecimalValue;
use WizDevelop\PhpValueObject\Number\Decimal\NegativeDecimalValue;
use BcMath\Number;

// 正の小数
$positive = PositiveDecimalValue::from(new Number("3.14"));

// 負の小数
$negative = NegativeDecimalValue::from(new Number("-2.71"));
```

## 算術演算

整数値オブジェクトと小数値オブジェクトは算術演算をサポートします。

### 加算

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(5);

// 例外を投げるバージョン
$sum = $a->add($b);
$sum->value; // 15

// Result を返すバージョン
$result = $a->tryAdd($b);
if ($result->isOk()) {
    $sum = $result->unwrap();
}
```

### 減算

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(5);

$diff = $a->sub($b);
$diff->value; // 5
```

### 乗算

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(5);

$product = $a->mul($b);
$product->value; // 50
```

### 除算

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(3);

// 整数除算 (切り捨て)
$quotient = $a->div($b);
$quotient->value; // 3

// ゼロ除算はエラー
$zero = IntegerValue::zero();
$result = $a->tryDiv($zero);
$result->isErr(); // true
```

## 比較演算

### 比較メソッド

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(5);

$a->gt($b);  // true (10 > 5)
$a->gte($b); // true (10 >= 5)
$a->lt($b);  // false (10 < 5)
$a->lte($b); // false (10 <= 5)
```

### 等価性

```php
$a = IntegerValue::from(10);
$b = IntegerValue::from(10);
$c = IntegerValue::from(5);

$a->equals($b); // true
$a->equals($c); // false
```

## カスタム数値値オブジェクトの作成

### 年齢の例

```php
use Override;
use WizDevelop\PhpValueObject\Number\Integer\IntegerValue;
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

// 使用例
$age = Age::from(25); // OK

$result = Age::tryFrom(-1);
$result->isErr(); // true (0未満)

$result = Age::tryFrom(200);
$result->isErr(); // true (150超)
```

### 価格の例

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

    public function withTax(float $taxRate = 0.1): self
    {
        $taxMultiplier = new Number(strval(1 + $taxRate));
        return self::from($this->value * $taxMultiplier);
    }
}

// 使用例
$price = Price::from(new Number("1000.00"));
$withTax = $price->withTax(); // 1100.00
```

## JSON シリアライズ

```php
$int = IntegerValue::from(42);
json_encode($int); // "42"

$decimal = DecimalValue::from(new Number("3.14"));
json_encode($decimal); // "3.14"
```

## 次のステップ

- [DateTime チュートリアル](/tutorial/datetime) - 日時値オブジェクト
- [IntegerValue API リファレンス](/api/number/integer-value) - 詳細な API
