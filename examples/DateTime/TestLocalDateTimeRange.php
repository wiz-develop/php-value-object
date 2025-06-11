<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\DateTime;

use DateTimeImmutable;
use DateTimeZone;
use WizDevelop\PhpValueObject\DateTime\LocalDateTime;
use WizDevelop\PhpValueObject\DateTime\LocalDateTimeRange;

require_once __DIR__ . '/../../vendor/autoload.php';

// 1. 基本的な使用例：閉区間での範囲作成
echo "=== 基本的な使用例 ===\n";
$start = LocalDateTime::from(new DateTimeImmutable('2024-01-01 09:00:00'));
$end = LocalDateTime::from(new DateTimeImmutable('2024-01-01 18:00:00'));
$workingHours = LocalDateTimeRange::closed($start, $end);

echo "営業時間: {$workingHours->toISOString()}\n";
echo "期間（時間）: {$workingHours->durationInHours()} 時間\n\n";

// 2. 開区間と閉区間の違い
echo "=== 開区間と閉区間の違い ===\n";
$closedRange = LocalDateTimeRange::closed($start, $end);
$openRange = LocalDateTimeRange::open($start, $end);

$testTime = $start; // 開始時刻でテスト
echo "テスト時刻: {$testTime->toISOString()}\n";
echo '閉区間に含まれる: ' . ($closedRange->contains($testTime) ? 'はい' : 'いいえ') . "\n";
echo '開区間に含まれる: ' . ($openRange->contains($testTime) ? 'はい' : 'いいえ') . "\n\n";

// 3. 半開区間の使用例
echo "=== 半開区間の使用例 ===\n";
// イベントのスケジュール（開始時刻を含み、終了時刻を含まない）
$event1 = LocalDateTimeRange::halfOpenRight(
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 10:00:00')),
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 12:00:00'))
);
$event2 = LocalDateTimeRange::halfOpenRight(
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 12:00:00')),
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 14:00:00'))
);

echo "イベント1: {$event1->toISOString()}\n";
echo "イベント2: {$event2->toISOString()}\n";
echo 'イベントが重なる: ' . ($event1->overlaps($event2) ? 'はい' : 'いいえ') . "\n\n";

// 4. 現在時刻が営業時間内かチェック
echo "=== 営業時間チェック ===\n";
$now = LocalDateTime::now(new DateTimeZone('Asia/Tokyo'));
$businessHours = LocalDateTimeRange::closed(
    LocalDateTime::from(new DateTimeImmutable('today 09:00:00')),
    LocalDateTime::from(new DateTimeImmutable('today 18:00:00'))
);

echo "現在時刻: {$now->toISOString()}\n";
echo "営業時間: {$businessHours->toISOString()}\n";
echo '営業中: ' . ($businessHours->contains($now) ? 'はい' : 'いいえ') . "\n\n";

// 5. 期間の重なり判定
echo "=== 期間の重なり判定 ===\n";
$meeting1 = LocalDateTimeRange::closed(
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 14:00:00')),
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 15:30:00'))
);
$meeting2 = LocalDateTimeRange::closed(
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 15:00:00')),
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 16:00:00'))
);

echo "会議1: {$meeting1->toISOString()}\n";
echo "会議2: {$meeting2->toISOString()}\n";
echo 'スケジュールが衝突: ' . ($meeting1->overlaps($meeting2) ? 'はい' : 'いいえ') . "\n\n";

// 6. エラーハンドリング
echo "=== エラーハンドリング ===\n";
$invalidResult = LocalDateTimeRange::tryFrom(
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 18:00:00')),
    LocalDateTime::from(new DateTimeImmutable('2024-01-01 09:00:00'))
);

if ($invalidResult->isErr()) {
    $error = $invalidResult->unwrapErr();
    echo "エラー: {$error->getMessage()}\n";
    echo "エラーコード: {$error->getCode()}\n";
}

// 7. nullable対応
echo "\n=== Nullable対応 ===\n";
$fromTime = LocalDateTime::from(new DateTimeImmutable('2024-01-01 09:00:00'));
$toTime = null;

$optionRange = LocalDateTimeRange::fromNullable($fromTime, $toTime);
if ($optionRange->isNone()) {
    echo "範囲を作成できませんでした（いずれかの値がnullです）\n";
}
