# LocalDateRange

日付の範囲を扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\DateTime\LocalDateRange
```

## 実装インターフェース

- `IValueObject`
- `Countable`
- `IteratorAggregate`

## 範囲タイプ

| タイプ | 記法 | 説明 |
|--------|------|------|
| CLOSED | [start, end] | 両端を含む閉区間 |
| OPEN | (start, end) | 両端を含まない開区間 |
| HALF_OPEN_RIGHT | [start, end) | 開始を含み、終了を含まない |
| HALF_OPEN_LEFT | (start, end] | 開始を含まず、終了を含む |

## ファクトリメソッド

### closed

```php
public static function closed(LocalDate $start, LocalDate $end): LocalDateRangeClosed
```

閉区間[start, end]を作成します。

```php
$range = LocalDateRange::closed(
    LocalDate::of(2025, 5, 1),
    LocalDate::of(2025, 5, 31)
);
```

### open

```php
public static function open(LocalDate $start, LocalDate $end): LocalDateRangeOpen
```

開区間 (start, end) を作成します。

### halfOpenRight

```php
public static function halfOpenRight(LocalDate $start, LocalDate $end): LocalDateRangeHalfOpenRight
```

半開区間 `[start, end)` を作成します。

### halfOpenLeft

```php
public static function halfOpenLeft(LocalDate $start, LocalDate $end): LocalDateRangeHalfOpenLeft
```

半開区間 `(start, end]` を作成します。

## プロパティ

### start / end

```php
public readonly LocalDate $start
public readonly LocalDate $end
```

範囲の開始日と終了日を保持します。

## 範囲のチェック

### contains

```php
public function contains(LocalDate $date): bool
```

指定した日付が範囲内かどうかを判定します。

```php
$range = LocalDateRange::closed(
    LocalDate::of(2025, 5, 1),
    LocalDate::of(2025, 5, 31)
);

$range->contains(LocalDate::of(2025, 5, 15)); // true
$range->contains(LocalDate::of(2025, 6, 1));  // false
```

### overlaps

```php
public function overlaps(LocalDateRange $other): bool
```

他の範囲と重複するかどうかを判定します。

```php
$range1 = LocalDateRange::closed(
    LocalDate::of(2025, 5, 1),
    LocalDate::of(2025, 5, 15)
);

$range2 = LocalDateRange::closed(
    LocalDate::of(2025, 5, 10),
    LocalDate::of(2025, 5, 20)
);

$range1->overlaps($range2); // true
```

### strictlyBefore

```php
public function strictlyBefore(LocalDateRange $other): bool
```

この範囲が他の範囲より前にあり、重複がないかどうかを判定します。

## 日数の計算

### days

```php
public function days(): int
```

範囲内の日数を取得します (範囲タイプにより計算が異なります)。

```php
$range = LocalDateRange::closed(
    LocalDate::of(2025, 5, 1),
    LocalDate::of(2025, 5, 31)
);

$range->days(); // 31
```

### count

```php
public function count(): int
```

Countable インターフェースの実装。days() と同じ値を返します。

```php
count($range); // 31
```

## イテレーション

LocalDateRange は IteratorAggregate を実装しており、foreach で各日付を列挙できます。

```php
$range = LocalDateRange::closed(
    LocalDate::of(2025, 5, 1),
    LocalDate::of(2025, 5, 5)
);

foreach ($range as $date) {
    echo $date->toISOString() . "\n";
}
// 2025-05-01
// 2025-05-02
// 2025-05-03
// 2025-05-04
// 2025-05-05
```

## 使用例

### 休暇期間のチェック

```php
$vacation = LocalDateRange::closed(
    LocalDate::of(2025, 8, 10),
    LocalDate::of(2025, 8, 20)
);

$today = LocalDate::now(new DateTimeZone('Asia/Tokyo'));
if ($vacation->contains($today)) {
    echo "現在休暇中です";
}
```

### 期間の重複チェック

```php
$meeting1 = LocalDateRange::closed(
    LocalDate::of(2025, 5, 10),
    LocalDate::of(2025, 5, 12)
);

$meeting2 = LocalDateRange::closed(
    LocalDate::of(2025, 5, 11),
    LocalDate::of(2025, 5, 13)
);

if ($meeting1->overlaps($meeting2)) {
    echo "会議が重複しています";
}
```

## 関連

- [DateTime チュートリアル](/tutorial/datetime)
- [LocalDate](/api/datetime/local-date)
