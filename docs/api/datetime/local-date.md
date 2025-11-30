# LocalDate

日付のみを扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\DateTime\LocalDate
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`

## 範囲

| 項目 | 値 |
|------|-----|
| 最小日付 | -9999-01-01 |
| 最大日付 | 9999-12-31 |

## ファクトリメソッド

### of

```php
public static function of(int $year, int $month, int $day): static
```

年月日を指定してインスタンスを作成します。

```php
$date = LocalDate::of(2025, 5, 14);
```

### from

```php
public static function from(\DateTimeInterface $dateTime): static
```

DateTimeInterface からインスタンスを作成します。

```php
$dateTime = new DateTimeImmutable('2025-05-14');
$date = LocalDate::from($dateTime);
```

### now

```php
public static function now(\DateTimeZone $timezone): static
```

指定されたタイムゾーンの現在日付を取得します。

```php
$today = LocalDate::now(new DateTimeZone('Asia/Tokyo'));
```

### ofEpochDay

```php
public static function ofEpochDay(int $epochDay): static
```

エポック日 (1970-01-01 からの日数) からインスタンスを作成します。

```php
$date = LocalDate::ofEpochDay(19492);
```

### min / max

```php
public static function min(): static
public static function max(): static
```

最小日付 / 最大日付を取得します。

## 値の取得

### getYear / getMonth / getDay

```php
public function getYear(): int
public function getMonth(): int
public function getDay(): int
```

年 / 月 / 日を取得します。

```php
$date = LocalDate::of(2025, 5, 14);
$date->getYear();  // 2025
$date->getMonth(); // 5
$date->getDay();   // 14
```

## 日付の操作

すべての操作は新しいインスタンスを返します。

### addYears / subYears

```php
public function addYears(int $years): static
public function subYears(int $years): static
```

年を加算 / 減算します。

### addMonths / subMonths

```php
public function addMonths(int $months): static
public function subMonths(int $months): static
```

月を加算 / 減算します。月末日は自動調整されます。

```php
$jan31 = LocalDate::of(2025, 1, 31);
$feb = $jan31->addMonths(1); // 2025-02-28 (調整)
```

### addWeeks / subWeeks

```php
public function addWeeks(int $weeks): static
public function subWeeks(int $weeks): static
```

週を加算 / 減算します。

### addDays / subDays

```php
public function addDays(int $days): static
public function subDays(int $days): static
```

日を加算 / 減算します。

## 比較メソッド

### compareTo

```php
public function compareTo(self $other): int
```

比較結果を返します (-1, 0, 1)。

### isBefore / isBeforeOrEqualTo

```php
public function isBefore(self $other): bool
public function isBeforeOrEqualTo(self $other): bool
```

### isAfter / isAfterOrEqualTo

```php
public function isAfter(self $other): bool
public function isAfterOrEqualTo(self $other): bool
```

### is

```php
public function is(self $other): bool
```

同じ日付かどうかを判定します。

## 変換メソッド

### toISOString

```php
public function toISOString(): string
```

ISO 8601 形式の文字列に変換します。

```php
$date = LocalDate::of(2025, 5, 14);
$date->toISOString(); // "2025-05-14"
```

### toEpochDay

```php
public function toEpochDay(): int
```

エポック日に変換します。

### toDateTimeImmutable

```php
public function toDateTimeImmutable(): \DateTimeImmutable
```

DateTimeImmutable に変換します (時刻は 00:00:00)。

### atTime

```php
public function atTime(LocalTime $time): LocalDateTime
```

LocalTime と組み合わせて LocalDateTime を作成します。

```php
$date = LocalDate::of(2025, 5, 14);
$time = LocalTime::of(10, 30, 0);
$dateTime = $date->atTime($time);
```

## 関連

- [DateTime チュートリアル](/tutorial/datetime)
- [LocalTime](/api/datetime/local-time)
- [LocalDateTime](/api/datetime/local-datetime)
