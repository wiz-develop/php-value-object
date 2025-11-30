# ArrayList

順序付きリストを扱う不変のコレクションです。

## 名前空間

```php
WizDevelop\PhpValueObject\Collection\ArrayList
```

## 実装インターフェース

- `ICollection`
- `ArrayAccess`
- `Countable`
- `IteratorAggregate`
- `JsonSerializable`

## ジェネリック型

```php
ArrayList<TValue>
```

## ファクトリメソッド

### from

```php
public static function from(TValue ...$items): static
```

可変長引数からリストを作成します。

```php
$list = ArrayList::from(1, 2, 3, 4, 5);
```

### make

```php
public static function make(iterable $items): static
```

イテラブルからリストを作成します。

```php
$list = ArrayList::make([1, 2, 3, 4, 5]);
```

### empty

```php
public static function empty(): static
```

空のリストを作成します。

### tryFrom

```php
public static function tryFrom(mixed ...$items): Result<static, ValueObjectError>
```

検証付きでリストを作成します。

### tryFromResults

```php
public static function tryFromResults(array $results): Result<static, ValueObjectError>
```

Result の配列からリストを作成します。すべて成功の場合のみリストを返します。

```php
$results = [
    EmailAddress::tryFrom('a@example.com'),
    EmailAddress::tryFrom('b@example.com'),
];
$listResult = ArrayList::tryFromResults($results);
```

## 要素へのアクセス

### 配列アクセス

```php
$list = ArrayList::from(1, 2, 3);
$list[0]; // 1
isset($list[0]); // true
```

### first

```php
public function first(?callable $predicate = null): Option<TValue>
```

最初の要素を取得します。条件を指定可能です。

```php
$list->first();                    // Some(1)
$list->first(fn($v) => $v > 2);    // Some(3)
```

### last

```php
public function last(?callable $predicate = null): Option<TValue>
```

最後の要素を取得します。

### sole

```php
public function sole(?callable $predicate = null): TValue
```

条件に一致する唯一の要素を取得します。複数ある場合は例外。

## 変換操作

すべての操作は新しいインスタンスを返します。

### map

```php
public function map(callable $callback): static
```

各要素を変換します。

```php
$doubled = $list->map(fn($v) => $v * 2);
```

### mapStrict

```php
public function mapStrict(callable $callback): static
```

型を厳格にチェックしながら変換します。

### flatMap

```php
public function flatMap(callable $callback): static
```

各要素を変換して平坦化します。

### flatten

```php
public function flatten(): static
```

ネストされた配列を平坦化します。

### filter

```php
public function filter(callable $predicate): static
```

条件に合う要素を抽出します。

```php
$even = $list->filter(fn($v) => $v % 2 === 0);
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

### values

```php
public function values(): static
```

キーを 0 から連番に振り直します。

## 追加・結合

### push / add

```php
public function push(TValue $item): static
public function add(TValue $item): static
```

末尾に要素を追加します。

### concat

```php
public function concat(self $other): static
```

別のリストを結合します。

### merge

```php
public function merge(self $other): static
```

別のリストをマージします。

## ソート・逆順

### sort

```php
public function sort(callable $comparator): static
```

ソートします。

```php
$sorted = $list->sort(fn($a, $b) => $a <=> $b);
```

### reverse

```php
public function reverse(): static
```

逆順にします。

### unique

```php
public function unique(?callable $callback = null): static
```

重複を除去します。

## 集約

### reduce

```php
public function reduce(callable $callback, mixed $initial): mixed
```

畳み込み演算します。

```php
$sum = $list->reduce(fn($carry, $v) => $carry + $v, 0);
```

### every

```php
public function every(callable $predicate): bool
```

すべての要素が条件を満たすか判定します。

### contains

```php
public function contains(mixed $value): bool
```

要素が含まれるか判定します。

## グループ化

### mapToDictionary

```php
public function mapToDictionary(callable $callback): array
```

辞書形式に変換します。

### mapToGroups

```php
public function mapToGroups(callable $callback): Map
```

グループ化して Map として返します。

## その他

### slice

```php
public function slice(int $offset, ?int $length = null): static
```

一部を切り出します。

### isEmpty

```php
public function isEmpty(): bool
```

空かどうか判定します。

### toArray

```php
public function toArray(): array
```

配列に変換します。

### count

```php
public function count(): int
```

要素数を取得します。

## 関連

- [Collection チュートリアル](/tutorial/collection)
- [Map](/api/collection/map)
- [ValueObjectList](/api/collection/value-object-list)
