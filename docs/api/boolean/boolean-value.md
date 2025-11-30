# BooleanValue

真偽値を扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\Boolean\BooleanValue
```

## 継承関係

```
BooleanValueBase
└── BooleanValue
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`
- `IBooleanValueFactory`

## プロパティ

### value

```php
public readonly bool $value
```

真偽値を保持します。

## ファクトリメソッド

### from

```php
public static function from(bool $value): static
```

真偽値からインスタンスを作成します。

```php
$bool = BooleanValue::from(true);
```

### tryFrom

```php
public static function tryFrom(bool $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。Result 型を返します。

```php
$result = BooleanValue::tryFrom(true);
if ($result->isOk()) {
    $bool = $result->unwrap();
}
```

### fromNullable

```php
public static function fromNullable(?bool $value): Option<static>
```

null 許容でインスタンスを作成します。null の場合は None を返します。

```php
$option = BooleanValue::fromNullable(null);
$option->isNone(); // true
```

### tryFromNullable

```php
public static function tryFromNullable(?bool $value): Result<Option<static>, ValueObjectError>
```

検証付き + null 許容でインスタンスを作成します。

### true

```php
public static function true(): static
```

true のインスタンスを作成します。

```php
$bool = BooleanValue::true();
```

### false

```php
public static function false(): static
```

false のインスタンスを作成します。

```php
$bool = BooleanValue::false();
```

## インスタンスメソッド

### yes

```php
public function yes(): bool
```

値が true かどうかを返します。

```php
$bool = BooleanValue::true();
$bool->yes(); // true
```

### no

```php
public function no(): bool
```

値が false かどうかを返します。

```php
$bool = BooleanValue::false();
$bool->no(); // true
```

### not

```php
public function not(): static
```

否定値を持つ新しいインスタンスを返します。

```php
$bool = BooleanValue::true();
$negated = $bool->not(); // false
```

### and

```php
public function and(self $other): static
```

論理積 (AND) の結果を持つ新しいインスタンスを返します。

```php
$a = BooleanValue::true();
$b = BooleanValue::false();
$result = $a->and($b); // false
```

### or

```php
public function or(self $other): static
```

論理和 (OR) の結果を持つ新しいインスタンスを返します。

```php
$a = BooleanValue::true();
$b = BooleanValue::false();
$result = $a->or($b); // true
```

### xor

```php
public function xor(self $other): static
```

排他的論理和 (XOR) の結果を持つ新しいインスタンスを返します。

```php
$a = BooleanValue::true();
$b = BooleanValue::false();
$result = $a->xor($b); // true
```

### equals

```php
public function equals(IValueObject $other): bool
```

他の値オブジェクトと等価かどうかを判定します。

```php
$a = BooleanValue::true();
$b = BooleanValue::true();
$a->equals($b); // true
```

### __toString

```php
public function __toString(): string
```

文字列に変換します。true の場合は "true"、false の場合は "false" を返します。

```php
$bool = BooleanValue::true();
echo $bool; // "true"
```

### jsonSerialize

```php
public function jsonSerialize(): bool
```

JSON シリアライズ時に真偽値として出力します。

```php
$bool = BooleanValue::true();
json_encode($bool); // "true"
```

## カスタマイズ

BooleanValue を継承してカスタムクラスを作成できます。

```php
use WizDevelop\PhpValueObject\Boolean\BooleanValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '有効フラグ')]
final readonly class IsActive extends BooleanValue
{
}
```

## 関連

- [Boolean チュートリアル](/tutorial/boolean)
