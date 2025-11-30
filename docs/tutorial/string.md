# String

文字列系の値オブジェクトについて解説します。

## StringValue

汎用的な文字列値オブジェクトです。

### 基本的な使い方

```php
use WizDevelop\PhpValueObject\String\StringValue;

// 作成
$str = StringValue::from("Hello, World!");

// 値の取得
$value = $str->value; // "Hello, World!"

// 文字列への自動変換
echo $str; // "Hello, World!"
```

### バリデーション

```php
$result = StringValue::tryFrom("Hello");

if ($result->isOk()) {
    $str = $result->unwrap();
} else {
    $error = $result->unwrapErr();
    echo $error->getMessage();
}
```

### Nullable 対応

```php
// null の場合は None
$option = StringValue::fromNullable(null);
$option->isNone(); // true

// 値がある場合は Some
$option = StringValue::fromNullable("Hello");
$str = $option->unwrap();
```

## EmailAddress

メールアドレスを扱う値オブジェクトです。RFC 5321 に準拠して検証します。

### 基本的な使い方

```php
use WizDevelop\PhpValueObject\String\EmailAddress;

// 有効なメールアドレス
$email = EmailAddress::from("test@example.com");
$value = $email->value; // "test@example.com"
```

### バリデーション

```php
// 有効なメールアドレス
$result = EmailAddress::tryFrom("test@example.com");
$result->isOk(); // true

// 無効なメールアドレス
$result = EmailAddress::tryFrom("invalid-email");
$result->isErr(); // true

$error = $result->unwrapErr();
echo $error->getMessage(); // エラーメッセージ
```

### 有効なメールアドレスの例

```php
// すべて有効
EmailAddress::tryFrom("test@example.com")->isOk();      // true
EmailAddress::tryFrom("test.name@example.com")->isOk(); // true
EmailAddress::tryFrom("test+tag@example.com")->isOk();  // true
EmailAddress::tryFrom("test@sub.example.com")->isOk();  // true
```

### 無効なメールアドレスの例

```php
// すべて無効
EmailAddress::tryFrom("")->isErr();              // true (空文字)
EmailAddress::tryFrom("testexample.com")->isErr(); // true (@ がない)
EmailAddress::tryFrom("test@@example.com")->isErr(); // true (@ が重複)
EmailAddress::tryFrom("@example.com")->isErr();    // true (ローカル部がない)
EmailAddress::tryFrom("test@")->isErr();           // true (ドメインがない)
```

## Ulid

ULID (Universally Unique Lexicographically Sortable Identifier) を扱う値オブジェクトです。

### ULID の生成

```php
use WizDevelop\PhpValueObject\String\Ulid;

// 新しい ULID を生成
$ulid = Ulid::generate();
$value = $ulid->value; // 例: "01H34J1XAQX0VBW6G6ZK22HC1K"
```

### タイムスタンプを指定して生成

```php
// 特定のタイムスタンプで生成
$timestamp = strtotime('2024-01-01 00:00:00') * 1000;
$ulid = Ulid::generateWithTimestamp($timestamp);
```

### 単調増加 ULID の生成

同じミリ秒内で生成された ULID が単調増加することを保証します。

```php
$timestamp = time() * 1000;
$previousRandom = null;

$ulid1 = Ulid::generateMonotonic($timestamp, $previousRandom);
$previousRandom = $ulid1->getRandomBits();

$ulid2 = Ulid::generateMonotonic($timestamp, $previousRandom);
// $ulid2 > $ulid1 (同じタイムスタンプでも単調増加)
```

### ULID からの情報取得

```php
$ulid = Ulid::from("01H34J1XAQX0VBW6G6ZK22HC1K");

// タイムスタンプを取得 (ミリ秒)
$timestamp = $ulid->getTimestamp();

// DateTimeImmutable として取得
$dateTime = $ulid->getDateTime();

// ランダム部分を取得
$randomBits = $ulid->getRandomBits();
```

### バリデーション

```php
// 有効な ULID
$result = Ulid::tryFrom("01H34J1XAQX0VBW6G6ZK22HC1K");
$result->isOk(); // true

// 無効な ULID
$result = Ulid::tryFrom("invalid");
$result->isErr(); // true
```

## カスタム文字列値オブジェクトの作成

独自の文字列値オブジェクトを作成できます。

### 基本的なカスタマイズ

```php
use Override;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '商品コード')]
final readonly class ProductCode extends StringValue
{
    // 最小文字数
    #[Override]
    public static function minLength(): int
    {
        return 5;
    }

    // 最大文字数
    #[Override]
    public static function maxLength(): int
    {
        return 5;
    }

    // 正規表現パターン
    #[Override]
    protected static function regex(): string
    {
        return '/^P[0-9]{4}$/';
    }
}

// 使用例
$code = ProductCode::from("P1234"); // OK
$result = ProductCode::tryFrom("INVALID"); // Err
```

### 電話番号の例

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

## 次のステップ

- [Number チュートリアル](/tutorial/number) - 数値値オブジェクト
- [StringValue API リファレンス](/api/string/string-value) - 詳細な API
