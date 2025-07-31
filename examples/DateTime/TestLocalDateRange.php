<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\DateTime;

use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalDateRange;
use WizDevelop\PhpValueObject\DateTime\RangeType;

require_once __DIR__ . '/../../vendor/autoload.php';

// 1. 基本的な使用例：月間の範囲
echo "=== 基本的な使用例 ===\n";
$startOfMonth = LocalDate::of(2024, 1, 1);
$endOfMonth = LocalDate::of(2024, 1, 31);
$january = LocalDateRange::from($startOfMonth, $endOfMonth);

echo "1月の期間: {$january->toISOString()}\n";
echo "日数: {$january->days()} 日\n\n";

// 2. 開区間と閉区間の違い
echo "=== 開区間と閉区間の違い ===\n";
$closedWeek = LocalDateRange::from(
    LocalDate::of(2024, 1, 1),
    LocalDate::of(2024, 1, 7),
    RangeType::CLOSED,
);
$openWeek = LocalDateRange::from(
    LocalDate::of(2024, 1, 1),
    LocalDate::of(2024, 1, 7),
    RangeType::OPEN,
);

echo "閉区間（両端含む）: {$closedWeek->toISOString()} = {$closedWeek->days()} 日\n";
echo "開区間（両端含まない）: {$openWeek->toISOString()} = {$openWeek->days()} 日\n\n";

// 3. 半開区間の使用例（一般的な日付範囲の表現）
echo "=== 半開区間の使用例 ===\n";
// 月初から月末まで（月末を含まない一般的なパターン）
$month = LocalDateRange::from(
    LocalDate::of(2024, 1, 1),
    LocalDate::of(2024, 2, 1),
    RangeType::HALF_OPEN_RIGHT,
);

echo "1月（右半開区間）: {$month->toISOString()}\n";
echo '1月31日を含む: ' . ($month->contains(LocalDate::of(2024, 1, 31)) ? 'はい' : 'いいえ') . "\n";
echo '2月1日を含む: ' . ($month->contains(LocalDate::of(2024, 2, 1)) ? 'はい' : 'いいえ') . "\n\n";

// 4. 日付の反復処理
echo "=== 日付の反復処理 ===\n";
$weekRange = LocalDateRange::from(
    LocalDate::of(2024, 1, 1),
    LocalDate::of(2024, 1, 7),
    RangeType::CLOSED,
);

echo "1週間の日付:\n";
foreach ($weekRange->getIterator() as $date) {
    echo " - {$date->toISOString()}\n";
}
echo "\n";

// 5. 期間の重なり判定
echo "=== 期間の重なり判定 ===\n";
$q1 = LocalDateRange::from(
    LocalDate::of(2024, 1, 1),
    LocalDate::of(2024, 3, 31),
    RangeType::CLOSED,
);
$q2 = LocalDateRange::from(
    LocalDate::of(2024, 4, 1),
    LocalDate::of(2024, 6, 30),
    RangeType::CLOSED,
);
$marchToMay = LocalDateRange::from(
    LocalDate::of(2024, 3, 1),
    LocalDate::of(2024, 5, 31),
    RangeType::CLOSED,
);

echo "第1四半期: {$q1->toISOString()}\n";
echo "第2四半期: {$q2->toISOString()}\n";
echo "3月〜5月: {$marchToMay->toISOString()}\n";
echo '第1四半期と第2四半期が重なる: ' . ($q1->overlaps($q2) ? 'はい' : 'いいえ') . "\n";
echo '第1四半期と3月〜5月が重なる: ' . ($q1->overlaps($marchToMay) ? 'はい' : 'いいえ') . "\n";
echo '第2四半期と3月〜5月が重なる: ' . ($q2->overlaps($marchToMay) ? 'はい' : 'いいえ') . "\n\n";

// 6. 特定の日付が期間内かチェック
echo "=== 期間内チェック ===\n";
$vacation = LocalDateRange::from(
    LocalDate::of(2024, 8, 10),
    LocalDate::of(2024, 8, 20),
);
$checkDate = LocalDate::of(2024, 8, 15);

echo "休暇期間: {$vacation->toISOString()}\n";
echo "{$checkDate->toISOString()} は休暇中: " . ($vacation->contains($checkDate) ? 'はい' : 'いいえ') . "\n\n";

// 7. エラーハンドリング
echo "=== エラーハンドリング ===\n";
$invalidResult = LocalDateRange::tryFrom(
    LocalDate::of(2024, 12, 31),
    LocalDate::of(2024, 1, 1),
    RangeType::CLOSED,
);

if ($invalidResult->isErr()) {
    $error = $invalidResult->unwrapErr();
    echo "エラー: {$error->getMessage()}\n";
    echo "エラーコード: {$error->getCode()}\n";
}

// 8. nullable対応
echo "\n=== Nullable対応 ===\n";
$startDate = LocalDate::of(2024, 1, 1);
$endDate = null;

// 9. 年間カレンダーの例
echo "\n=== 年間カレンダーの例 ===\n";
$year2024 = LocalDateRange::from(
    LocalDate::of(2024, 1, 1),
    LocalDate::of(2024, 12, 31),
    RangeType::CLOSED,
);

echo "2024年: {$year2024->toISOString()}\n";
echo "日数: {$year2024->days()} 日（うるう年）\n";
