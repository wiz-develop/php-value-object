<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\String;

use DateTimeImmutable;
use Override;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\String\Base\StringValueBase;
use WizDevelop\PhpValueObject\String\Base\StringValueFactory;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * ULIDの値オブジェクト
 * ULID (Universally Unique Lexicographically Sortable Identifier)
 * 26文字のソート可能な一意識別子
 *
 * @see https://github.com/ulid/spec
 */
#[ValueObjectMeta(displayName: 'ULID', description: 'ULIDの値オブジェクト')]
readonly class UlidValue extends StringValueBase
{
    use StringValueFactory;

    /**
     * ULIDの文字長（固定）
     */
    private const int ULID_LENGTH = 26;

    /**
     * ULIDの正規表現パターン
     * Base32文字（大文字の英字と数字、I, L, O, Uを除く）で構成される
     */
    private const string ULID_REGEX = '/^[0-9A-HJKMNP-TV-Z]{26}$/';

    /**
     * Avoid new() operator.
     */
    final private function __construct(string $value)
    {
        parent::__construct($value);
    }

    #[Override]
    final public static function tryFrom(string $value): Result
    {
        return self::isValid($value)
            ->andThen(static fn () => self::isLengthValid($value))
            ->andThen(static fn () => self::isRegexValid($value))
            ->andThen(static fn () => self::isValidUlid($value))
            ->andThen(static fn () => Result\ok(self::from($value)));
    }

    /**
     * ULIDの最小文字数（常に固定長）
     */
    #[Override]
    final protected static function minLength(): int
    {
        return self::ULID_LENGTH;
    }

    /**
     * ULIDの最大文字数（常に固定長）
     */
    #[Override]
    final protected static function maxLength(): int
    {
        return self::ULID_LENGTH;
    }

    /**
     * ULIDのパターン
     */
    #[Override]
    final protected static function regex(): string
    {
        return self::ULID_REGEX;
    }

    /**
     * ULIDとして有効かどうか
     * @return Result<bool,StringValueError>
     */
    final protected static function isValidUlid(string $value): Result
    {
        $pregMatchResult = preg_match(self::ULID_REGEX, $value);
        if ($pregMatchResult !== 1) {
            return Result\err(StringValueError::invalidUlid(
                className: self::class,
                value: $value,
            ));
        }

        return Result\ok(true);
    }

    /**
     * 新しいULIDを生成する
     */
    final public static function generate(): static
    {
        // microtimeを使用して、現在のタイムスタンプをミリ秒単位で取得
        $msec = (int)(microtime(true) * 1000);

        // タイムスタンプ部分（最初の10文字）を生成
        $timestampBytes = '';
        for ($i = 9; $i >= 0; --$i) {
            $mod = $msec % 32;
            $msec = ($msec - $mod) / 32;
            $timestampBytes = self::encodeChar($mod) . $timestampBytes;
        }

        // ランダム部分（残りの16文字）を生成
        $randomBytes = '';
        for ($i = 0; $i < 16; ++$i) {
            $randomBytes .= self::encodeChar(random_int(0, 31));
        }

        $ulid = $timestampBytes . $randomBytes;

        return self::from($ulid);
    }

    /**
     * ULIDからタイムスタンプを抽出する（ミリ秒単位）
     */
    final public function getTimestamp(): int
    {
        $timestampPart = mb_substr($this->value, 0, 10);
        $timestamp = 0;

        // タイムスタンプ部分（最初の10文字）をデコード
        for ($i = 0; $i < 10; ++$i) {
            $char = $timestampPart[$i];
            $timestamp = $timestamp * 32 + self::decodeChar($char);
        }

        return $timestamp;
    }

    /**
     * タイムスタンプをDateTimeImmutableに変換する
     */
    final public function getDateTime(): DateTimeImmutable
    {
        $timestamp = $this->getTimestamp();
        $seconds = floor($timestamp / 1000);
        $milliseconds = $timestamp % 1000;

        $dateTime = new DateTimeImmutable('@' . $seconds);

        return $dateTime->modify('+' . $milliseconds . ' milliseconds');
    }

    // -------------------------------------------------------------------------
    // NOTE: private methods
    // -------------------------------------------------------------------------
    /**
     * 数値を適切なBase32文字にエンコードする（I, L, O, Uを除く）
     */
    private static function encodeChar(int $number): string
    {
        // Crockford's Base32文字セット (0-9, A-Z, 除外: I, L, O, U)
        $chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

        return $chars[$number];
    }

    /**
     * Base32文字を数値にデコードする
     */
    private static function decodeChar(string $char): int
    {
        $charMap = [
            '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
            '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
            'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14,
            'F' => 15, 'G' => 16, 'H' => 17, 'J' => 18, 'K' => 19,
            'M' => 20, 'N' => 21, 'P' => 22, 'Q' => 23, 'R' => 24,
            'S' => 25, 'T' => 26, 'V' => 27, 'W' => 28, 'X' => 29,
            'Y' => 30, 'Z' => 31,
        ];

        return $charMap[$char] ?? 0;
    }
}
