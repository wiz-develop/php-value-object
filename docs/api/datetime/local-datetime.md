# LocalDateTime

日付と時刻を組み合わせた値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\DateTime\LocalDateTime
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`

## ファクトリメソッド

### of

```php
public static function of(LocalDate $date, LocalTime $time): static
```

LocalDate と LocalTime からインスタンスを作成します。

```php
$date = LocalDate::of(2025, 5, 14);
$time = LocalTime::of(10, 30, 0);
$dateTime = LocalDateTime::of($date, $time);
```

### from

```php
public static function from(\DateTimeInterface $dateTime): static
```

DateTimeInterface からインスタンスを作成します。

```php
$native = new DateTimeImmutable('2025-05-14 10:30:00');
$dateTime = LocalDateTime::from($native);
```

### now

```php
public static function now(\DateTimeZone $timezone): static
```

指定されたタイムゾーンの現在日時を取得します。

```php
$now = LocalDateTime::now(new DateTimeZone('Asia/Tokyo'));
```

## 値の取得

### toLocalDate

```php
public function toLocalDate(): LocalDate
```

日付部分を取得します。

### toLocalTime

```php
public function toLocalTime(): LocalTime
```

時刻部分を取得します。

```php
$dateTime = LocalDateTime::of(
    LocalDate::of(2025, 5, 14),
    LocalTime::of(10, 30, 0)
);

$date = $dateTime->toLocalDate();
$time = $dateTime->toLocalTime();
```

## 日時の操作

LocalDate と LocalTime の操作メソッドを持ちます。すべての操作は新しいインスタンスを返します。

### 日付の操作

```php
public function addYears(int $years): static
public function subYears(int $years): static
public function addMonths(int $months): static
public function subMonths(int $months): static
public function addWeeks(int $weeks): static
public function subWeeks(int $weeks): static
public function addDays(int $days): static
public function subDays(int $days): static
```

### 時刻の操作

```php
public function addHours(int $hours): static
public function subHours(int $hours): static
public function addMinutes(int $minutes): static
public function subMinutes(int $minutes): static
public function addSeconds(int $seconds): static
public function subSeconds(int $seconds): static
```

時刻の操作で日付をまたぐ場合、日付も自動的に調整されます。

```php
$dateTime = LocalDateTime::of(
    LocalDate::of(2025, 5, 14),
    LocalTime::of(23, 0, 0)
);
$later = $dateTime->addHours(2);
// 2025-05-15 01:00:00
```

## 過去・未来の判定

### isPast

```php
public function isPast(): bool
```

過去の日時かどうかを判定します。

### isFuture

```php
public function isFuture(): bool
```

未来の日時かどうかを判定します。

```php
$past = LocalDateTime::now(new DateTimeZone('Asia/Tokyo'))->subDays(1);
$past->isPast();   // true
$past->isFuture(); // false
```

## 比較メソッド

### compareTo

```php
public function compareTo(self $other): int
```

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

## 変換メソッド

### toISOString

```php
public function toISOString(): string
```

ISO 8601 形式の文字列に変換します。

```php
$dateTime->toISOString(); // "2025-05-14T10:30:00"
```

### toDateTimeImmutable

```php
public function toDateTimeImmutable(): \DateTimeImmutable
```

DateTimeImmutable に変換します。

## 関連

- [DateTime チュートリアル](/tutorial/datetime)
- [LocalDate](/api/datetime/local-date)
- [LocalTime](/api/datetime/local-time)
- [LocalDateRange](/api/datetime/local-date-range)
