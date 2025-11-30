# DateTime

日時系の値オブジェクトについて解説します。

## LocalDate

日付のみを扱う値オブジェクトです。

### 作成

```php
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use DateTimeImmutable;
use DateTimeZone;

// 年月日を指定して作成
$date = LocalDate::of(2025, 5, 14);

// DateTimeImmutable から作成
$dateTime = new DateTimeImmutable('2025-05-14');
$date = LocalDate::from($dateTime);

// 現在日付
$today = LocalDate::now(new DateTimeZone('Asia/Tokyo'));

// エポック日から作成
$date = LocalDate::ofEpochDay(19492);

// 最小値・最大値
$min = LocalDate::min(); // -9999-01-01
$max = LocalDate::max(); // 9999-12-31
```

### 値の取得

```php
$date = LocalDate::of(2025, 5, 14);

$date->getYear();  // 2025
$date->getMonth(); // 5
$date->getDay();   // 14
```

### 日付の操作

すべての操作は新しいインスタンスを返します。

```php
$date = LocalDate::of(2025, 5, 14);

// 加算
$tomorrow = $date->addDays(1);      // 2025-05-15
$nextWeek = $date->addWeeks(1);     // 2025-05-21
$nextMonth = $date->addMonths(1);   // 2025-06-14
$nextYear = $date->addYears(1);     // 2026-05-14

// 減算
$yesterday = $date->subDays(1);     // 2025-05-13
$lastWeek = $date->subWeeks(1);     // 2025-05-07
$lastMonth = $date->subMonths(1);   // 2025-04-14
$lastYear = $date->subYears(1);     // 2024-05-14
```

### 月末日の自動調整

月を跨ぐ操作では、月末日が自動的に調整されます。

```php
$jan31 = LocalDate::of(2025, 1, 31);
$feb = $jan31->addMonths(1); // 2025-02-28 (28日に調整)

$mar31 = LocalDate::of(2025, 3, 31);
$apr = $mar31->addMonths(1); // 2025-04-30 (30日に調整)
```

### うるう年の処理

```php
$leapDay = LocalDate::of(2024, 2, 29); // うるう年
$nextYear = $leapDay->addYears(1);     // 2025-02-28 (調整)
```

### 比較

```php
$date1 = LocalDate::of(2025, 5, 14);
$date2 = LocalDate::of(2025, 5, 15);

$date1->compareTo($date2);       // -1
$date1->isBefore($date2);        // true
$date1->isBeforeOrEqualTo($date2); // true
$date1->isAfter($date2);         // false
$date1->isAfterOrEqualTo($date2);  // false
$date1->is($date1);              // true
```

### 変換

```php
$date = LocalDate::of(2025, 5, 14);

// ISO 8601 形式
$iso = $date->toISOString(); // "2025-05-14"

// エポック日
$epochDay = $date->toEpochDay(); // 20222

// DateTimeImmutable
$dateTime = $date->toDateTimeImmutable();

// 時刻と組み合わせ
$time = LocalTime::of(10, 30, 0);
$dateTime = $date->atTime($time); // LocalDateTime
```

## LocalTime

時刻のみを扱う値オブジェクトです。

### 作成

```php
use WizDevelop\PhpValueObject\DateTime\LocalTime;
use DateTimeZone;

// 時分秒を指定して作成
$time = LocalTime::of(10, 30, 45);

// マイクロ秒も指定
$time = LocalTime::of(10, 30, 45, 123456);

// 現在時刻
$now = LocalTime::now(new DateTimeZone('Asia/Tokyo'));

// 真夜中
$midnight = LocalTime::midnight(); // 00:00:00

// 秒数から作成
$time = LocalTime::ofSecondOfDay(45045); // 12:30:45

// 最小値・最大値
$min = LocalTime::min(); // 00:00:00.000000
$max = LocalTime::max(); // 23:59:59.999999
```

### 値の取得

```php
$time = LocalTime::of(10, 30, 45, 123456);

$time->getHour();   // 10
$time->getMinute(); // 30
$time->getSecond(); // 45
$time->getMicro();  // 123456
```

### 時刻の操作

