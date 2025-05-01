# PHP Value Object

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg)](https://www.php.net/releases/8.4/en.php)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

PHPでの堅牢なドメイン駆動設計を実現するための値オブジェクトライブラリです。型安全性と不変性を重視し、ドメインの制約を明示的に表現します。

## 特徴

- 📦 **型安全性** - すべての値オブジェクトは型チェックと検証を提供
- 🔒 **不変性** - すべての値オブジェクトはイミュータブル（readonly）
- ✅ **自己検証** - 値オブジェクトは自身の妥当性を保証
- 🧮 **演算機能** - 数値型には演算や比較機能を提供
- 📚 **コレクション** - リストや連想配列のコレクションをサポート
- 🔄 **モナド** - Result型によるエラーハンドリング
- 📋 **JsonSerializable** - JSON変換のサポート

## インストール

Composerを使用してインストールできます：

```bash
composer require wiz-develop/php-value-object
```

## 要件

- PHP 8.4以上

## 基本的な使い方

### 文字列値オブジェクト

```php
use WizDevelop\PhpValueObject\String\StringValue;

// 成功例
$strResult = StringValue::tryFrom("hello");
if ($strResult->isOk()) {
    $str = $strResult->unwrap();
    echo $str; // "hello"
}

// バリデーションエラー例（文字数超過）
$invalidResult = StringValue::tryFrom(str_repeat("a", 10000000));
if ($invalidResult->isErr()) {
    $error = $invalidResult->unwrapErr();
    echo $error->message; // エラーメッセージ
}
```

### メールアドレス

```php
use WizDevelop\PhpValueObject\String\EmailAddress;

// 成功例
$emailResult = EmailAddress::tryFrom("user@example.com");
if ($emailResult->isOk()) {
    $email = $emailResult->unwrap();
    echo $email; // "user@example.com"
}

// バリデーションエラー例
$invalidResult = EmailAddress::tryFrom("invalid-email");
if ($invalidResult->isErr()) {
    $error = $invalidResult->unwrapErr();
    echo $error->message; // "無効なメールアドレスです"
}
```

### 整数値オブジェクト

```php
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\Number\PositiveIntegerValue;

// 成功例
$intResult = IntegerValue::tryFrom(42);
if ($intResult->isOk()) {
    $int = $intResult->unwrap();
    echo $int; // "42"

    // 算術演算
    $added = $int->add(IntegerValue::from(10));
    echo $added; // "52"

    $multiplied = $int->mul(IntegerValue::from(2));
    echo $multiplied; // "84"
}

// 正の整数値
$positiveResult = PositiveIntegerValue::tryFrom(42);
if ($positiveResult->isOk()) {
    $positive = $positiveResult->unwrap();
    echo $positive; // "42"
}

// バリデーションエラー例
$invalidResult = PositiveIntegerValue::tryFrom(-1);
if ($invalidResult->isErr()) {
    $error = $invalidResult->unwrapErr();
    echo $error->message; // "値は0より大きくなければなりません"
}
```

### 小数値オブジェクト

```php
use WizDevelop\PhpValueObject\Number\DecimalValue;

// 成功例
$decimalResult = DecimalValue::tryFrom(3.14);
if ($decimalResult->isOk()) {
    $decimal = $decimalResult->unwrap();
    echo $decimal; // "3.14"

    // 算術演算
    $added = $decimal->add(DecimalValue::from(2.5));
    echo $added; // "5.64"
}
```

### コレクション

#### リストコレクション

```php
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Number\IntegerValue;

// 整数値オブジェクトのコレクション
$numbers = [
    IntegerValue::from(1),
    IntegerValue::from(2),
    IntegerValue::from(3),
];

$list = ArrayList::from($numbers);

// マップ操作
$doubled = $list->map(fn ($num) => $num->mul(IntegerValue::from(2)));
// [2, 4, 6]

// フィルター操作
$filtered = $list->filter(fn ($num) => $num->value > 1);
// [2, 3]

// 要素追加
$newList = $list->add(IntegerValue::from(4));
// [1, 2, 3, 4]

// 最初と最後の要素
$first = $list->first();  // 1
$last = $list->last();    // 3
```

#### マップコレクション

```php
use WizDevelop\PhpValueObject\Collection\Map;
use WizDevelop\PhpValueObject\String\StringValue;

// 文字列値オブジェクトの連想配列
$map = Map::from([
    'name' => StringValue::from('John'),
    'email' => StringValue::from('john@example.com'),
]);

// キーと値の取得
$name = $map->get('name');  // StringValue('John')
$hasEmail = $map->has('email');  // true

// 新しいキーと値の追加
$newMap = $map->set('age', StringValue::from('30'));
```

## APIリファレンス

### 値オブジェクト基本API

すべての値オブジェクトは`IValueObject`インターフェイスを実装し、共通の基本機能を提供します：

```php
// 共通インターフェイス
public function equals(IValueObject $other): bool  // 等価性比較
public function __toString(): string               // 文字列表現
public function jsonSerialize(): mixed             // JSON変換
```****

### StringValue API

```php
// 静的ファクトリメソッド
StringValue::from(string $value): static                     // 安全な値から作成
StringValue::fromNullable(?string $value): Option<static>    // null許容
StringValue::tryFrom(string $value): Result<static, StringValueError>  // 検証付き作成
StringValue::tryFromNullable(?string $value): Result<Option<static>, StringValueError>

// オーバーライド可能なメソッド
protected static function minLength(): int    // 最小文字数（デフォルト: 1）
protected static function maxLength(): int    // 最大文字数（デフォルト: 4,194,303）
protected static function regex(): string     // 正規表現パターン（デフォルト: '/^.*$/u'）
protected static function isValid(string $value): Result  // 追加の検証ロジック
```

### EmailAddress API

```php
// 静的ファクトリメソッド
EmailAddress::from(string $value): static
EmailAddress::fromNullable(?string $value): Option<static>
EmailAddress::tryFrom(string $value): Result<static, StringValueError>
EmailAddress::tryFromNullable(?string $value): Result<Option<static>, StringValueError>

// 制約設定（オーバーライド済み）
minLength(): int  // 1
maxLength(): int  // 254
regex(): string   // 基底クラスの正規表現
```

### IntegerValue API

```php
// 静的ファクトリメソッド
IntegerValue::from(int $value): static
IntegerValue::fromNullable(?int $value): Option<static>
IntegerValue::tryFrom(int $value): Result<static, NumberValueError>
IntegerValue::tryFromNullable(?int $value): Result<Option<static>, NumberValueError>

// 算術演算
public function add(IntegerValueBase $other): static
public function tryAdd(IntegerValueBase $other): Result<static, NumberValueError>
public function sub(IntegerValueBase $other): static
public function trySub(IntegerValueBase $other): Result<static, NumberValueError>
public function mul(IntegerValueBase $other): static
public function tryMul(IntegerValueBase $other): Result<static, NumberValueError>
public function div(IntegerValueBase $other): static
public function tryDiv(IntegerValueBase $other): Result<static, NumberValueError>

// 比較
public function isZero(): bool
public function isPositive(): bool
public function isNegative(): bool
public function gt(IntegerValueBase $other): bool   // greater than
public function gte(IntegerValueBase $other): bool  // greater than or equal
public function lt(IntegerValueBase $other): bool   // less than
public function lte(IntegerValueBase $other): bool  // less than or equal

// オーバーライド可能なメソッド
protected static function min(): int  // 最小値（デフォルト: PHP_INT_MIN）
protected static function max(): int  // 最大値（デフォルト: PHP_INT_MAX）
protected static function isValid(int $value): Result  // 追加の検証ロジック
```

### DecimalValue API

```php
// 静的ファクトリメソッド
DecimalValue::from(\BcMath\Number $value): static
DecimalValue::fromNullable(?\BcMath\Number $value): Option<static>
DecimalValue::tryFrom(\BcMath\Number $value): Result<static, NumberValueError>
DecimalValue::tryFromNullable(?\BcMath\Number $value): Result<Option<static>, NumberValueError>

// 算術演算
public function add(DecimalValueBase $other): static
public function tryAdd(DecimalValueBase $other): Result<static, NumberValueError>
public function sub(DecimalValueBase $other): static
public function trySub(DecimalValueBase $other): Result<static, NumberValueError>
public function mul(DecimalValueBase $other): static
public function tryMul(DecimalValueBase $other): Result<static, NumberValueError>
public function div(DecimalValueBase $other): static
public function tryDiv(DecimalValueBase $other): Result<static, NumberValueError>

// 比較
public function isZero(): bool
public function isPositive(): bool
public function isNegative(): bool
public function gt(DecimalValueBase $other): bool   // greater than
public function gte(DecimalValueBase $other): bool  // greater than or equal
public function lt(DecimalValueBase $other): bool   // less than
public function lte(DecimalValueBase $other): bool  // less than or equal

// オーバーライド可能なメソッド
protected static function min(): \BcMath\Number  // 最小値
protected static function max(): \BcMath\Number  // 最大値
protected static function isValid(\BcMath\Number $value): Result  // 追加の検証ロジック
```

### ArrayList API

```php
// 静的ファクトリメソッド
ArrayList::from(array $elements): static
ArrayList::tryFrom(array $elements): Result<static, CollectionValueError>
ArrayList::empty(): static
ArrayList::make(iterable $items = []): static

// 要素の取得
public function first(?Closure $closure = null, $default = null): mixed
public function firstOrFail(?Closure $closure = null): mixed
public function last(?Closure $closure = null, $default = null): mixed
public function lastOrFail(?Closure $closure = null): mixed
public function sole(?Closure $closure = null): mixed

// コレクション操作
public function toArray(): array
public function slice(int $offset, ?int $length = null): static
public function reverse(): static
public function push(...$values): static
public function add($element): static
public function concat(IArrayList $other): self
public function merge(IArrayList $other): self

// 高階関数
public function map(Closure $closure): self
public function mapStrict(Closure $closure): static
public function filter(Closure $closure): static
public function reject(Closure $closure): static
public function reduce(Closure $closure, $initial = null): mixed
public function unique(?Closure $closure = null): static
public function sort(?Closure $closure = null): static

// 検索・条件チェック
public function contains($key): bool
public function every($key): bool

// インターフェース実装
public function count(): int
public function getIterator(): Generator
public function offsetExists(mixed $offset): bool
public function offsetGet(mixed $offset): mixed
```

### Map API

```php
// 静的ファクトリメソッド
Map::from(array $elements): static
Map::tryFrom(array $elements): Result<static, CollectionValueError>
Map::empty(): static
Map::make(iterable $items = []): static

// 要素の取得
public function get(string $key, $default = null): mixed
public function has(string $key): bool
public function keys(): ArrayList
public function values(): ArrayList

// コレクション操作
public function toArray(): array
public function set(string $key, $value): static
public function remove(string $key): static

// 高階関数
public function map(Closure $closure): self
public function mapStrict(Closure $closure): static
public function filter(Closure $closure): static
public function reject(Closure $closure): static
public function reduce(Closure $closure, $initial = null): mixed

// インターフェース実装
public function count(): int
public function getIterator(): Generator
public function offsetExists(mixed $offset): bool
public function offsetGet(mixed $offset): mixed
```

## カスタム値オブジェクトの作成

### 独自の文字列値オブジェクト

```php
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(displayName: 'ユーザー名', description: 'システムのユーザー名')]
final readonly class Username extends StringValue
{
    // 3～20文字の制限を設定
    protected static function minLength(): int
    {
        return 3;
    }

    protected static function maxLength(): int
    {
        return 20;
    }

    // 英数字とアンダースコアのみを許可
    protected static function regex(): string
    {
        return '/^[a-zA-Z0-9_]+$/';
    }

    // 追加のバリデーションロジック（オプション）
    protected static function isValid(string $value): Result
    {
        // 予約語チェックなど、追加のバリデーションを実装可能
        $reservedWords = ['admin', 'root', 'system'];

        if (in_array(strtolower($value), $reservedWords, true)) {
            return Result\err(StringValueError::custom(
                className: static::class,
                message: '予約語は使用できません',
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
```

### 独自の整数値オブジェクト

```php
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(displayName: '年齢', description: '人の年齢')]
final readonly class Age extends IntegerValue
{
    // 0～120歳の範囲を設定
    protected static function min(): int
    {
        return 0;
    }

    protected static function max(): int
    {
        return 120;
    }

    // 追加のバリデーションロジック（オプション）
    protected static function isValid(int $value): Result
    {
        // 例: 偶数のみ許可
        if ($value % 2 !== 0) {
            return Result\err(NumberValueError::custom(
                className: static::class,
                message: '年齢は偶数のみ許可されます',
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
```

### カスタムコレクションの作成

```php
use WizDevelop\PhpValueObject\Collection\ArrayList;

/**
 * @template T of User
 * @extends ArrayList<T>
 */
final readonly class UserCollection extends ArrayList
{
    // コレクションサイズの制約をオーバーライド
    protected static function minCount(): int
    {
        return 0;  // 空のコレクションを許可
    }

    protected static function maxCount(): int
    {
        return 100;  // 最大100ユーザーまで
    }

    // コレクション専用のメソッドを追加
    public function findByEmail(string $email): ?User
    {
        return $this->first(function (User $user) use ($email) {
            return $user->email->value === $email;
        });
    }
}
```

## エラーハンドリング

すべての`tryFrom`メソッドは`Result`型を返します。これにより、エラーハンドリングを型安全に行うことができます。

```php
use WizDevelop\PhpValueObject\String\EmailAddress;

$emailResult = EmailAddress::tryFrom('invalid-email');

// パターン1: isOk/isErrによるチェック
if ($emailResult->isOk()) {
    $email = $emailResult->unwrap();
    echo "有効なメールアドレス: {$email}";
} else {
    $error = $emailResult->unwrapErr();
    echo "エラー: {$error->message}";
}

// パターン2: matchによる網羅的なチェック
$message = $emailResult->match(
    ok: fn ($email) => "有効なメールアドレス: {$email}",
    err: fn ($error) => "エラー: {$error->message}"
);
echo $message;

// パターン3: mapによる変換
$transformedResult = $emailResult->map(
    fn ($email) => "メールアドレス {$email} は有効です"
);

// パターン4: andThenによるチェーン
$result = EmailAddress::tryFrom('user@example.com')
    ->andThen(function ($email) {
        // メールアドレスを使用した追加の処理
        return Result\ok("検証済み: {$email}");
    });
```

## ライセンス

MIT License - 詳細は [LICENSE](LICENSE) ファイルを参照してください。

## 開発情報

- PHP 8.4以上が必要
- PSR-4オートローディングを採用
- PHPUnitによるテスト
- PHPStanによる静的解析

## 作者

- [kakiuchi-shigenao](https://github.com/endou-mame)
