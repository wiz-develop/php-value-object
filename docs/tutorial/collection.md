# Collection

コレクション系の値オブジェクトについて解説します。

## ArrayList

順序付きリストを扱う不変のコレクションです。

### 作成

```php
use WizDevelop\PhpValueObject\Collection\ArrayList;

// 配列から作成
$list = ArrayList::from([1, 2, 3, 4, 5]);

// 空のリスト
$empty = ArrayList::empty();

// イテラブルから作成
$list = ArrayList::make($iterable);
```

### 要素へのアクセス

```php
$list = ArrayList::from([1, 2, 3, 4, 5]);

// 配列のようにアクセス
$first = $list[0]; // 1

// 存在チェック
isset($list[0]); // true
isset($list[10]); // false

// first/last メソッド
$first = $list->first(); // Some(1)
$last = $list->last();   // Some(5)

// 条件付き first
$found = $list->first(fn($v) => $v > 3); // Some(4)

// 見つからない場合
$notFound = $list->first(fn($v) => $v > 10); // None
```

### 変換操作

すべての操作は新しいインスタンスを返します。

```php
$list = ArrayList::from([1, 2, 3, 4, 5]);

// map: 各要素を変換
$doubled = $list->map(fn($v) => $v * 2);
// [2, 4, 6, 8, 10]

// filter: 条件に合う要素を抽出
$even = $list->filter(fn($v) => $v % 2 === 0);
// [2, 4]

// reject: 条件に合わない要素を抽出
$odd = $list->reject(fn($v) => $v % 2 === 0);
// [1, 3, 5]

// flatMap: map して平坦化
$expanded = $list->flatMap(fn($v) => [$v, $v * 10]);
// [1, 10, 2, 20, 3, 30, 4, 40, 5, 50]
```

### 集約操作

```php
$list = ArrayList::from([1, 2, 3, 4, 5]);

// reduce: 畳み込み
$sum = $list->reduce(fn($carry, $v) => $carry + $v, 0);
// 15

// every: すべての要素が条件を満たすか
$allPositive = $list->every(fn($v) => $v > 0);
// true

// contains: 要素が含まれるか
$hasThree = $list->contains(3);
// true
```

### 追加・結合

```php
$list = ArrayList::from([1, 2, 3]);

// push: 末尾に追加
$pushed = $list->push(4);
// [1, 2, 3, 4]

// concat: リストを結合
$other = ArrayList::from([4, 5, 6]);
$concatenated = $list->concat($other);
// [1, 2, 3, 4, 5, 6]

// merge: マージ (concat と同様)
$merged = $list->merge($other);
```

### ソート・逆順

```php
$list = ArrayList::from([3, 1, 4, 1, 5]);

// sort: ソート
$sorted = $list->sort(fn($a, $b) => $a <=> $b);
// [1, 1, 3, 4, 5]

// 降順
$descending = $list->sort(fn($a, $b) => $b <=> $a);
// [5, 4, 3, 1, 1]

// reverse: 逆順
$reversed = $list->reverse();
// [5, 1, 4, 1, 3]
```

### グループ化

```php
$list = ArrayList::from([1, 2, 3, 4, 5, 6]);

// mapToDictionary: 辞書に変換
$dict = $list->mapToDictionary(fn($v) => [
    $v % 2 === 0 ? 'even' : 'odd' => $v
]);
// ['odd' => [1, 3, 5], 'even' => [2, 4, 6]]

// mapToGroups: グループに変換
$groups = $list->mapToGroups(fn($v) => [$v % 2 => $v]);
// Map { 0 => ArrayList[2, 4, 6], 1 => ArrayList[1, 3, 5] }
```

## Map

キーと値のペアを扱う不変のコレクションです。

### 作成

