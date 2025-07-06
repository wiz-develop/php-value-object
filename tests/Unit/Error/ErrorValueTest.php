<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit\Error;

use AssertionError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\Error\ErrorValue;
use WizDevelop\PhpValueObject\Error\IErrorValue;

#[TestDox('ErrorValueクラスのテスト')]
#[CoversClass(ErrorValue::class)]
final class ErrorValueTest extends TestCase
{
    #[Test]
    public function エラーコードとメッセージでインスタンスが作成できる(): void
    {
        $error = ErrorValue::of('E001', 'エラーが発生しました');

        $this->assertEquals('E001', $error->getCode());
        $this->assertEquals('エラーが発生しました', $error->getMessage());
        $this->assertEmpty($error->getDetails());
    }

    #[Test]
    public function 詳細エラー情報を含むインスタンスが作成できる(): void
    {
        $detail1 = ErrorValue::of('E002', '詳細エラー1');
        $detail2 = ErrorValue::of('E003', '詳細エラー2');
        $error = ErrorValue::of('E001', 'メインエラー', [$detail1, $detail2]);

        $this->assertEquals('E001', $error->getCode());
        $this->assertEquals('メインエラー', $error->getMessage());

        $details = $error->getDetails();
        $this->assertCount(2, $details);
        $this->assertInstanceOf(IErrorValue::class, $details[0]);
        $this->assertInstanceOf(IErrorValue::class, $details[1]);
        $this->assertEquals('E002', $details[0]->getCode());
        $this->assertEquals('詳細エラー1', $details[0]->getMessage());
        $this->assertEquals('E003', $details[1]->getCode());
        $this->assertEquals('詳細エラー2', $details[1]->getMessage());
    }

    #[Test]
    public function ネストした詳細エラー情報を含むインスタンスが作成できる(): void
    {
        $nestedDetail = ErrorValue::of('E004', 'ネストした詳細エラー');
        $nestedDetail2 = ErrorValue::of('E005', 'ネストした詳細エラー');
        $detail = ErrorValue::of('E003', '詳細エラー', [$nestedDetail, $nestedDetail2]);
        $error = ErrorValue::of('E001', 'メインエラー', [$detail]);

        var_dump('⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️⚠️');
        var_dump($error->serialize());

        $this->assertEquals('E001', $error->getCode());
        $this->assertEquals('メインエラー', $error->getMessage());

        $details = $error->getDetails();
        $this->assertCount(1, $details);
        $this->assertEquals('E003', $details[0]->getCode());

        $nestedDetails = $details[0]->getDetails();
        $this->assertCount(2, $nestedDetails);
        $this->assertEquals('E004', $nestedDetails[0]->getCode());
        $this->assertEquals('ネストした詳細エラー', $nestedDetails[0]->getMessage());
        $this->assertEquals('E005', $nestedDetails[1]->getCode());
        $this->assertEquals('ネストした詳細エラー', $nestedDetails[1]->getMessage());
    }

    #[Test]
    public function toString変換が正しく動作する(): void
    {
        $error = ErrorValue::of('E001', 'エラーメッセージ');

        $this->assertEquals('E001' . ErrorValue::SEPARATOR . 'エラーメッセージ', (string)$error);
    }

    #[Test]
    public function 詳細情報なしのシリアライズとデシリアライズが正しく動作する(): void
    {
        $error = ErrorValue::of('E001', 'エラーメッセージ');
        $serialized = $error->serialize();
        $deserialized = ErrorValue::deserialize($serialized);

        $this->assertEquals($error->getCode(), $deserialized->getCode());
        $this->assertEquals($error->getMessage(), $deserialized->getMessage());
        $this->assertEmpty($deserialized->getDetails());
    }

