# Enum

PHP の Enum を値オブジェクトとして扱う方法について解説します。

## 基本的な使い方

### Enum の定義

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

### インスタンスの作成

```php
// from メソッドで作成
$status = Status::from('active');

// PHP ネイティブの方法
$status = Status::ACTIVE;

// tryFrom で安全に作成
$status = Status::tryFrom('active'); // Status::ACTIVE または null
```

### Result 型を使った作成

```php
use WizDevelop\PhpValueObject\Enum\EnumValueFactory;

// Result 型を返すファクトリ
$result = Status::tryFrom2('active');

if ($result->isOk()) {
    $status = $result->unwrap();
} else {
    $error = $result->unwrapErr();
}
```

## 等価性の比較

```php
$status1 = Status::ACTIVE;
$status2 = Status::ACTIVE;
$status3 = Status::PENDING;

// equals メソッド
$status1->equals($status2); // true
$status1->equals($status3); // false

// PHP ネイティブの比較も可能
$status1 === $status2; // true
```

## JSON シリアライズ

EnumValueObjectDefault トレイトを使用すると、自動的に JsonSerializable が実装されます。

```php
$status = Status::ACTIVE;

json_encode($status); // '"active"'

// 配列内でも使用可能
$data = [
    'user' => 'John',
    'status' => Status::ACTIVE
];
json_encode($data);
// {"user":"John","status":"active"}
```

## すべてのケースの取得

```php
$cases = Status::cases();
// [Status::PENDING, Status::ACTIVE, Status::INACTIVE]
```

## Nullable 対応

```php
// null の場合は None
$option = Status::fromNullable(null);
$option->isNone(); // true

// 値がある場合は Some
$option = Status::fromNullable('active');
$status = $option->unwrap(); // Status::ACTIVE
```

## バリデーション

```php
// 有効な値
$result = Status::tryFrom2('active');
$result->isOk(); // true

// 無効な値
$result = Status::tryFrom2('invalid');
$result->isErr(); // true

$error = $result->unwrapErr();
echo $error->getMessage();
```

## 実践的な例

### 注文ステータス

```php
use WizDevelop\PhpValueObject\Enum\IEnumValue;
use WizDevelop\PhpValueObject\Enum\EnumValueObjectDefault;

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

// 使用例
$status = OrderStatus::PENDING;

if ($status->canTransitionTo(OrderStatus::CONFIRMED)) {
    $newStatus = OrderStatus::CONFIRMED;
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

    public function canView(): bool
    {
        return true; // すべてのロールで閲覧可能
    }
}

// 使用例
$role = UserRole::EDITOR;

if ($role->canEdit()) {
    // 編集処理
}
```

### 支払い方法

```php
enum PaymentMethod: string implements IEnumValue
{
    use EnumValueObjectDefault;

    case CREDIT_CARD = 'credit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH_ON_DELIVERY = 'cod';

    public function requiresConfirmation(): bool
    {
        return $this === self::BANK_TRANSFER;
    }

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'クレジットカード',
            self::BANK_TRANSFER => '銀行振込',
            self::CASH_ON_DELIVERY => '代金引換',
        };
    }
}
```

## Int Backed Enum

文字列だけでなく、整数値の Enum も使用できます。

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

// 使用例
$priority = Priority::from(3); // Priority::HIGH

$high = Priority::HIGH;
$low = Priority::LOW;
$high->isHigherThan($low); // true
```

## 次のステップ

- [API リファレンス](/api/) - 全クラスの詳細な API
- [拡張ガイド](/extension/) - 独自の値オブジェクトの作成方法
