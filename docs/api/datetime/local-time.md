# LocalTime

時刻のみを扱う値オブジェクトです。

## 名前空間

```php
WizDevelop\PhpValueObject\DateTime\LocalTime
```

## 実装インターフェース

- `IValueObject`
- `Stringable`
- `JsonSerializable`

## 範囲

| 項目 | 値 |
|------|-----|
| 最小時刻 | 00:00:00.000000 |
| 最大時刻 | 23:59:59.999999 |

## ファクトリメソッド

### of

```php
public static function of(int $hour, int $minute, int $second = 0, int $micro = 0): static
```

時分秒マイクロ秒を指定してインスタンスを作成します。

```php
$time = LocalTime::of(10, 30, 45);
$time = LocalTime::of(10, 30, 45, 123456); // マイクロ秒付き
```

### from

```php
public static function from(\DateTimeInterface $dateTime): static
```

DateTimeInterface から時刻部分を抽出してインスタンスを作成します。

### now

```php
public static function now(\DateTimeZone $timezone): static
```

指定されたタイムゾーンの現在時刻を取得します。

```php
$now = LocalTime::now(new DateTimeZone('Asia/Tokyo'));
```

### ofSecondOfDay

```php
public static function ofSecondOfDay(int $secondOfDay, int $micro = 0): static
```

1 日の開始からの秒数で作成します。

```php
$time = LocalTime::ofSecondOfDay(45045); // 12:30:45
```

### midnight

```php
public static function midnight(): static
```

真夜中 (00:00:00) を取得します。

### min / max

```php
public static function min(): static
public static function max(): static
```

最小時刻 / 最大時刻を取得します。

## 値の取得

### getHour / getMinute / getSecond / getMicro

```php
public function getHour(): int
public function getMinute(): int
public function getSecond(): int
public function getMicro(): int
```

時 / 分 / 秒 / マイクロ秒を取得します。

```php
$time = LocalTime::of(10, 30, 45, 123456);
$time->getHour();   // 10
$time->getMinute(); // 30
$time->getSecond(); // 45
$time->getMicro();  // 123456
```

## 時刻の操作

すべての操作は新しいインスタンスを返します。24 時間を超えると翌日に繰り越されます。

### addHours / subHours

```php
public function addHours(int $hours): static
public function subHours(int $hours): static
```

### addMinutes / subMinutes

```php
public function addMinutes(int $minutes): static
public function subMinutes(int $minutes): static
```

### addSeconds / subSeconds

```php
public function addSeconds(int $seconds): static
public function subSeconds(int $seconds): static
```

### addMicros / subMicros

```php
public function addMicros(int $micros): static
public function subMicros(int $micros): static
```

## 比較メソッド

### compareTo

```php
public function compareTo(self $other): int
```

### isBefore / isAfter

```php
public function isBefore(self $other): bool
public function isAfter(self $other): bool
```

## 変換メソッド

### toISOString

```php
public function toISOString(): string
```

ISO 8601 形式の文字列に変換します。

```php
$time = LocalTime::of(10, 30, 45);
$time->toISOString(); // "10:30:45"
```

### toSecondOfDay

```php
public function toSecondOfDay(): int
```

1 日の開始からの秒数に変換します。

## 関連

- [DateTime チュートリアル](/tutorial/datetime)
- [LocalDate](/api/datetime/local-date)
- [LocalDateTime](/api/datetime/local-datetime)
