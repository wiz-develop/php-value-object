<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Examples\FullName;
use WizDevelop\PhpValueObject\String\StringValueError;

#[TestDox('FullNameクラスのテスト')]
#[CoversClass(FullName::class)]
final class FullNameTest extends TestCase
{
    #[Test]
    public function 有効な氏名でインスタンスが作成できる(): void
    {
        $name = FullName::from('山田 太郎');

        $this->assertEquals('山田 太郎', $name->value);
    }

    #[Test]
    public function 最小長の氏名でインスタンスが作成できる(): void
    {
        $name = FullName::from('太');

        $this->assertEquals('太', $name->value);
    }

    #[Test]
    public function 最大長の氏名でインスタンスが作成できる(): void
    {
        $value = str_repeat('あ', 50);
        $name = FullName::from($value);

        $this->assertEquals($value, $name->value);
    }

    #[Test]
    public function 空文字の場合はエラーになる(): void
    {
        $result = FullName::tryFrom('');

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(StringValueError::class, $result->unwrapErr());

        // エラーメッセージに「氏名」（ValueObjectMetaで指定した表示名）が含まれていることを確認
        $this->assertStringContainsString('氏名', $result->unwrapErr()->getMessage());
    }

    #[Test]
    public function 最大長を超える値はエラーになる(): void
    {
        $value = str_repeat('あ', 51);
        $result = FullName::tryFrom($value);

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(StringValueError::class, $result->unwrapErr());

        // エラーメッセージにメタ情報の表示名と長さ情報が含まれていることを確認
        $errorMessage = $result->unwrapErr()->getMessage();
        $this->assertStringContainsString('氏名', $errorMessage);
        $this->assertStringContainsString('50文字以下', $errorMessage);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 無効な文字列のパターンを提供(): array
    {
        return [
            '記号のみ' => ['@#$%^&*'],
            'スラッシュを含む' => ['山田/太郎'],
            'バックスラッシュを含む' => ['山田\太郎'],
            '特殊文字を含む' => ['山田\s太郎'],
        ];
    }

    #[Test]
    #[DataProvider('無効な文字列のパターンを提供')]
    public function 正規表現に一致しない値はエラーになる(string $invalidValue): void
    {
        $result = FullName::tryFrom($invalidValue);

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(StringValueError::class, $result->unwrapErr());

        // エラーメッセージに表示名が含まれていることを確認
        $this->assertStringContainsString('氏名', $result->unwrapErr()->getMessage());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 有効な文字列のパターンを提供(): array
    {
        return [
            '漢字' => ['山田太郎'],
            'ひらがな' => ['やまだたろう'],
            'カタカナ' => ['ヤマダタロウ'],
            '英字' => ['Yamada Taro'],
            '数字' => ['1234567890'],
            'スペースを含む' => ['山田 太郎'],
        ];
    }

    #[Test]
    #[DataProvider('有効な文字列のパターンを提供')]
    public function 正規表現に一致する値はインスタンスが作成できる(string $validValue): void
    {
        $result = FullName::tryFrom($validValue);

        $this->assertTrue($result->isOk());
        $this->assertEquals($validValue, $result->unwrap()->value);
    }

    #[Test]
    public function メタ情報がエラーメッセージに反映される(): void
    {
        // 文字列長エラーのケース
        $lengthErrorResult = FullName::tryFrom('');
        $lengthErrorMessage = $lengthErrorResult->unwrapErr()->getMessage();

        // 正規表現エラーのケース
        $regexErrorResult = FullName::tryFrom('@@@');
        $regexErrorMessage = $regexErrorResult->unwrapErr()->getMessage();

        // どちらのエラーメッセージにも「氏名」という表示名が含まれていることを確認
        $this->assertStringContainsString('氏名', $lengthErrorMessage);
        $this->assertStringContainsString('氏名', $regexErrorMessage);
    }

    #[Test]
    public function NullableメソッドでNullを扱える(): void
    {
        $option = FullName::fromNullable(null);
        $this->assertTrue($option->isNone());

        $result = FullName::tryFromNullable(null);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isNone());
    }
}
