# シリアライゼーション

値オブジェクトのシリアライズ / デシリアライズについて解説します。

## JSON シリアライズ

すべての値オブジェクトは `JsonSerializable` を実装しています。

```php
$email = EmailAddress::from("test@example.com");
$json = json_encode($email);
// "test@example.com"

$int = IntegerValue::from(42);
$json = json_encode($int);
// 42

$bool = BooleanValue::from(true);
$json = json_encode($bool);
// true
```

### オブジェクト内での使用

```php
$user = [
    'id' => UserId::from(1),
    'email' => EmailAddress::from("test@example.com"),
    'age' => Age::from(25),
    'isActive' => BooleanValue::from(true),
];

echo json_encode($user);
// {"id":1,"email":"test@example.com","age":25,"isActive":true}
```

### コレクションの JSON 化

```php
$list = ArrayList::from(
    EmailAddress::from("a@example.com"),
    EmailAddress::from("b@example.com"),
);

echo json_encode($list);
// ["a@example.com","b@example.com"]

$map = Map::make([
    'primary' => EmailAddress::from("primary@example.com"),
    'secondary' => EmailAddress::from("secondary@example.com"),
]);

echo json_encode($map);
// {"primary":"primary@example.com","secondary":"secondary@example.com"}
```

## JSON からの復元

JSON から値オブジェクトを復元するには、明示的に `from` または `tryFrom` を呼び出します。

```php
$json = '{"email":"test@example.com","age":25}';
$data = json_decode($json, true);

$email = EmailAddress::from($data['email']);
$age = Age::from($data['age']);
```

### DTO を使用したパターン

```php
class UserDTO
{
    public function __construct(
        public EmailAddress $email,
        public Age $age,
        public Username $username,
    ) {
    }

    public static function fromArray(array $data): Result
    {
        return EmailAddress::tryFrom($data['email'] ?? '')
            ->andThen(fn($email) =>
                Age::tryFrom($data['age'] ?? 0)
                    ->map(fn($age) => compact('email', 'age'))
            )
            ->andThen(fn($result) =>
                Username::tryFrom($data['username'] ?? '')
                    ->map(fn($username) => [...$result, 'username' => $username])
            )
            ->map(fn($all) => new self(
                $all['email'],
                $all['age'],
                $all['username']
            ));
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email->value,
            'age' => $this->age->value,
            'username' => $this->username->value,
        ];
    }
}
```

## データベースとの連携

### Eloquent (Laravel) での使用

#### カスタムキャスト

```php
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use WizDevelop\PhpValueObject\String\EmailAddress;

class EmailAddressCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }
        return EmailAddress::from($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof EmailAddress) {
            return $value->value;
        }
        return $value;
    }
}
```

#### モデルでの使用

```php
class User extends Model
{
    protected $casts = [
        'email' => EmailAddressCast::class,
    ];
}

// 使用
$user = User::find(1);
$email = $user->email; // EmailAddress インスタンス

$user->email = EmailAddress::from("new@example.com");
$user->save();
```

### Doctrine での使用

#### カスタム型

```php
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use WizDevelop\PhpValueObject\String\EmailAddress;

class EmailAddressType extends Type
{
    public const NAME = 'email_address';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?EmailAddress
    {
        if ($value === null) {
            return null;
        }
        return EmailAddress::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof EmailAddress) {
            return $value->value;
        }
        return $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
```

## API レスポンスの構築

### リソースクラス

```php
class UserResource
{
    public function __construct(
        private User $user,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->user->id->value,
            'email' => $this->user->email->value,
            'name' => $this->user->name->value,
            'created_at' => $this->user->createdAt->toISOString(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
```

### コレクションリソース

```php
class UserCollectionResource
{
    public function __construct(
        private ArrayList $users,
    ) {
    }

    public function toArray(): array
    {
        return $this->users
            ->map(fn($user) => (new UserResource($user))->toArray())
            ->toArray();
    }
}
```

## フォームリクエストからの変換

### Laravel でのパターン

```php
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'age' => 'required|integer|min:0',
            'username' => 'required|string|max:50',
        ];
    }

    public function toValueObjects(): Result
    {
        return EmailAddress::tryFrom($this->input('email'))
            ->andThen(fn($email) =>
                Age::tryFrom($this->input('age'))
                    ->map(fn($age) => compact('email', 'age'))
            )
            ->andThen(fn($result) =>
                Username::tryFrom($this->input('username'))
                    ->map(fn($username) => [...$result, 'username' => $username])
            );
    }
}
```

## イベントソーシングでの使用

```php
class UserRegistered
{
    public function __construct(
        public UserId $userId,
        public EmailAddress $email,
        public Username $username,
        public LocalDateTime $registeredAt,
    ) {
    }

    public function toPayload(): array
    {
        return [
            'user_id' => $this->userId->value,
            'email' => $this->email->value,
            'username' => $this->username->value,
            'registered_at' => $this->registeredAt->toISOString(),
        ];
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            UserId::from($payload['user_id']),
            EmailAddress::from($payload['email']),
            Username::from($payload['username']),
            LocalDateTime::from(new DateTimeImmutable($payload['registered_at'])),
        );
    }
}
```

## 関連

- [Result 型との連携](/advanced/result-type)
- [バリデーション戦略](/advanced/validation)
