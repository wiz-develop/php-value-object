# Boolean

BooleanValue は真偽値を扱う値オブジェクトです。

## 基本的な使い方

### インスタンスの作成

```php
use WizDevelop\PhpValueObject\Boolean\BooleanValue;

// from メソッドで作成
$bool = BooleanValue::from(true);

// ファクトリメソッドで作成
$true = BooleanValue::true();
$false = BooleanValue::false();
```

### 値の取得

```php
$bool = BooleanValue::from(true);

// value プロパティで取得
$value = $bool->value; // true

// 述語メソッド
$bool->yes(); // true の場合 true
$bool->no();  // false の場合 true
```

## 論理演算

BooleanValue は論理演算メソッドを提供します。すべての演算は新しいインスタンスを返します。

### AND 演算

```php
$true = BooleanValue::true();
$false = BooleanValue::false();

$result = $true->and($true);   // true
$result = $true->and($false);  // false
$result = $false->and($false); // false
```

### OR 演算

```php
$result = $true->or($true);   // true
$result = $true->or($false);  // true
$result = $false->or($false); // false
```

### XOR 演算

```php
$result = $true->xor($true);   // false
$result = $true->xor($false);  // true
$result = $false->xor($false); // false
```

### NOT 演算

```php
$result = $true->not();  // false
$result = $false->not(); // true
```

### 演算のチェーン

```php
$a = BooleanValue::true();
$b = BooleanValue::false();
$c = BooleanValue::true();

// (a AND b) OR c
$result = $a->and($b)->or($c); // true
```

## 等価性の比較

```php
$bool1 = BooleanValue::from(true);
$bool2 = BooleanValue::from(true);
$bool3 = BooleanValue::from(false);

$bool1->equals($bool2); // true
$bool1->equals($bool3); // false
```

## バリデーション

### tryFrom メソッド

```php
$result = BooleanValue::tryFrom(true);

if ($result->isOk()) {
    $bool = $result->unwrap();
}
```

### Nullable 対応

```php
// null の場合は None を返す
$option = BooleanValue::fromNullable(null);
$option->isNone(); // true

// 値がある場合は Some を返す
$option = BooleanValue::fromNullable(true);
$option->isSome(); // true
$bool = $option->unwrap();
```

## JSON シリアライズ

BooleanValue は `JsonSerializable` を実装しています。

```php
$bool = BooleanValue::from(true);

json_encode($bool); // "true"
```

## カスタム Boolean 値オブジェクトの作成

独自の Boolean 値オブジェクトを作成できます。

```php
use WizDevelop\PhpValueObject\Boolean\BooleanValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '有効フラグ')]
final readonly class IsActive extends BooleanValue
{
    // 必要に応じてメソッドを追加
    public function activate(): self
    {
        return self::true();
    }

    public function deactivate(): self
    {
        return self::false();
    }
}

// 使用例
$isActive = IsActive::from(true);
$deactivated = $isActive->deactivate();
```

## 次のステップ

- [String チュートリアル](/tutorial/string) - 文字列値オブジェクト
- [BooleanValue API リファレンス](/api/boolean/boolean-value) - 詳細な API
