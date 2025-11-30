# Map

キーと値のペアを扱う不変のコレクションです。

## 名前空間

```php
WizDevelop\PhpValueObject\Collection\Map
```

## 実装インターフェース

- `ICollection`
- `ArrayAccess`
- `Countable`
- `IteratorAggregate`
- `JsonSerializable`

## ジェネリック型

```php
Map<TKey, TValue>
```

## ファクトリメソッド

### from

```php
public static function from(Pair ...$pairs): static
```

Pair から Map を作成します。

```php
$map = Map::from(
    new Pair('name', 'John'),
    new Pair('age', 30)
);
```

### make

```php
public static function make(array $items): static
```

連想配列から Map を作成します。

```php
$map = Map::make(['name' => 'John', 'age' => 30]);
```

### empty

```php
public static function empty(): static
```

空の Map を作成します。

### tryFrom

```php
public static function tryFrom(Pair ...$pairs): Result<static, ValueObjectError>
```

検証付きで Map を作成します。

### tryFromResults

```php
public static function tryFromResults(array $results): Result<static, ValueObjectError>
```

Result の配列から Map を作成します。

## 値へのアクセス

### get

```php
public function get(TKey $key): TValue
```

キーに対応する値を取得します。

```php
$map = Map::make(['name' => 'John']);
$name = $map->get('name'); // 'John'
```

### has

```php
public function has(TKey $key): bool
```

キーが存在するか判定します。

### 配列アクセス

```php
$map['name']; // 'John'
isset($map['name']); // true
```

### first / last

```php
public function first(?callable $predicate = null): Option<TValue>
public function last(?callable $predicate = null): Option<TValue>
```

最初 / 最後の要素を取得します。

### sole

```php
public function sole(?callable $predicate = null): TValue
```

条件に一致する唯一の要素を取得します。

## キーと値の取得

### keys

```php
public function keys(): ArrayList<TKey>
```

すべてのキーを ArrayList として取得します。

```php
$keys = $map->keys(); // ArrayList['name', 'age']
```

### values

```php
public function values(): ArrayList<TValue>
```

すべての値を ArrayList として取得します。

```php
$values = $map->values(); // ArrayList['John', 30]
```

## 追加・削除

すべての操作は新しいインスタンスを返します。

### put

```php
public function put(TKey $key, TValue $value): static
```

キーと値を追加または更新します。

```php
$updated = $map->put('email', 'john@example.com');
```

### putAll

```php
public function putAll(array $items): static
```

複数のキーと値を追加します。

### forget / remove

```php
public function forget(TKey $key): static
public function remove(TKey $key): static
```

キーを削除します。

## 変換操作

### map

```php
public function map(callable $callback): static
```

各値を変換します。

```php
$doubled = $map->map(fn($v) => $v * 2);
```

### mapStrict

```php
public function mapStrict(callable $callback): static
```

型を厳格にチェックしながら変換します。

### filter

```php
public function filter(callable $predicate): static
```

条件に合う要素を抽出します。

```php
$filtered = $map->filter(fn($v) => is_string($v));
```

### filterAs

```php
public function filterAs(string $className): static
```

指定したクラスのインスタンスのみを抽出します。

### reject

```php
public function reject(callable $predicate): static
```

条件に合わない要素を抽出します。

## ソート・逆順

### sort

```php
public function sort(callable $comparator): static
```

値でソートします。

### reverse

```php
public function reverse(): static
```

逆順にします。

## マージ

### merge

```php
public function merge(self $other): static
```

別の Map をマージします。同じキーがある場合は後から渡した Map の値で上書きされます。

## その他

### isEmpty

```php
public function isEmpty(): bool
```

空かどうか判定します。

### toArray

```php
public function toArray(): array
```

連想配列に変換します。

### count

```php
public function count(): int
```

要素数を取得します。

## 使用例

### 設定値の管理

```php
$config = Map::make([
    'debug' => true,
    'timeout' => 30,
    'retries' => 3
]);

if ($config->get('debug')) {
    // デバッグモード
}

$updated = $config->put('timeout', 60);
```

## 関連

- [Collection チュートリアル](/tutorial/collection)
- [ArrayList](/api/collection/array-list)
- [Pair](/api/collection/pair)