```php
$time = LocalTime::of(10, 30, 0);

// 加算
$later = $time->addHours(2);    // 12:30:00
$later = $time->addMinutes(30); // 11:00:00
$later = $time->addSeconds(30); // 10:30:30

// 減算
$earlier = $time->subHours(2);    // 08:30:00
$earlier = $time->subMinutes(30); // 10:00:00
```

### 日付をまたぐ操作

時刻が 24 時間を超えると、翌日に繰り越されます。

```php
$time = LocalTime::of(23, 0, 0);
$later = $time->addHours(2); // 01:00:00 (翌日)
```

### 比較

```php
$time1 = LocalTime::of(10, 0, 0);
$time2 = LocalTime::of(11, 0, 0);

$time1->compareTo($time2); // -1
$time1->isBefore($time2);  // true
$time1->isAfter($time2);   // false
```

## LocalDateTime

日付と時刻を組み合わせた値オブジェクトです。

### 作成

```php
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalTime;
use DateTimeZone;

// LocalDate と LocalTime から作成
$date = LocalDate::of(2025, 5, 14);
$time = LocalTime::of(10, 30, 0);
$dateTime = LocalDateTime::of($date, $time);

// DateTimeImmutable から作成
$nativeDateTime = new DateTimeImmutable('2025-05-14 10:30:00');
$dateTime = LocalDateTime::from($nativeDateTime);

// 現在日時
$now = LocalDateTime::now(new DateTimeZone('Asia/Tokyo'));
```

### 値の取得

```php
$dateTime = LocalDateTime::of(
    LocalDate::of(2025, 5, 14),
    LocalTime::of(10, 30, 0)
);

// 日付部分
$date = $dateTime->toLocalDate();
$date->getYear();  // 2025
$date->getMonth(); // 5
$date->getDay();   // 14

// 時刻部分
$time = $dateTime->toLocalTime();
$time->getHour();   // 10
$time->getMinute(); // 30
```

### 操作

LocalDateTime は日付と時刻の両方の操作が可能です。

```php
$dateTime = LocalDateTime::of(
    LocalDate::of(2025, 5, 14),
    LocalTime::of(10, 30, 0)
);

// 日付の操作
$tomorrow = $dateTime->addDays(1);
$nextMonth = $dateTime->addMonths(1);

// 時刻の操作
$later = $dateTime->addHours(2);
$later = $dateTime->addMinutes(30);
```

### 過去・未来の判定

```php
$dateTime = LocalDateTime::now(new DateTimeZone('Asia/Tokyo'));

$past = $dateTime->subDays(1);
$past->isPast();   // true
$past->isFuture(); // false

$future = $dateTime->addDays(1);
$future->isPast();   // false
$future->isFuture(); // true
```

## LocalDateRange

日付の範囲を扱う値オブジェクトです。

### 作成

```php
use WizDevelop\PhpValueObject\DateTime\LocalDateRange;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\RangeType;

$start = LocalDate::of(2025, 5, 1);
$end = LocalDate::of(2025, 5, 31);

// 閉区間 [start, end]
$range = LocalDateRange::closed($start, $end);

// 開区間 (start, end)
$range = LocalDateRange::open($start, $end);

// 半開区間 [start, end)
$range = LocalDateRange::halfOpenRight($start, $end);

// 半開区間 (start, end]
$range = LocalDateRange::halfOpenLeft($start, $end);
```

### 範囲のチェック

```php
$range = LocalDateRange::closed(
    LocalDate::of(2025, 5, 1),
    LocalDate::of(2025, 5, 31)
);

$date = LocalDate::of(2025, 5, 15);
$range->contains($date); // true

$outside = LocalDate::of(2025, 6, 1);
$range->contains($outside); // false
```

### 範囲の重複チェック

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

### 日数の計算

```php
$range = LocalDateRange::closed(
    LocalDate::of(2025, 5, 1),
    LocalDate::of(2025, 5, 31)
);

$days = $range->days(); // 31
$count = $range->count(); // 31
```

### イテレーション

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

## 次のステップ

- [Collection チュートリアル](/tutorial/collection) - コレクション値オブジェクト
- [LocalDate API リファレンス](/api/datetime/local-date) - 詳細な API
