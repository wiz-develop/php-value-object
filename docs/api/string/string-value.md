# StringValue

汎用的な文字列を扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\String\StringValue
```

## 継承関係

```
StringValueBase
└── StringValue
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`
- `IStringValueFactory`

## プロパティ

### value

```php
public readonly string $value
```

文字列値を保持します。

## オーバーライド可能な定数・メソッド

継承クラスで以下をオーバーライドしてカスタマイズできます。

| メソッド | デフォルト値 | 説明 |
|----------|--------------|------|
| `minLength()` | 0 | 最小文字数 |
| `maxLength()` | 4194303 | 最大文字数 |
| `regex()` | `/^.*$/u` | 正規表現パターン |

## ファクトリメソッド

### from

```php
public static function from(string $value): static
```

文字列からインスタンスを作成します。

```php
$str = StringValue::from("Hello, World!");
```

### tryFrom

```php
public static function tryFrom(string $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。

```php
$result = StringValue::tryFrom("Hello");
if ($result->isOk()) {
    $str = $result->unwrap();
}
```

### fromNullable

```php
public static function fromNullable(?string $value): Option<static>
```

null 許容でインスタンスを作成します。

```php
$option = StringValue::fromNullable(null);
$option->isNone(); // true
```

### tryFromNullable

```php
public static function tryFromNullable(?string $value): Result<Option<static>, ValueObjectError>
```

検証付き + null 許容でインスタンスを作成します。

## インスタンスメソッド

### equals

```php
public function equals(IValueObject $other): bool
```

他の値オブジェクトと等価かどうかを判定します。

```php
$a = StringValue::from("hello");
$b = StringValue::from("hello");
$a->equals($b); // true
```

### __toString

```php
public function __toString(): string
```

文字列に変換します。

```php
$str = StringValue::from("Hello");
echo $str; // "Hello"
```

### jsonSerialize

```php
public function jsonSerialize(): string
```

JSON シリアライズ時に文字列として出力します。

```php
$str = StringValue::from("Hello");
json_encode($str); // "\"Hello\""
```

## カスタマイズ例

### 商品コード

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
```

### 電話番号

```php
#[ValueObjectMeta(name: '電話番号')]
final readonly class PhoneNumber extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 10;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 13;
    }

    #[Override]
    protected static function regex(): string
    {
        return '/^0[0-9]{9,12}$/';
    }
}
```

## 関連

- [String チュートリアル](/tutorial/string)
- [EmailAddress](/api/string/email-address)
- [Ulid](/api/string/ulid)