    #[Test]
    public function 詳細情報ありのシリアライズとデシリアライズが正しく動作する(): void
    {
        $detail1 = ErrorValue::of('E002', '詳細エラー1');
        $detail2 = ErrorValue::of('E003', '詳細エラー2');
        $error = ErrorValue::of('E001', 'メインエラー', [$detail1, $detail2]);

        $serialized = $error->serialize();
        $deserialized = ErrorValue::deserialize($serialized);

        $this->assertEquals($error->getCode(), $deserialized->getCode());
        $this->assertEquals($error->getMessage(), $deserialized->getMessage());

        $details = $deserialized->getDetails();
        $this->assertCount(2, $details);
        $this->assertEquals('E002', $details[0]->getCode());
        $this->assertEquals('詳細エラー1', $details[0]->getMessage());
        $this->assertEquals('E003', $details[1]->getCode());
        $this->assertEquals('詳細エラー2', $details[1]->getMessage());
    }

    #[Test]
    public function ネストした詳細情報のシリアライズとデシリアライズが正しく動作する(): void
    {
        $nestedDetail = ErrorValue::of('E004', 'ネストした詳細エラー');
        $detail = ErrorValue::of('E003', '詳細エラー', [$nestedDetail]);
        $error = ErrorValue::of('E001', 'メインエラー', [$detail]);

        $serialized = $error->serialize();
        $deserialized = ErrorValue::deserialize($serialized);

        $this->assertEquals($error->getCode(), $deserialized->getCode());
        $this->assertEquals($error->getMessage(), $deserialized->getMessage());

        $details = $deserialized->getDetails();
        $this->assertCount(1, $details);
        $this->assertEquals('E003', $details[0]->getCode());
        $this->assertEquals('詳細エラー', $details[0]->getMessage());

        $nestedDetails = $details[0]->getDetails();
        $this->assertCount(1, $nestedDetails);
        $this->assertEquals('E004', $nestedDetails[0]->getCode());
        $this->assertEquals('ネストした詳細エラー', $nestedDetails[0]->getMessage());
    }

    #[Test]
    public function 不正なシリアライズフォーマットでアサーションエラーが発生する(): void
    {
        $this->expectException(AssertionError::class);
        $this->expectExceptionMessage('Invalid serialized error value format.');

        ErrorValue::deserialize('E001');
    }

    #[Test]
    public function equalsメソッドで同じエラーが等しいと判定される(): void
    {
        $error1 = ErrorValue::of('E001', 'エラーメッセージ');
        $error2 = ErrorValue::of('E001', 'エラーメッセージ');

        $this->assertTrue($error1->equals($error2));
    }

    #[Test]
    public function equalsメソッドで異なるコードのエラーが等しくないと判定される(): void
    {
        $error1 = ErrorValue::of('E001', 'エラーメッセージ');
        $error2 = ErrorValue::of('E002', 'エラーメッセージ');

        $this->assertFalse($error1->equals($error2));
    }

    #[Test]
    public function equalsメソッドで異なるメッセージのエラーが等しくないと判定される(): void
    {
        $error1 = ErrorValue::of('E001', 'エラーメッセージ1');
        $error2 = ErrorValue::of('E001', 'エラーメッセージ2');

        $this->assertFalse($error1->equals($error2));
    }

    #[Test]
    public function equalsメソッドで詳細情報が異なるエラーが等しくないと判定される(): void
    {
        $detail1 = ErrorValue::of('E002', '詳細エラー1');
        $detail2 = ErrorValue::of('E002', '詳細エラー2');

        $error1 = ErrorValue::of('E001', 'エラーメッセージ', [$detail1]);
        $error2 = ErrorValue::of('E001', 'エラーメッセージ', [$detail2]);

        $this->assertFalse($error1->equals($error2));
    }

    #[Test]
    public function equalsメソッドで詳細情報を含む同じエラーが等しいと判定される(): void
    {
        $detail1 = ErrorValue::of('E002', '詳細エラー');
        $detail2 = ErrorValue::of('E002', '詳細エラー');

        $error1 = ErrorValue::of('E001', 'エラーメッセージ', [$detail1]);
        $error2 = ErrorValue::of('E001', 'エラーメッセージ', [$detail2]);

        $this->assertTrue($error1->equals($error2));
    }
}
