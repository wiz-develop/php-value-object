# EmailAddress

メールアドレスを扱う値オブジェクトです。RFC 5321 に準拠して検証します。

## 名前空間

```php
WizDevelop\PhpValueObject\String\EmailAddress
```

## 継承関係

```
StringValueBase
└── EmailAddress
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

メールアドレス文字列を保持します。

## 制約

| 項目 | 値 |
|------|-----|
| 最小文字数 | 1 |
| 最大文字数 | 254 (RFC 5321) |

## ファクトリメソッド

### from

```php
public static function from(string $value): static
```

メールアドレスからインスタンスを作成します。無効なメールアドレスの場合は例外が発生します。

```php
$email = EmailAddress::from("test@example.com");
```

### tryFrom

```php
public static function tryFrom(string $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。入力値はサニタイズされます。

```php
$result = EmailAddress::tryFrom("test@example.com");
if ($result->isOk()) {
    $email = $result->unwrap();
} else {
    $error = $result->unwrapErr();
    echo $error->getMessage();
}
```

### fromNullable

```php
public static function fromNullable(?string $value): Option<static>
```

null 許容でインスタンスを作成します。

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

他のメールアドレスと等価かどうかを判定します。

```php
$a = EmailAddress::from("test@example.com");
$b = EmailAddress::from("test@example.com");
$a->equals($b); // true
```

### __toString

```php
public function __toString(): string
```

文字列に変換します。

```php
$email = EmailAddress::from("test@example.com");
echo $email; // "test@example.com"
```

### jsonSerialize

```php
public function jsonSerialize(): string
```

JSON シリアライズ時に文字列として出力します。

## バリデーション

以下の条件を満たす場合にメールアドレスとして有効です。

### 有効な例

```php
EmailAddress::tryFrom("test@example.com")->isOk();      // true
EmailAddress::tryFrom("test.name@example.com")->isOk(); // true
EmailAddress::tryFrom("test+tag@example.com")->isOk();  // true
EmailAddress::tryFrom("test@sub.example.com")->isOk();  // true
```

### 無効な例

```php
EmailAddress::tryFrom("")->isErr();                // true (空文字)
EmailAddress::tryFrom("testexample.com")->isErr(); // true (@ がない)
EmailAddress::tryFrom("test@@example.com")->isErr(); // true (@ が重複)
EmailAddress::tryFrom("@example.com")->isErr();    // true (ローカル部がない)
EmailAddress::tryFrom("test@")->isErr();           // true (ドメインがない)
```

## エラーメッセージ

無効なメールアドレスの場合、`ValueObjectError` が返されます。

```php
$result = EmailAddress::tryFrom("invalid");
if ($result->isErr()) {
    $error = $result->unwrapErr();
    echo $error->getMessage(); // "メールアドレス は有効なメールアドレスではありません: invalid"
}
```

## 関連

- [String チュートリアル](/tutorial/string)
- [StringValue](/api/string/string-value)
- [Ulid](/api/string/ulid)
