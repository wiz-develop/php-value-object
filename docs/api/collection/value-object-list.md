# ValueObjectList

値オブジェクトのコレクションを扱う特別な ArrayList です。値オブジェクトの等価性に基づいた操作を提供します。

## 名前空間

```php
WizDevelop\PhpValueObject\ValueObjectList
```

## 継承関係

```
ArrayList
└── ValueObjectList
```

## ジェネリック型

```php
ValueObjectList<TValue extends IValueObject>
```

## ArrayList との違い

ValueObjectList は値オブジェクトの `equals()` メソッドを使用して等価性を判定します。これにより、同じ値を持つ別のインスタンスも同一とみなされます。

```php
$email1 = EmailAddress::from('test@example.com');
$email2 = EmailAddress::from('test@example.com');

// 別インスタンスだが、値は同じ
$email1 === $email2; // false
$email1->equals($email2); // true

// ValueObjectList では equals で比較
$list = new ValueObjectList([$email1]);
$list->has($email2); // true (equals で判定)
```

## コンストラクタ

```php
public function __construct(array $items = [])
```

値オブジェクトの配列からリストを作成します。

```php
$list = new ValueObjectList([
    StringValue::from('apple'),
    StringValue::from('banana'),
    StringValue::from('orange')
]);
```

## インスタンスメソッド

### has

```php
public function has(IValueObject $item): bool
```

値オブジェクトがリストに含まれるか判定します。`equals()` メソッドで比較します。

```php
$apple = StringValue::from('apple');
$list->has($apple); // true

$grape = StringValue::from('grape');
$list->has($grape); // false
```

### remove

```php
public function remove(IValueObject $item): static
```

値オブジェクトをリストから削除します。

```php
$banana = StringValue::from('banana');
$newList = $list->remove($banana);
// [StringValue('apple'), StringValue('orange')]
```

### put

```php
public function put(IValueObject $item): static
```

値オブジェクトをリストに追加します。すでに存在する場合は追加しません。

```php
$apple = StringValue::from('apple');
$newList = $list->put($apple);
// すでに存在するので変化なし
```

### diff

```php
public function diff(self $other): static
```

他のリストとの差分を取得します。

```php
$list1 = new ValueObjectList([
    StringValue::from('apple'),
    StringValue::from('banana'),
    StringValue::from('orange')
]);

$list2 = new ValueObjectList([
    StringValue::from('apple')
]);

$diff = $list1->diff($list2);
// [StringValue('banana'), StringValue('orange')]
```

### equals

```php
public function equals(IValueObject $other): bool
```

他のリストと等価かどうかを判定します。要素の順序と値が一致する場合に true。

## 使用例

### ユニークなコレクション

```php
$emails = new ValueObjectList([
    EmailAddress::from('a@example.com'),
    EmailAddress::from('b@example.com'),
]);

// 重複を追加しようとしても追加されない
$emails = $emails->put(EmailAddress::from('a@example.com'));
$emails->count(); // 2

// 新しいメールアドレスは追加される
$emails = $emails->put(EmailAddress::from('c@example.com'));
$emails->count(); // 3
```

### 差分の計算

```php
$oldTags = new ValueObjectList([
    StringValue::from('php'),
    StringValue::from('laravel'),
    StringValue::from('mysql'),
]);

$newTags = new ValueObjectList([
    StringValue::from('php'),
    StringValue::from('react'),
]);

// 削除されたタグ
$removed = $oldTags->diff($newTags);
// [StringValue('laravel'), StringValue('mysql')]

// 追加されたタグ
$added = $newTags->diff($oldTags);
// [StringValue('react')]
```

## 関連

- [Collection チュートリアル](/tutorial/collection)
- [ArrayList](/api/collection/array-list)
