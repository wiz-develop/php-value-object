# PHP Value Object

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-purple.svg)](composer.json)

📦 The PHP Value Object library offers immutable, type-safe, and self-validating objects to model domain values using the Value Object pattern.

## 概要

このライブラリは、ドメイン駆動設計における値オブジェクトパターンを PHP で実装するためのツールセットを提供します。
値オブジェクトは以下の特性を持ちます：

- **不変性** - 一度作成された値オブジェクトは変更できません
- **自己検証** - 値オブジェクトは常に有効な状態を保証します
- **型安全性** - 厳格な型チェックにより、予期しない型の値が混入することを防ぎます
- **値による等価性** - 同じ値を持つオブジェクトは等価とみなされます

## インストール

Composer を使用してインストールできます：

```bash
composer require wiz-develop/php-value-object
```

## 要件

- PHP 8.4 以上

## 主な機能

### 基本型

- **Boolean** - 真偽値を扱う値オブジェクト。自己検証機能を備え、`from`メソッドによる直接作成と`tryFrom`メソッドによる検証付き作成をサポート。
- **String** - 文字列を扱う値オブジェクト。最小長・最大長の検証や正規表現による検証機能を持ち、以下の特殊タイプも提供：
  - **EmailAddress** - メールアドレスを表現する値オブジェクト
  - **Ulid** - ULIDを表現する値オブジェクト
- **Number** - 数値を扱う値オブジェクト：
  - **IntegerValue** - 整数値を表現（最小値・最大値の範囲検証あり）
  - **PositiveIntegerValue** - 正の整数を表現
  - **NegativeIntegerValue** - 負の整数を表現
  - **DecimalValue** - 少数値をBCMath\Number型で表現（高精度計算対応）
  - **PositiveDecimalValue** - 正の少数値を表現
  - **NegativeDecimalValue** - 負の少数値を表現
- **DateTime** - 日付と時刻を扱う値オブジェクト：
  - **LocalDate** - 日付のみを表現（年月日）
  - **LocalTime** - 時刻のみを表現（時分秒）
  - **LocalDateTime** - 日付と時刻を組み合わせて表現

### コレクション

- **ArrayList** - 順序付きリストコレクション。要素の追加、フィルタリング、マッピング、ソート、マージなどの操作をサポート。常に不変性を保ちながら新しいインスタンスを返す。
- **Map** - キーと値のペアを管理するマップコレクション。キーによる値の取得、追加、削除、フィルタリングなどの操作をサポート。常に不変性を保つ。
- **Pair** - キーと値のペアを表現する基本型。MapコレクションはPairの集合として実装されている。
- **ValueObjectList** - 値オブジェクトのコレクションを扱うための特別なArrayList。値オブジェクトの等価性に基づいた操作を提供。

### その他

- **Enum Value** - 列挙型の値を安全に扱うためのファクトリと基底クラス。型安全な列挙値の作成と検証をサポート。
- **Result 型** - WizDevelop\PhpMonadライブラリを活用したエラーハンドリングのための型。成功/失敗を表現し、エラーチェーンの構築を可能にする。

## 使用例

### Boolean 値の作成と検証

```php
use WizDevelop\PhpValueObject\Boolean\BooleanValue;
use WizDevelop\PhpMonad\Result;

// 直接作成 - 検証なし
$bool = BooleanValue::from(true);

// 検証付き作成 - Result型を返す
$result = BooleanValue::tryFrom(true);
if ($result->isOk()) {
    $bool = $result->unwrap();
} else {
    $error = $result->unwrapErr(); // エラー情報を取得
}

// 等価性の比較
$anotherBool = BooleanValue::from(true);
$areEqual = $bool->equals($anotherBool); // true
```

### String 値の作成と操作

```php
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\String\EmailAddress;
use WizDevelop\PhpValueObject\String\Ulid;

// 基本的な文字列
$str = StringValue::from("Hello, World!");
echo $str; // 文字列への自動変換

// メールアドレス - 検証付き
$emailResult = EmailAddress::tryFrom("example@example.com");
if ($emailResult->isOk()) {
    $email = $emailResult->unwrap();
}

// ULID
$ulid = Ulid::generate(); // 新しいULIDを生成
```

