# Pair

キーと値のペアを表現する値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\Collection\Pair
```

## ジェネリック型

```php
Pair<TKey, TValue>
```

## プロパティ

### key

```php
public readonly TKey $key
```

キーを保持します。

### value

```php
public readonly TValue $value
```

値を保持します。

## ファクトリメソッド

### of

```php
public static function of(TKey $key, TValue $value): static
```

キーと値からインスタンスを作成します。

```php
$pair = Pair::of('name', 'John');
```

### コンストラクタ

```php
public function __construct(TKey $key, TValue $value)
```

コンストラクタでも作成できます。

```php
$pair = new Pair('name', 'John');
```

## インスタンスメソッド

### copy

```php
public function copy(?TKey $key = null, ?TValue $value = null): static
```

一部を変更した新しいインスタンスを作成します。

```php
$pair = Pair::of('name', 'John');
$newPair = $pair->copy(value: 'Jane');
// Pair('name', 'Jane')
```

### toArray

```php
public function toArray(): array
```

連想配列に変換します。

```php
$pair = Pair::of('name', 'John');
$array = $pair->toArray();
// ['name' => 'John']
```

## 使用例

### Map との組み合わせ

```php
$map = Map::from(
    Pair::of('name', 'John'),
    Pair::of('age', 30),
    Pair::of('city', 'Tokyo')
);
```

### データ変換

```php
$pairs = [
    Pair::of('a', 1),
    Pair::of('b', 2),
    Pair::of('c', 3),
];

$array = array_merge(...array_map(
    fn($pair) => $pair->toArray(),
    $pairs
));
// ['a' => 1, 'b' => 2, 'c' => 3]
```

## 関連

- [Collection チュートリアル](/tutorial/collection)
- [Map](/api/collection/map)
