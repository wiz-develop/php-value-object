<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Number;

use WizDevelop\PhpValueObject\Number\IntegerRange;

require_once __DIR__ . '/../../vendor/autoload.php';

// 1. 基本的な使用例：整数範囲の作成
echo "=== 基本的な使用例 ===\n";
$range1to10 = IntegerRange::closed(1, 10);

echo "1から10の範囲: {$range1to10}\n";
echo "要素数: {$range1to10->count()} 個\n\n";

// 2. 開区間と閉区間の違い
echo "=== 開区間と閉区間の違い ===\n";
$closedRange = IntegerRange::closed(1, 5);
$openRange = IntegerRange::open(1, 5);

echo "閉区間（両端含む）: {$closedRange} = {$closedRange->count()} 個\n";
echo "開区間（両端含まない）: {$openRange} = {$openRange->count()} 個\n\n";

// 3. 半開区間の使用例（配列のインデックス範囲など）
echo "=== 半開区間の使用例 ===\n";
// 配列のインデックス範囲（0から始まり、長さを終端とする）
$arrayIndexRange = IntegerRange::halfOpenRight(0, 10);

echo "配列インデックス範囲: {$arrayIndexRange}\n";
echo '0を含む: ' . ($arrayIndexRange->contains(0) ? 'はい' : 'いいえ') . "\n";
echo '9を含む: ' . ($arrayIndexRange->contains(9) ? 'はい' : 'いいえ') . "\n";
echo '10を含む: ' . ($arrayIndexRange->contains(10) ? 'はい' : 'いいえ') . "\n\n";

// 4. 整数の反復処理
echo "=== 整数の反復処理 ===\n";
$smallRange = IntegerRange::closed(1, 5);

echo "1から5の整数:\n";
foreach ($smallRange->iterate() as $value) {
    echo " - {$value}\n";
}
echo "\n";

// 5. 範囲の重なり判定
echo "=== 範囲の重なり判定 ===\n";
$range1to5 = IntegerRange::closed(1, 5);
$range3to8 = IntegerRange::closed(3, 8);
$range10to15 = IntegerRange::closed(10, 15);

echo "範囲1: {$range1to5}\n";
echo "範囲2: {$range3to8}\n";
echo "範囲3: {$range10to15}\n";
echo '範囲1と範囲2が重なる: ' . ($range1to5->overlaps($range3to8) ? 'はい' : 'いいえ') . "\n";
echo '範囲1と範囲3が重なる: ' . ($range1to5->overlaps($range10to15) ? 'はい' : 'いいえ') . "\n\n";

// 6. 特定の整数が範囲内かチェック
echo "=== 範囲内チェック ===\n";
$scoreRange = IntegerRange::closed(0, 100);
$testScores = [50, 100, 101, -1];

echo "有効なスコア範囲: {$scoreRange}\n";
foreach ($testScores as $score) {
    echo "{$score} は有効なスコア: " . ($scoreRange->contains($score) ? 'はい' : 'いいえ') . "\n";
}
echo "\n";

// 7. エラーハンドリング
echo "=== エラーハンドリング ===\n";
$invalidResult = IntegerRange::tryFrom(10, 5);

if ($invalidResult->isErr()) {
    $error = $invalidResult->unwrapErr();
    echo "エラー: {$error->getMessage()}\n";
    echo "エラーコード: {$error->getCode()}\n";
}

// 8. nullable対応
echo "\n=== Nullable対応 ===\n";
$start = 1;
$end = null;

$optionRange = IntegerRange::fromNullable($start, $end);
if ($optionRange->isNone()) {
    echo "範囲を作成できませんでした（いずれかの値がnullです）\n";
}

// 9. 負の整数範囲
echo "\n=== 負の整数範囲 ===\n";
$negativeRange = IntegerRange::closed(-10, -1);

echo "負の整数範囲: {$negativeRange}\n";
echo "要素数: {$negativeRange->count()} 個\n";
echo '-5を含む: ' . ($negativeRange->contains(-5) ? 'はい' : 'いいえ') . "\n\n";

// 10. ゼロを含む範囲
echo "=== ゼロを含む範囲 ===\n";
$zeroIncludedRange = IntegerRange::closed(-5, 5);

echo "範囲: {$zeroIncludedRange}\n";
echo '0を含む: ' . ($zeroIncludedRange->contains(0) ? 'はい' : 'いいえ') . "\n";
echo "要素数: {$zeroIncludedRange->count()} 個\n\n";

// 11. ページネーションの例
echo "=== ページネーションの例 ===\n";
$totalItems = 95;
$itemsPerPage = 10;
$totalPages = (int)ceil($totalItems / $itemsPerPage);

$pageRange = IntegerRange::closed(1, $totalPages);
echo "ページ範囲: {$pageRange}\n";

$currentPage = 5;
$itemStartIndex = ($currentPage - 1) * $itemsPerPage;
$itemEndIndex = min($itemStartIndex + $itemsPerPage - 1, $totalItems - 1);
$itemRange = IntegerRange::closed($itemStartIndex, $itemEndIndex);

echo "ページ{$currentPage}のアイテムインデックス範囲: {$itemRange}\n";
echo "表示アイテム数: {$itemRange->count()} 個\n\n";

// 12. JSON変換
echo "=== JSON変換 ===\n";
$jsonRange = IntegerRange::halfOpenRight(0, 100);
$json = json_encode($jsonRange);

echo "範囲: {$jsonRange}\n";
echo "JSON: {$json}\n\n";

// 13. toがnullの場合（最大値まで）
echo "=== toがnullの場合（最大値まで） ===\n";
$openEndRange = IntegerRange::closed(0, null);

echo "0から最大値までの範囲: {$openEndRange}\n";
echo "最大値: " . $openEndRange->getTo() . "\n";
echo "1000を含む: " . ($openEndRange->contains(1000) ? 'はい' : 'いいえ') . "\n";

// fromNullableでもtoはnullを許容
$optionalRange = IntegerRange::fromNullable(100, null);
if ($optionalRange->isSome()) {
    $range = $optionalRange->unwrap();
    echo "100から最大値までの範囲（Optional）: {$range}\n";
}
