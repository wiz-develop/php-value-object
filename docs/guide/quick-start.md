# クイックスタート

このページでは、PHP Value Object ライブラリの基本的な使い方を紹介します。

## 基本的な使い方

### Boolean

```php
use WizDevelop\PhpValueObject\Boolean\BooleanValue;

// 作成
$bool = BooleanValue::from(true);

// 値の取得
$value = $bool->value; // true

// ファクトリメソッド
$true = BooleanValue::true();
$false = BooleanValue::false();

// 論理演算
$result = $true->and($false); // false
$result = $true->or($false);  // true
$result = $true->not();       // false
```

### String

```php
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\String\EmailAddress;

// 基本的な文字列
$str = StringValue::from("Hello, World!");
echo $str; // 文字列への自動変換

// メールアドレス (検証付き)
$result = EmailAddress::tryFrom("test@example.com");
if ($result->isOk()) {
    $email = $result->unwrap();
}
```

### Number

```php
use WizDevelop\PhpValueObject\Number\Integer\IntegerValue;
use WizDevelop\PhpValueObject\Number\Decimal\DecimalValue;
use BcMath\Number;

// 整数値
$int = IntegerValue::from(42);

// 小数値 (BCMath による高精度計算)
$decimal = DecimalValue::from(new Number("3.14159"));

// 比較
$int->isZero();     // false
$int->isPositive(); // true
$int->isNegative(); // false
```

### DateTime

```php
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalTime;
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;
use DateTimeZone;

// 日付
$date = LocalDate::of(2025, 5, 14);
$tomorrow = $date->addDays(1);

// 時刻
$time = LocalTime::of(13, 30, 0);

// 日時
$dateTime = LocalDateTime::of($date, $time);

// 現在日時
$now = LocalDateTime::now(new DateTimeZone('Asia/Tokyo'));

// 比較
$date->isBefore($tomorrow); // true
```

### Collection

```php
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Collection\Map;

// ArrayList
$list = ArrayList::from([1, 2, 3, 4, 5]);
$filtered = $list->filter(fn($v) => $v > 2);  // [3, 4, 5]
$mapped = $list->map(fn($v) => $v * 2);       // [2, 4, 6, 8, 10]

// Map
$map = Map::make(['name' => 'John', 'age' => 30]);
$name = $map->get('name'); // 'John'
$updated = $map->put('age', 31);
```

## エラーハンドリング

### Result 型の使い方

```php
use WizDevelop\PhpValueObject\String\EmailAddress;

$result = EmailAddress::tryFrom($userInput);

// パターン 1: isOk/isErr でチェック
if ($result->isOk()) {
    $email = $result->unwrap();
} else {
    $error = $result->unwrapErr();
    echo $error->getMessage();
}

// パターン 2: match で処理
$email = $result->match(
    ok: fn($email) => $email,
    err: fn($error) => throw new ValidationException($error->getMessage())
);
```

### Option 型の使い方

```php
use WizDevelop\PhpValueObject\String\EmailAddress;

$option = EmailAddress::fromNullable($maybeNull);

// パターン 1: isSome/isNone でチェック
if ($option->isSome()) {
    $email = $option->unwrap();
}

// パターン 2: デフォルト値を指定
$email = $option->unwrapOr(EmailAddress::from("default@example.com"));

// パターン 3: null を返す
$emailOrNull = $option->unwrapOrNull();
```

## 独自の値オブジェクトを作成

既存のクラスを継承して、独自の値オブジェクトを作成できます。

```php
use Override;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '商品コード')]
final readonly class ProductCode extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 5;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 5;
    }

    #[Override]
    protected static function regex(): string
    {
        return '/^P[0-9]{4}$/';
    }
}

// 使用例
$code = ProductCode::from("P1234");
$result = ProductCode::tryFrom("INVALID"); // Result::err
```

## 次のステップ

- [チュートリアル](/tutorial/) - 各値オブジェクトの詳細な使い方
- [API リファレンス](/api/) - 全クラスの API ドキュメント
- [拡張ガイド](/extension/) - 独自の値オブジェクトの作成方法