### Number 値の作成と演算

```php
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\Number\PositiveIntegerValue;
use WizDevelop\PhpValueObject\Number\DecimalValue;
use BcMath\Number;

// 整数値
$int = IntegerValue::from(42);

// 正の整数値 - 0未満の値は検証エラー
$positiveInt = PositiveIntegerValue::tryFrom(10);

// 少数値（BCMath を利用した高精度計算）
$decimal = DecimalValue::from(new Number("3.14159"));

// 算術演算 (DecimalValueの場合)
$pi = DecimalValue::from(new Number("3.14159"));
$radius = DecimalValue::from(new Number("5"));
$area = $pi->multiply($radius->square()); // πr²
```

### DateTime 値の作成と操作

```php
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalTime;
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;
use DateTimeImmutable;
use DateTimeZone;

// 日付
$date = LocalDate::of(2025, 5, 14);
$tomorrow = $date->addDays(1);

// 時刻
$time = LocalTime::of(13, 30, 0);
$laterTime = $time->addHours(2);

// 日時
$dateTime = LocalDateTime::of($date, $time);

// 現在時刻からの作成
$now = LocalDateTime::now(new DateTimeZone('Asia/Tokyo'));

// DateTimeImmutableとの相互変換
$nativeDate = $dateTime->toDateTimeImmutable();
$backToLocalDateTime = LocalDateTime::from($nativeDate);

// 日付の比較
$isBefore = $date->isBefore($tomorrow); // true
```

### コレクションの使用

```php
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Collection\Map;
use WizDevelop\PhpValueObject\Collection\Pair;
use WizDevelop\PhpValueObject\ValueObjectList;
use WizDevelop\PhpValueObject\String\StringValue;

// ArrayList - 不変のリスト
$list = ArrayList::from([1, 2, 3, 4, 5]);
$filteredList = $list->filter(fn($value) => $value > 2); // [3, 4, 5]
$mappedList = $list->map(fn($value) => $value * 2); // [2, 4, 6, 8, 10]
$sortedList = $list->sort(fn($a, $b) => $b <=> $a); // [5, 4, 3, 2, 1]
$concatList = $list->concat(ArrayList::from([6, 7, 8])); // [1, 2, 3, 4, 5, 6, 7, 8]

// Map - キーと値のペアを扱う不変のマップ
$map = Map::make(['name' => 'John', 'age' => 30]);
$hasKey = $map->has('name'); // true
$values = $map->values(); // ArrayList::from(['John', 30])
$keys = $map->keys(); // ArrayList::from(['name', 'age'])
$filteredMap = $map->filter(fn($value) => is_string($value)); // ['name' => 'John']
$updatedMap = $map->put('age', 31); // ['name' => 'John', 'age' => 31]

// Pair - キーと値のペア
$pair = Pair::of('key', 'value');
echo $pair->key; // 'key'
echo $pair->value; // 'value'

// 値オブジェクトのリスト
$stringList = ArrayList::from([
    StringValue::from('apple'),
    StringValue::from('banana'),
    StringValue::from('orange')
]);
// ValueObjectListへの変換 - 値オブジェクトの等価性に基づいた操作をサポート
$valueObjectList = new ValueObjectList($stringList->toArray());
$hasApple = $valueObjectList->has(StringValue::from('apple')); // true
```

## 拡張

既存の値オブジェクトを拡張して、独自のドメイン固有の値オブジェクトを作成できます：

```php
use Override;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '商品コード')]
final　readonly class ProductCode extends StringValue
{
    #[Override]
    final public static function minLength(): int
    {
        return 5;
    }

    #[Override]
    final public static function maxLength(): int
    {
        return 5;
    }

    #[Override]
    final protected static function regex(): string
    {
        return '/^P[0-9]{4}$/';
    }
}
```

## ライセンス

MIT ライセンスの下で公開されています。詳細は [LICENSE](LICENSE) ファイルを参照してください。
