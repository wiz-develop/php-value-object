<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\String;

use DateTimeImmutable;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use WizDevelop\PhpValueObject\Examples\String\TestUlidValue;
use WizDevelop\PhpValueObject\String\StringValueError;
use WizDevelop\PhpValueObject\String\UlidValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * UlidValueクラスのテスト
 */
#[TestDox('UlidValueクラスのテスト')]
#[Group('UlidValue')]
#[CoversClass(UlidValue::class)]
#[CoversClass(TestUlidValue::class)]
final class UlidValueTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効なULIDでインスタンスが作成できる(): void
    {
        // 実際のULID
        $validUlid = '01H34J1XAQX0VBW6G6ZK22HC1K';
        $ulidValue = TestUlidValue::from($validUlid);

        $this->assertEquals($validUlid, $ulidValue->value);
    }

    #[Test]
    public function generateWithTimestamp関数で指定したタイムスタンプのULIDが生成できる(): void
    {
        // 特定のタイムスタンプ（2023年1月1日）
        $timestamp = strtotime('2023-01-01') * 1000;

        // 同じタイムスタンプで2つのULIDを生成
        $ulid1 = TestUlidValue::generateWithTimestamp($timestamp);
        $ulid2 = TestUlidValue::generateWithTimestamp($timestamp);

        // 両方とも正しい形式か確認
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid1->value);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid2->value);

        // タイムスタンプ部分が同じであることを確認
        $timestampPart1 = mb_substr($ulid1->value, 0, 10);
        $timestampPart2 = mb_substr($ulid2->value, 0, 10);
        $this->assertEquals($timestampPart1, $timestampPart2);

        // ランダム部分が異なることを確認
        $randomPart1 = mb_substr($ulid1->value, 10);
        $randomPart2 = mb_substr($ulid2->value, 10);
        $this->assertNotEquals($randomPart1, $randomPart2);

        // 抽出したタイムスタンプが元の値に近いことを確認
        // （完全に一致しないことがあるため、許容範囲内であることを確認）
        $extractedTimestamp = $ulid1->getTimestamp();
        $this->assertLessThan(1000, abs($extractedTimestamp - $timestamp)); // 1秒以内の誤差
    }

    #[Test]
    public function generateMonotonic関数で単調増加するULIDが生成できる(): void
    {
        // 特定のタイムスタンプ（2023年1月1日）
        $timestamp = strtotime('2023-01-01') * 1000;

        // 最初のULIDを生成
        $ulid1 = TestUlidValue::generateMonotonic($timestamp);

        // 同じタイムスタンプを使用して、前回のランダム部分を渡して次のULIDを生成
        $ulid2 = TestUlidValue::generateMonotonic($timestamp, $ulid1->getRandomBits());

        // さらに次のULIDを生成
        $ulid3 = TestUlidValue::generateMonotonic($timestamp, $ulid2->getRandomBits());

        // すべて正しい形式か確認
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid1->value);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid2->value);
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulid3->value);

        // タイムスタンプ部分が同じであることを確認
        $timestampPart1 = mb_substr($ulid1->value, 0, 10);
        $timestampPart2 = mb_substr($ulid2->value, 0, 10);
        $timestampPart3 = mb_substr($ulid3->value, 0, 10);
        $this->assertEquals($timestampPart1, $timestampPart2);
        $this->assertEquals($timestampPart2, $timestampPart3);

        // ランダム部分が順序付けされていることを確認（単調増加）
        $randomPart1 = mb_substr($ulid1->value, 10);
        $randomPart2 = mb_substr($ulid2->value, 10);
        $randomPart3 = mb_substr($ulid3->value, 10);

        // 辞書順で比較（文字列としての順序比較）
        $this->assertLessThan($randomPart2, $randomPart1);
        $this->assertLessThan($randomPart3, $randomPart2);

        // 全体としても単調増加の順序になっていることを確認
        $this->assertLessThan($ulid2->value, $ulid1->value);
        $this->assertLessThan($ulid3->value, $ulid2->value);
    }

    #[Test]
    public function getRandomBits関数でランダムビット部分を取得できる(): void
    {
        // 既知のULID
        $ulid = TestUlidValue::from('01H34J1XAQX0VBW6G6ZK22HC1K');

        // ランダムビット部分（後半16文字）を取得
        $randomBits = $ulid->getRandomBits();

        // 長さが16文字であることを確認
        $this->assertEquals(16, mb_strlen($randomBits));

        // 元のULIDから抽出した場合と同じであることを確認
        $expectedRandomBits = mb_substr($ulid->value, 10, 16);
        $this->assertEquals($expectedRandomBits, $randomBits);
    }

    #[Test]
    public function generate関数で新しいULIDが生成できる(): void
    {
        $ulidValue = TestUlidValue::generate();

        // 正しい形式かチェック
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $ulidValue->value);

        // 2回生成して違う値になることを確認
        $ulidValue2 = TestUlidValue::generate();
        $this->assertNotEquals($ulidValue->value, $ulidValue2->value);
    }

    #[Test]
    public function value関数で内部値を取得できる(): void
    {
        $validUlid = '01H34J1XAQX0VBW6G6ZK22HC1K';
        $ulidValue = TestUlidValue::from($validUlid);

        $this->assertEquals($validUlid, $ulidValue->value);
    }

    // ------------------------------------------
    // バリデーションのテスト
    // ------------------------------------------

    /**
     * @return array<string, array{string}>
     */
    public static function 無効なULIDのパターンを提供(): array
    {
        return [
            '空文字' => [''],
            '短すぎる' => ['01H34J1XAQX'],
            '長すぎる' => ['01H34J1XAQX0VBW6G6ZK22HC1K01H34J1XAQX'],
            '無効な文字を含む' => ['01H34J1XAQX0VBW6G6ZK22HCIO'], // 'O'は無効
            '小文字を含む' => ['01h34J1XAQX0VBW6G6ZK22HC1K'], // 小文字は無効
        ];
    }

    #[Test]
    #[DataProvider('無効なULIDのパターンを提供')]
    public function 無効なULIDはエラーになる(string $invalidUlid): void
    {
        $result = TestUlidValue::tryFrom($invalidUlid);

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(StringValueError::class, $result->unwrapErr());
    }

    // ------------------------------------------
    // 機能テスト
    // ------------------------------------------

    #[Test]
    public function getTimestamp関数でタイムスタンプを取得できる(): void
    {
        // 特定のタイムスタンプを持つULID
        $ulidValue = TestUlidValue::from('01H34J1XAQX0VBW6G6ZK22HC1K');

        // タイムスタンプは最初の10文字から抽出されるので、一定の範囲内にあることを確認
        $timestamp = $ulidValue->getTimestamp();

        // タイムスタンプは0以上であることを確認
        $this->assertGreaterThan(0, $timestamp);
    }

    #[Test]
    public function getDateTime関数でDateTimeImmutableを取得できる(): void
    {
        $ulidValue = TestUlidValue::from('01H34J1XAQX0VBW6G6ZK22HC1K');

        $dateTime = $ulidValue->getDateTime();

        // 結果がDateTimeImmutableインスタンスであることを確認
        $this->assertInstanceOf(DateTimeImmutable::class, $dateTime);

        // ULIDから抽出したタイムスタンプと一致することを確認
        $timestamp = $ulidValue->getTimestamp();
        $seconds = floor($timestamp / 1000);

        // タイムスタンプの秒数部分とDateTimeから得られる秒数が一致するはず
        $this->assertEquals($seconds, $dateTime->getTimestamp());
    }

    #[Test]
    public function 同じタイムスタンプで生成されたULIDの比較ができる(): void
    {
        // 2つのULIDを同時に生成（ほぼ同じタイムスタンプになるはず）
        $ulid1 = TestUlidValue::generate();
        $ulid2 = TestUlidValue::generate();

        // タイムスタンプが同じか近いことを確認（誤差は10ms以内）
        $timestamp1 = $ulid1->getTimestamp();
        $timestamp2 = $ulid2->getTimestamp();

        $this->assertLessThanOrEqual(10, abs($timestamp1 - $timestamp2));

        // 値が異なることを確認（ランダム部分が違うため）
        $this->assertNotEquals($ulid1->value, $ulid2->value);
    }

    // ------------------------------------------
    // 変換関数のテスト
    // ------------------------------------------

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $validUlid = '01H34J1XAQX0VBW6G6ZK22HC1K';
        $ulidValue = TestUlidValue::from($validUlid);

        $this->assertEquals($validUlid, (string)$ulidValue);
    }

    #[Test]
    public function jsonSerializeメソッドはULID文字列を返す(): void
    {
        $validUlid = '01H34J1XAQX0VBW6G6ZK22HC1K';
        $ulidValue = TestUlidValue::from($validUlid);

        $this->assertSame($validUlid, $ulidValue->jsonSerialize());

        $json = json_encode($ulidValue);
        $this->assertSame('"' . $validUlid . '"', $json);
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflectionClass = new ReflectionClass(TestUlidValue::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor, 'コンストラクタが見つかりませんでした');
        $this->assertTrue($constructor->isPrivate(), 'コンストラクタはprivateでなければならない');
    }

    #[Test]
    public function privateコンストラクタへのアクセスを試みるとエラーとなることを確認(): void
    {
        $hasThrown = false;

        try {
            // コンストラクタへの直接アクセスを試みる（通常これはPHPで許可されていない）
            // 以下は単にエラーが発生することを確認するだけ
            /** @phpstan-ignore-next-line */
            $newObj = new TestUlidValue('01H34J1XAQX0VBW6G6ZK22HC1K');
        } catch (Error $e) {
            $hasThrown = true;
            $this->assertStringContainsString(
                'private',
                $e->getMessage(),
                'エラーメッセージにprivateという文字列が含まれるべき'
            );
        }

        $this->assertTrue($hasThrown, 'privateコンストラクタへのアクセス時にはエラーが発生するべき');
    }

    // ------------------------------------------
    // 追加テスト：ULIDの唯一性と順序性
    // ------------------------------------------

    #[Test]
    public function ULIDは時間順に並べることができる(): void
    {
        // 古いタイムスタンプ（2023年1月1日）をベースにしたULID
        $oldTime = strtotime('2023-01-01') * 1000;
        $oldUlid = $this->createUlidWithTimestamp($oldTime);

        // 新しいタイムスタンプ（2023年7月1日）をベースにしたULID
        $newTime = strtotime('2023-07-01') * 1000;
        $newUlid = $this->createUlidWithTimestamp($newTime);

        // 新しいULIDが古いULIDより辞書順で大きいことを確認（時間順）
        $this->assertGreaterThan($oldUlid->value, $newUlid->value);
    }

    /**
     * 特定のタイムスタンプを持つULIDを作成する補助メソッド
     */
    private function createUlidWithTimestamp(int $timestamp): TestUlidValue
    {
        // タイムスタンプ部分（最初の10文字）を生成
        $timestampBytes = '';
        for ($i = 9; $i >= 0; --$i) {
            $mod = $timestamp % 32;
            $timestamp = (int)(($timestamp - $mod) / 32);
            $timestampBytes = $this->encodeChar($mod) . $timestampBytes;
        }

        // ランダム部分（残りの16文字）を生成
        $randomBytes = '';
        for ($i = 0; $i < 16; ++$i) {
            $randomBytes .= $this->encodeChar(random_int(0, 31));
        }

        $ulid = $timestampBytes . $randomBytes;

        return TestUlidValue::from($ulid);
    }

    /**
     * 数値を適切なBase32文字にエンコードする（UlidValueクラスのプライベートメソッドの複製）
     */
    private function encodeChar(int $number): string
    {
        $chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

        return $chars[$number % 32];
    }
}
