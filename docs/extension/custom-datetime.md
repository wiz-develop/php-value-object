# カスタム日時値オブジェクト

LocalDate、LocalTime、LocalDateTime を継承または組み合わせてカスタム日時値オブジェクトを作成する方法を解説します。

## オーバーライド可能なメソッド

### LocalDate

| メソッド | 戻り値 | 説明 |
|----------|--------|------|
| `min()` | `LocalDate` | 最小日付 |
| `max()` | `LocalDate` | 最大日付 |

## 日付の拡張例

### 生年月日

```php
use Override;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\ValueObjectMeta;
use DateTimeZone;

#[ValueObjectMeta(name: '生年月日')]
final readonly class BirthDate extends LocalDate
{
    #[Override]
    public static function min(): LocalDate
    {
        // 120年前まで
        return LocalDate::now(new DateTimeZone('UTC'))->subYears(120);
    }

    #[Override]
    public static function max(): LocalDate
    {
        // 今日まで
        return LocalDate::now(new DateTimeZone('UTC'));
    }

    /**
     * 年齢を計算
     */
    public function getAge(DateTimeZone $timezone): int
    {
        $today = LocalDate::now($timezone);
        $age = $today->getYear() - $this->getYear();

        // 誕生日がまだ来ていない場合
        if ($today->getMonth() < $this->getMonth() ||
            ($today->getMonth() === $this->getMonth() &&
             $today->getDay() < $this->getDay())) {
            $age--;
        }

        return $age;
    }

    /**
     * 成人かどうか
     */
    public function isAdult(DateTimeZone $timezone): bool
    {
        return $this->getAge($timezone) >= 18;
    }

    /**
     * 今年の誕生日
     */
    public function thisBirthday(DateTimeZone $timezone): LocalDate
    {
        $today = LocalDate::now($timezone);
        return LocalDate::of($today->getYear(), $this->getMonth(), $this->getDay());
    }
}
```

### 使用例

```php
$birthDate = BirthDate::of(1990, 5, 15);

$age = $birthDate->getAge(new DateTimeZone('Asia/Tokyo'));
echo "年齢: {$age}歳";

if ($birthDate->isAdult(new DateTimeZone('Asia/Tokyo'))) {
    echo "成人です";
}
```

### 有効期限

```php
#[ValueObjectMeta(name: '有効期限')]
final readonly class ExpirationDate extends LocalDate
{
    #[Override]
    public static function min(): LocalDate
    {
        // 今日以降
        return LocalDate::now(new DateTimeZone('UTC'));
    }

    /**
     * 期限切れかどうか
     */
    public function isExpired(DateTimeZone $timezone): bool
    {
        $today = LocalDate::now($timezone);
        return $this->isBefore($today);
    }

    /**
     * 残り日数
     */
    public function daysRemaining(DateTimeZone $timezone): int
    {
        $today = LocalDate::now($timezone);
        return $this->toEpochDay() - $today->toEpochDay();
    }

    /**
     * 期限が近いかどうか (30日以内)
     */
    public function isNearExpiration(DateTimeZone $timezone, int $days = 30): bool
    {
        return $this->daysRemaining($timezone) <= $days;
    }
}
```

## 日時の組み合わせ

### 営業時間

```php
use WizDevelop\PhpValueObject\DateTime\LocalTime;

#[ValueObjectMeta(name: '営業時間')]
final readonly class BusinessHours
{
    public function __construct(
        public LocalTime $openTime,
        public LocalTime $closeTime,
    ) {
        // 開店時刻は閉店時刻より前であること
        assert($openTime->isBefore($closeTime));
    }

    public static function of(
        int $openHour,
        int $openMinute,
        int $closeHour,
        int $closeMinute
    ): self {
        return new self(
            LocalTime::of($openHour, $openMinute),
            LocalTime::of($closeHour, $closeMinute),
        );
    }

    /**
     * 現在営業中かどうか
     */
    public function isOpen(DateTimeZone $timezone): bool
    {
        $now = LocalTime::now($timezone);
        return !$now->isBefore($this->openTime) &&
               $now->isBefore($this->closeTime);
    }

    /**
     * 指定時刻が営業時間内かどうか
     */
    public function isWithinHours(LocalTime $time): bool
    {
        return !$time->isBefore($this->openTime) &&
               $time->isBefore($this->closeTime);
    }
}
```

### 予約日時

```php
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;

#[ValueObjectMeta(name: '予約日時')]
final readonly class ReservationDateTime extends LocalDateTime
{
    /**
     * 予約可能な最小日時 (1時間後から)
     */
    public static function minReservableTime(DateTimeZone $timezone): LocalDateTime
    {
        return LocalDateTime::now($timezone)->addHours(1);
    }

    /**
     * 予約可能な最大日時 (3ヶ月後まで)
     */
    public static function maxReservableTime(DateTimeZone $timezone): LocalDateTime
    {
        return LocalDateTime::now($timezone)->addMonths(3);
    }

    /**
     * 予約可能かどうか検証
     */
    public static function validate(
        LocalDateTime $dateTime,
        DateTimeZone $timezone
    ): Result {
        $min = self::minReservableTime($timezone);
        $max = self::maxReservableTime($timezone);

        if ($dateTime->isBefore($min)) {
            return Result\err(ValueObjectError::dateTime()->invalidRange(
                className: static::class,
                message: '予約は1時間後以降を指定してください',
            ));
        }

        if ($dateTime->isAfter($max)) {
            return Result\err(ValueObjectError::dateTime()->invalidRange(
                className: static::class,
                message: '予約は3ヶ月以内を指定してください',
            ));
        }

        return Result\ok(true);
    }
}
```

## 日付範囲の拡張

### 契約期間

```php
use WizDevelop\PhpValueObject\DateTime\LocalDateRange;

#[ValueObjectMeta(name: '契約期間')]
final readonly class ContractPeriod
{
    public function __construct(
        public LocalDateRange $range,
    ) {
    }

    public static function of(LocalDate $start, LocalDate $end): self
    {
        return new self(LocalDateRange::closed($start, $end));
    }

    /**
     * 1年契約を作成
     */
    public static function oneYear(LocalDate $start): self
    {
        return new self(LocalDateRange::closed(
            $start,
            $start->addYears(1)->subDays(1)
        ));
    }

    /**
     * 契約が有効かどうか
     */
    public function isActive(DateTimeZone $timezone): bool
    {
        $today = LocalDate::now($timezone);
        return $this->range->contains($today);
    }

    /**
     * 残り日数
     */
    public function daysRemaining(DateTimeZone $timezone): int
    {
        $today = LocalDate::now($timezone);
        return $this->range->end->toEpochDay() - $today->toEpochDay();
    }

    /**
     * 更新が必要かどうか (30日前から)
     */
    public function needsRenewal(DateTimeZone $timezone): bool
    {
        return $this->isActive($timezone) &&
               $this->daysRemaining($timezone) <= 30;
    }
}
```

## 関連

- [LocalDate API](/api/datetime/local-date)
- [LocalTime API](/api/datetime/local-time)
- [LocalDateTime API](/api/datetime/local-datetime)
