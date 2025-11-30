# Ulid

ULID (Universally Unique Lexicographically Sortable Identifier) を扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\String\Ulid
```

## 継承関係

```
StringValueBase
└── Ulid
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`
- `IStringValueFactory`

## ULID の特徴

- 26 文字の固定長
- タイムスタンプ (10 文字) + ランダム (16 文字)
- 辞書順でソート可能
- Crockford's Base32 エンコーディング

## プロパティ

### value

```php
public readonly string $value
```

ULID 文字列を保持します。例: `01H34J1XAQX0VBW6G6ZK22HC1K`

## ファクトリメソッド

### from

```php
public static function from(string $value): static
```

ULID 文字列からインスタンスを作成します。

```php
$ulid = Ulid::from("01H34J1XAQX0VBW6G6ZK22HC1K");
```

### tryFrom

```php
public static function tryFrom(string $value): Result<static, ValueObjectError>
```

検証付きでインスタンスを作成します。

```php
$result = Ulid::tryFrom("01H34J1XAQX0VBW6G6ZK22HC1K");
if ($result->isOk()) {
    $ulid = $result->unwrap();
}
```

### generate

```php
public static function generate(): static
```

新しい ULID を生成します。タイムスタンプは現在時刻、ランダム部分は暗号的に安全な乱数で生成されます。

```php
$ulid = Ulid::generate();
```

### generateWithTimestamp

```php
public static function generateWithTimestamp(int $timestamp): static
```

指定されたタイムスタンプ (ミリ秒) で ULID を生成します。

```php
$timestamp = strtotime('2024-01-01 00:00:00') * 1000;
$ulid = Ulid::generateWithTimestamp($timestamp);
```

### generateMonotonic

```php
public static function generateMonotonic(int $timestamp, ?string $previousRandomBits = null): static
```

単調増加する ULID を生成します。同じミリ秒内で生成された ULID が順序付けられることを保証します。

```php
$timestamp = time() * 1000;
$previousRandom = null;

$ulid1 = Ulid::generateMonotonic($timestamp, $previousRandom);
$previousRandom = $ulid1->getRandomBits();

$ulid2 = Ulid::generateMonotonic($timestamp, $previousRandom);
// $ulid2 > $ulid1 (同じタイムスタンプでも単調増加)
```

## インスタンスメソッド

### getTimestamp

```php
public function getTimestamp(): int
```

ULID からタイムスタンプを抽出します (ミリ秒単位)。

```php
$ulid = Ulid::from("01H34J1XAQX0VBW6G6ZK22HC1K");
$timestamp = $ulid->getTimestamp(); // ミリ秒
```

### getDateTime

```php
public function getDateTime(): DateTimeImmutable
```

タイムスタンプを `DateTimeImmutable` に変換します。

```php
$ulid = Ulid::from("01H34J1XAQX0VBW6G6ZK22HC1K");
$dateTime = $ulid->getDateTime();
echo $dateTime->format('Y-m-d H:i:s');
```

### getRandomBits

```php
public function getRandomBits(): string
```

ULID のランダム部分 (16 文字) を取得します。`generateMonotonic` で使用します。

```php
$ulid = Ulid::from("01H34J1XAQX0VBW6G6ZK22HC1K");
$randomBits = $ulid->getRandomBits(); // 16文字
```

### equals

```php
public function equals(IValueObject $other): bool
```

他の ULID と等価かどうかを判定します。

### __toString

```php
public function __toString(): string
```

文字列に変換します。

### jsonSerialize

```php
public function jsonSerialize(): string
```

JSON シリアライズ時に文字列として出力します。

## バリデーション

以下の条件を満たす場合に有効な ULID です。

- 26 文字の固定長
- Crockford's Base32 文字 (0-9, A-Z、ただし I, L, O, U を除く)

```php
// 有効
Ulid::tryFrom("01H34J1XAQX0VBW6G6ZK22HC1K")->isOk(); // true

// 無効
Ulid::tryFrom("invalid")->isErr();          // true (文字数不足)
Ulid::tryFrom("00000000000000000000000000")->isOk(); // true (すべて0でも有効)
```

## 使用例

### ID として使用

```php
final readonly class UserId extends Ulid
{
}

$userId = UserId::generate();
echo $userId; // "01H34J1XAQX0VBW6G6ZK22HC1K"
```

### 生成日時の取得

```php
$ulid = Ulid::from("01H34J1XAQX0VBW6G6ZK22HC1K");
$createdAt = $ulid->getDateTime();
echo $createdAt->format('Y-m-d H:i:s.u');
```

## 関連

- [String チュートリアル](/tutorial/string)
- [StringValue](/api/string/string-value)
- [EmailAddress](/api/string/email-address)
