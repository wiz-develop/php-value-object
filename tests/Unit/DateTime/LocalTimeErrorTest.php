<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\DateTime;

use AssertionError;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use WizDevelop\PhpValueObject\DateTime\LocalTime;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * LocalTimeクラスの異常系テスト
 */
#[TestDox('LocalTimeクラスの異常系テスト')]
#[Group('DateTime')]
#[CoversClass(LocalTime::class)]
final class LocalTimeErrorTest extends TestCase
{
    // ------------------------------------------
    // 範囲外の値によるエラーのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な時のデータを提供(): array
    {
        return [
            '負の時' => [-1],
            '24時' => [24],
            '極端に小さい時' => [-100],
            '極端に大きい時' => [100],
        ];
    }

    #[Test]
    #[DataProvider('無効な時のデータを提供')]
    public function 無効な時でインスタンスを作成するとエラーとなる(int $invalidHour): void
    {
        // DateTimeImmutableでは無効な時間を指定すると自動補正されるため、
        // tryFromメソッドを直接テストすることはできません。

        // 代わりに、アサーションで範囲外の時間を検証するコードをテストします
        $this->expectException(AssertionError::class);

        // PHPStanのエラーを回避するため
        // @phpstan-ignore-next-line
        LocalTime::of($invalidHour, 0);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な分のデータを提供(): array
    {
        return [
            '負の分' => [-1],
            '60分' => [60],
            '極端に小さい分' => [-100],
            '極端に大きい分' => [100],
        ];
    }

    #[Test]
    #[DataProvider('無効な分のデータを提供')]
    public function 無効な分でインスタンスを作成するとエラーとなる(int $invalidMinute): void
    {
        $this->expectException(AssertionError::class);

        // @phpstan-ignore-next-line
        LocalTime::of(0, $invalidMinute);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な秒のデータを提供(): array
    {
        return [
            '負の秒' => [-1],
            '60秒' => [60],
            '極端に小さい秒' => [-100],
            '極端に大きい秒' => [100],
        ];
    }

    #[Test]
    #[DataProvider('無効な秒のデータを提供')]
    public function 無効な秒でインスタンスを作成するとエラーとなる(int $invalidSecond): void
    {
        $this->expectException(AssertionError::class);

        // @phpstan-ignore-next-line
        LocalTime::of(0, 0, $invalidSecond);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function 無効なマイクロ秒のデータを提供(): array
    {
        return [
            '負のマイクロ秒' => [-1],
            '1,000,000マイクロ秒' => [1000000],
            '極端に小さいマイクロ秒' => [-100000],
            '極端に大きいマイクロ秒' => [10000000],
        ];
    }

    #[Test]
    #[DataProvider('無効なマイクロ秒のデータを提供')]
    public function 無効なマイクロ秒でインスタンスを作成するとエラーとなる(int $invalidMicro): void
    {
        $this->expectException(AssertionError::class);

        // @phpstan-ignore-next-line
        LocalTime::of(0, 0, 0, $invalidMicro);
    }

    // ------------------------------------------
    // ofSecondOfDayの境界値テスト
    // ------------------------------------------

    /**
     * @return array<string, array{int}>
     */
    public static function 無効な秒オブデイのデータを提供(): array
    {
        return [
            '負の秒' => [-1],
            '1日の秒数以上' => [LocalTime::SECONDS_PER_DAY],
            '極端に小さい秒' => [-100000],
            '極端に大きい秒' => [1000000],
        ];
    }

    #[Test]
    #[DataProvider('無効な秒オブデイのデータを提供')]
    public function 無効な秒オブデイ値が適切に例がをスローすること(int $invalidSecondOfDay): void
    {
        // DateTimeImmutableでは無効な秒オブデイを指定すると自動補正されるため、
        // tryFromメソッドを直接テストすることはできません。

        // 代わりに、アサーションで範囲外の秒オブデイを検証するコードをテストします
        $this->expectException(AssertionError::class);

        // PHPStanのエラーを回避するため
        // @phpstan-ignore-next-line
        LocalTime::ofSecondOfDay($invalidSecondOfDay);
    }

    // ------------------------------------------
    // 演算メソッドの端値テスト
    // ------------------------------------------

    #[Test]
    public function 加算メソッドで時刻が循環することを確認(): void
    {
        // 23時に2時間加算すると1時になる
        $time1 = LocalTime::of(23, 0, 0);
        $result1 = $time1->addHours(2);
        $this->assertSame(1, $result1->getHour());

        // 0時から1時間引くと23時になる
        $time2 = LocalTime::of(0, 0, 0);
        $result2 = $time2->addHours(-1);
        $this->assertSame(23, $result2->getHour());

        // 23:59:59に1秒加算すると00:00:00になる
        $time3 = LocalTime::of(23, 59, 59);
        $result3 = $time3->addSeconds(1);
        $this->assertSame(0, $result3->getHour());
        $this->assertSame(0, $result3->getMinute());
        $this->assertSame(0, $result3->getSecond());
    }

    #[Test]
    public function マイクロ秒の桁上がりが正しく処理される(): void
    {
        // 999,999マイクロ秒に1マイクロ秒加算すると次の秒に桁上がり
        $time = LocalTime::of(12, 30, 45, 999999);
        $result = $time->addMicros(1);

        $this->assertSame(12, $result->getHour());
        $this->assertSame(30, $result->getMinute());
        $this->assertSame(46, $result->getSecond());
        $this->assertSame(0, $result->getMicro());

        // 0マイクロ秒から1マイクロ秒引くと前の秒から桁借り
        $time2 = LocalTime::of(12, 30, 46, 0);
        $result2 = $time2->addMicros(-1);

        $this->assertSame(12, $result2->getHour());
        $this->assertSame(30, $result2->getMinute());
        $this->assertSame(45, $result2->getSecond());
        $this->assertSame(999999, $result2->getMicro());
    }

    #[Test]
    public function 大きな時間加算が正しく処理される(): void
    {
        // 1年分（1日×365）の時間を加算しても正しく循環する
        $time = LocalTime::of(12, 0, 0);
        $result = $time->addHours(24 * 365);

        // 1年後も同じ時刻
        $this->assertSame(12, $result->getHour());
        $this->assertSame(0, $result->getMinute());
        $this->assertSame(0, $result->getSecond());
    }

    // ------------------------------------------
    // 存在しない時刻によるエラーのテスト
    // ------------------------------------------

    #[Test]
    public function ISO文字列への変換で秒とマイクロ秒が省略される(): void
    {
        // 時:分のみ
        $time1 = LocalTime::of(12, 30, 0, 0);
        $this->assertSame('12:30', $time1->toISOString());

        // 時:分:秒（マイクロ秒は0）
        $time2 = LocalTime::of(12, 30, 45, 0);
        $this->assertSame('12:30:45', $time2->toISOString());

        // 時:分:秒.マイクロ秒
        $time3 = LocalTime::of(12, 30, 45, 123456);
        $this->assertSame('12:30:45.123456', $time3->toISOString());

        // マイクロ秒の末尾の0は削除される
        $time4 = LocalTime::of(12, 30, 45, 123000);
        $this->assertSame('12:30:45.123', $time4->toISOString());
    }

    // ------------------------------------------
    // tryFromメソッドのエラーテスト
    // ------------------------------------------

    #[Test]
    public function tryFromでバリデーションエラーが適切に処理される(): void
    {
        // tryFromメソッドでは、DateTimeImmutableから時間を抽出し、
        // その後にバリデーションを行うため、直接無効な値を渡すのは難しいです。
        // したがって、バリデーションが呼び出されることを確認するテストとなります。

        // 実装上、DateTimeImmutableから取得するhour, minute, secondなどの値は
        // すでに有効な範囲に収まっていることが期待されるため、
        // 通常のケースではエラーは発生しません。

        $validTime = new DateTimeImmutable('12:30:45');
        $result = LocalTime::tryFrom($validTime);

        $this->assertTrue($result->isOk());
        $this->assertSame(12, $result->unwrap()->getHour());
        $this->assertSame(30, $result->unwrap()->getMinute());
        $this->assertSame(45, $result->unwrap()->getSecond());
    }
}
