# EnumValue

PHP の Enum を値オブジェクトとして扱うためのトレイトとインターフェースを提供します。

## 名前空間

```php
WizDevelop\PhpValueObject\Enum\IEnumValue
WizDevelop\PhpValueObject\Enum\EnumValueObjectDefault
WizDevelop\PhpValueObject\Enum\EnumValueFactory
```

## 使い方

PHP の Enum に `IEnumValue` インターフェースと `EnumValueObjectDefault` トレイトを使用します。

```php
use WizDevelop\PhpValueObject\Enum\IEnumValue;
use WizDevelop\PhpValueObject\Enum\EnumValueObjectDefault;

enum Status: string implements IEnumValue
{
    use EnumValueObjectDefault;

    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
```

## IEnumValue インターフェース

```php
interface IEnumValue extends IValueObject, IEnumValueFactory
{
}
```

IValueObject を継承しているため、`equals()` メソッドが使用できます。

## EnumValueObjectDefault トレイト

### equals

```php
public function equals(IValueObject $other): bool
```

他の Enum と等価かどうかを判定します。

```php
$status1 = Status::ACTIVE;
$status2 = Status::ACTIVE;
$status1->equals($status2); // true
```

### jsonSerialize

```php
public function jsonSerialize(): string|int
```

JSON シリアライズ時に値を出力します。

```php
$status = Status::ACTIVE;
json_encode($status); // '"active"'
```

## EnumValueFactory トレイト

Result 型を返すファクトリメソッドを提供します。

### tryFrom2

```php
public static function tryFrom2(string|int $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。PHP 標準の `tryFrom` は null を返しますが、`tryFrom2` は Result 型を返します。

```php
$result = Status::tryFrom2('active');
if ($result->isOk()) {
    $status = $result->unwrap();
} else {
    $error = $result->unwrapErr();
}
```

### fromNullable

```php
public static function fromNullable(string|int|null $value): Option<static>
```

null 許容でインスタンスを作成します。

```php
$option = Status::fromNullable(null);
$option->isNone(); // true

$option = Status::fromNullable('active');
$status = $option->unwrap(); // Status::ACTIVE
```

### tryFromNullable

```php
public static function tryFromNullable(string|int|null $value): Result<Option<static>, ValueObjectError>
```

検証付き + null 許容でインスタンスを作成します。

## 使用例

### 注文ステータス

```php
enum OrderStatus: string implements IEnumValue
{
    use EnumValueObjectDefault;

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::DRAFT => in_array($next, [self::PENDING, self::CANCELLED]),
            self::PENDING => in_array($next, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($next, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => $next === self::DELIVERED,
            self::DELIVERED, self::CANCELLED => false,
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED]);
    }
}
```

### ユーザーロール

```php
enum UserRole: string implements IEnumValue
{
    use EnumValueObjectDefault;

    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';

    public function canEdit(): bool
    {
        return in_array($this, [self::ADMIN, self::EDITOR]);
    }

    public function canDelete(): bool
    {
        return $this === self::ADMIN;
    }
}
```

### Int Backed Enum

```php
enum Priority: int implements IEnumValue
{
    use EnumValueObjectDefault;

    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
    case URGENT = 4;

    public function isHigherThan(self $other): bool
    {
        return $this->value > $other->value;
    }
}

$priority = Priority::from(3); // Priority::HIGH
```

## 関連

- [Enum チュートリアル](/tutorial/enum)
