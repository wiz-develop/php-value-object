<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\String;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Error\ValueObjectError;
use WizDevelop\PhpValueObject\String\EmailAddress;

#[TestDox('EmailAddressクラスのテスト')]
#[CoversClass(EmailAddress::class)]
final class EmailAddressTest extends TestCase
{
    #[Test]
    public function 有効なメールアドレスでインスタンスが作成できる(): void
    {
        $email = EmailAddress::from('test@example.com');

        $this->assertEquals('test@example.com', $email->value);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 有効なメールアドレスのパターンを提供(): array
    {
        return [
            '標準的なメールアドレス' => ['test@example.com'],
            'サブドメインを含むアドレス' => ['test@sub.example.com'],
            'ドットを含むローカル部' => ['test.name@example.com'],
            '数字を含むアドレス' => ['test123@example.com'],
            'ダッシュを含むアドレス' => ['test-name@example.com'],
            'アンダースコアを含むアドレス' => ['test_name@example.com'],
            'プラス記号を含むアドレス' => ['test+name@example.com'],
            '大文字を含むアドレス' => ['Test.Name@Example.com'],
        ];
    }

    #[Test]
    #[DataProvider('有効なメールアドレスのパターンを提供')]
    public function 有効なメールアドレスパターンでインスタンスが作成できる(string $validEmail): void
    {
        $result = EmailAddress::tryFrom($validEmail);
        $this->assertTrue($result->isOk());
        $this->assertEquals($validEmail, $result->unwrap()->value);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function 無効なメールアドレスのパターンを提供(): array
    {
        return [
            '空文字列' => [''],
            '@記号がない' => ['testexample.com'],
            '@記号が複数ある' => ['test@example@com'],
            'ドメイン部がない' => ['test@'],
            'ローカル部がない' => ['@example.com'],
            '無効な文字を含む' => ['test<>()[]\;:,@example.com'],
            '連続したドットを含む' => ['test..name@example.com'],
            'ドメイン部に無効な文字を含む' => ['test@example..com'],
            '無効なTLD' => ['test@example'],
            '長すぎるメールアドレス' => [str_repeat('a', 245) . '@example.com'], // 254文字を超える
        ];
    }

    #[Test]
    #[DataProvider('無効なメールアドレスのパターンを提供')]
    public function 無効なメールアドレスパターンではエラーになる(string $invalidEmail): void
    {
        $result = EmailAddress::tryFrom($invalidEmail);

        $this->assertFalse($result->isOk());
        $this->assertInstanceOf(ValueObjectError::class, $result->unwrapErr());
    }

    #[Test]
    public function メタ情報がエラーメッセージに反映される(): void
    {
        // 文字列長エラーのケース
        $lengthErrorResult = EmailAddress::tryFrom('');
        $lengthErrorMessage = $lengthErrorResult->unwrapErr()->getMessage();

        // 正規表現エラーのケース
        $regexErrorResult = EmailAddress::tryFrom('address@@example.com');
        $regexErrorMessage = $regexErrorResult->unwrapErr()->getMessage();

        // どちらのエラーメッセージにも「メールアドレス」という表示名が含まれていることを確認
        $this->assertStringContainsString('メールアドレス', $lengthErrorMessage);
        $this->assertStringContainsString('メールアドレス', $regexErrorMessage);
    }

    #[Test]
    public function NullableメソッドでNullを扱える(): void
    {
        $option = EmailAddress::fromNullable(null);
        $this->assertTrue($option->isNone());

        $result = EmailAddress::tryFromNullable(null);
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap()->isNone());
    }
}