```php
use WizDevelop\PhpValueObject\Collection\Map;
use WizDevelop\PhpValueObject\Collection\Pair;

// 連想配列から作成
$map = Map::make(['name' => 'John', 'age' => 30]);

// Pair から作成
$map = Map::from(
    new Pair('name', 'John'),
    new Pair('age', 30)
);

// 空のマップ
$empty = Map::empty();
```

### 値へのアクセス

```php
$map = Map::make(['name' => 'John', 'age' => 30]);

// get: 値を取得
$name = $map->get('name'); // 'John'

// has: キーの存在チェック
$hasName = $map->has('name'); // true
$hasEmail = $map->has('email'); // false

// 配列のようにアクセス
$name = $map['name']; // 'John'
isset($map['name']); // true
```

### キーと値の取得

```php
$map = Map::make(['name' => 'John', 'age' => 30]);

// keys: キーのリスト
$keys = $map->keys(); // ArrayList['name', 'age']

// values: 値のリスト
$values = $map->values(); // ArrayList['John', 30]
```

### 追加・削除

```php
$map = Map::make(['name' => 'John']);

// put: 追加・更新
$updated = $map->put('age', 30);
// ['name' => 'John', 'age' => 30]

// putAll: 複数追加
$updated = $map->putAll(['age' => 30, 'city' => 'Tokyo']);

// forget: 削除
$removed = $map->forget('name');
// ['age' => 30] (put 後の場合)
```

### 変換操作

```php
$map = Map::make(['a' => 1, 'b' => 2, 'c' => 3]);

// map: 値を変換
$doubled = $map->map(fn($v) => $v * 2);
// ['a' => 2, 'b' => 4, 'c' => 6]

// filter: 条件に合う要素を抽出
$filtered = $map->filter(fn($v) => $v > 1);
// ['b' => 2, 'c' => 3]

// merge: マップをマージ
$other = Map::make(['d' => 4]);
$merged = $map->merge($other);
// ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]
```

## Pair

キーと値のペアを表現する基本型です。

```php
use WizDevelop\PhpValueObject\Collection\Pair;

// 作成
$pair = Pair::of('name', 'John');

// 値へのアクセス
$key = $pair->key;     // 'name'
$value = $pair->value; // 'John'

// コピー
$newPair = $pair->copy(value: 'Jane');
// Pair('name', 'Jane')

// 配列に変換
$array = $pair->toArray();
// ['name' => 'John']
```

## ValueObjectList

値オブジェクトのコレクションを扱う特別な ArrayList です。値オブジェクトの等価性に基づいた操作を提供します。

```php
use WizDevelop\PhpValueObject\ValueObjectList;
use WizDevelop\PhpValueObject\String\StringValue;

$list = new ValueObjectList([
    StringValue::from('apple'),
    StringValue::from('banana'),
    StringValue::from('orange')
]);

// 値オブジェクトの等価性で検索
$hasApple = $list->has(StringValue::from('apple'));
// true (同じ値なので見つかる)

// 削除
$removed = $list->remove(StringValue::from('banana'));

// 追加
$added = $list->put(StringValue::from('grape'));

// 差分
$other = new ValueObjectList([
    StringValue::from('apple')
]);
$diff = $list->diff($other);
// [StringValue('banana'), StringValue('orange')]
```

## Result 型との統合

コレクションは Result 型と統合されています。

```php
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\String\EmailAddress;

// 複数の Result から ArrayList を作成
$results = [
    EmailAddress::tryFrom('test1@example.com'),
    EmailAddress::tryFrom('test2@example.com'),
    EmailAddress::tryFrom('invalid'),
];

// すべて成功した場合のみ ArrayList を返す
$listResult = ArrayList::tryFromResults($results);

if ($listResult->isOk()) {
    $emails = $listResult->unwrap();
} else {
    $error = $listResult->unwrapErr();
    // 最初のエラーを取得
}
```

## 次のステップ

- [Enum チュートリアル](/tutorial/enum) - Enum 値オブジェクト
- [ArrayList API リファレンス](/api/collection/array-list) - 詳細な API
