<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Boolean;

use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use WizDevelop\PhpValueObject\Boolean\BooleanValue;
use WizDevelop\PhpValueObject\Examples\Boolean\TestBooleanValue;
use WizDevelop\PhpValueObject\Tests\TestCase;

/**
 * BooleanValue抽象クラスのテスト
 */
#[TestDox('BooleanValue抽象クラスのテスト')]
#[Group('BooleanValue')]
#[CoversClass(BooleanValue::class)]
#[CoversClass(TestBooleanValue::class)]
final class BooleanValueTest extends TestCase
{
    // ------------------------------------------
    // インスタンス生成と基本機能のテスト
    // ------------------------------------------

    #[Test]
    public function 有効な値でインスタンスが作成できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $this->assertTrue($boolTrue->value);

        $boolFalse = TestBooleanValue::from(false);
        $this->assertFalse($boolFalse->value);
    }

    #[Test]
    public function 専用ファクトリメソッドでインスタンスが作成できる(): void
    {
        $boolTrue = TestBooleanValue::true();
        $this->assertTrue($boolTrue->value);

        $boolFalse = TestBooleanValue::false();
        $this->assertFalse($boolFalse->value);
    }

    #[Test]
    public function value関数で内部値を取得できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $this->assertTrue($boolTrue->value);

        $boolFalse = TestBooleanValue::from(false);
        $this->assertFalse($boolFalse->value);
    }

    #[Test]
    public function isTrue関数で真の値かどうかを判定できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertTrue($boolTrue->yes());
        $this->assertFalse($boolFalse->yes());
    }

    #[Test]
    public function isFalse関数で偽の値かどうかを判定できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertFalse($boolTrue->no());
        $this->assertTrue($boolFalse->no());
    }

    // ------------------------------------------
    // 論理演算のテスト
    // ------------------------------------------

    #[Test]
    public function not関数で否定値を取得できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertTrue($boolTrue->not()->no());
        $this->assertTrue($boolFalse->not()->yes());
    }

    #[Test]
    public function and関数で論理積を計算できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertTrue($boolTrue->and($boolTrue)->yes());
        $this->assertTrue($boolTrue->and($boolFalse)->no());
        $this->assertTrue($boolFalse->and($boolTrue)->no());
        $this->assertTrue($boolFalse->and($boolFalse)->no());
    }

    #[Test]
    public function or関数で論理和を計算できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertTrue($boolTrue->or($boolTrue)->yes());
        $this->assertTrue($boolTrue->or($boolFalse)->yes());
        $this->assertTrue($boolFalse->or($boolTrue)->yes());
        $this->assertTrue($boolFalse->or($boolFalse)->no());
    }

    #[Test]
    public function xor関数で排他的論理和を計算できる(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertTrue($boolTrue->xor($boolTrue)->no());
        $this->assertTrue($boolTrue->xor($boolFalse)->yes());
        $this->assertTrue($boolFalse->xor($boolTrue)->yes());
        $this->assertTrue($boolFalse->xor($boolFalse)->no());
    }

    // ------------------------------------------
    // Nullableメソッドのテスト
    // ------------------------------------------

    #[Test]
    public function fromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $option1 = TestBooleanValue::fromNullable(null);
        $this->assertTrue($option1->isNone());

        // 非Null値の場合
        $option2 = TestBooleanValue::fromNullable(true);
        $this->assertTrue($option2->isSome());
        $this->assertTrue($option2->unwrap()->value);

        $option3 = TestBooleanValue::fromNullable(false);
        $this->assertTrue($option3->isSome());
        $this->assertFalse($option3->unwrap()->value);
    }

    #[Test]
    public function tryFromNullable関数でNullを扱える(): void
    {
        // Null値の場合
        $result1 = TestBooleanValue::tryFromNullable(null);
        $this->assertTrue($result1->isOk());
        $this->assertTrue($result1->unwrap()->isNone());

        // 非Null値の場合
        $result2 = TestBooleanValue::tryFromNullable(true);
        $this->assertTrue($result2->isOk());
        $this->assertTrue($result2->unwrap()->isSome());
        $this->assertTrue($result2->unwrap()->unwrap()->value);

        $result3 = TestBooleanValue::tryFromNullable(false);
        $this->assertTrue($result3->isOk());
        $this->assertTrue($result3->unwrap()->isSome());
        $this->assertFalse($result3->unwrap()->unwrap()->value);
    }

    // ------------------------------------------
    // 変換関数のテスト
    // ------------------------------------------

    #[Test]
    public function 文字列表現のテスト(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertEquals('true', (string)$boolTrue);
        $this->assertEquals('false', (string)$boolFalse);
    }

    #[Test]
    public function jsonSerializeメソッドは真偽値を返す(): void
    {
        $boolTrue = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertTrue($boolTrue->jsonSerialize());
        $this->assertFalse($boolFalse->jsonSerialize());

        $jsonTrue = json_encode($boolTrue);
        $jsonFalse = json_encode($boolFalse);

        $this->assertSame('true', $jsonTrue);
        $this->assertSame('false', $jsonFalse);
    }

    // ------------------------------------------
    // 比較演算のテスト
    // ------------------------------------------

    #[Test]
    public function equals関数で同値性の比較ができる(): void
    {
        $boolTrue1 = TestBooleanValue::from(true);
        $boolTrue2 = TestBooleanValue::from(true);
        $boolFalse = TestBooleanValue::from(false);

        $this->assertTrue($boolTrue1->equals($boolTrue2));
        $this->assertFalse($boolTrue1->equals($boolFalse));
        $this->assertFalse($boolFalse->equals($boolTrue1));
        $this->assertTrue($boolFalse->equals($boolFalse));
    }

    // ------------------------------------------
    // アクセス制御のテスト
    // ------------------------------------------

    #[Test]
    public function コンストラクタはprivateアクセス修飾子を持つことを確認(): void
    {
        $reflectionClass = new ReflectionClass(TestBooleanValue::class);
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
            $newObj = new TestBooleanValue(true);
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
    // 追加テスト：複合的な論理演算
    // ------------------------------------------

    #[Test]
    public function 複合的な論理演算のテスト(): void
    {
        $trueVal = TestBooleanValue::true();
        $falseVal = TestBooleanValue::false();

        // (true AND false) OR true = true
        $result1 = $trueVal->and($falseVal)->or($trueVal);
        $this->assertTrue($result1->yes());

        // (false OR true) AND false = false
        $result2 = $falseVal->or($trueVal)->and($falseVal);
        $this->assertTrue($result2->no());

        // NOT (true AND true) = false
        $result3 = $trueVal->and($trueVal)->not();
        $this->assertTrue($result3->no());

        // true XOR (false OR false) = true
        $result4 = $trueVal->xor($falseVal->or($falseVal));
        $this->assertTrue($result4->yes());
    }
}
