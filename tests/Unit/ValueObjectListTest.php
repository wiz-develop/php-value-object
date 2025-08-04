<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\Number\IntegerValue;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectList;

#[TestDox('ValueObjectListクラスのテスト')]
#[CoversClass(ValueObjectList::class)]
final class ValueObjectListTest extends TestCase
{
    #[Test]
    #[TestDox('has(): 値オブジェクトが存在する場合にtrueを返すこと')]
    public function has_値オブジェクトが存在する場合にtrueを返すこと(): void
    {
        // Arrange
        $list = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
            StringValue::from('cherry'),
        ]);

        // Act
        $result = $list->has(StringValue::from('banana'));

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('has(): 値オブジェクトが存在しない場合にfalseを返すこと')]
    public function has_値オブジェクトが存在しない場合にfalseを返すこと(): void
    {
        // Arrange
        $list = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
            StringValue::from('cherry'),
        ]);

        // Act
        $result = $list->has(StringValue::from('orange'));

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('has(): 空のリストの場合にfalseを返すこと')]
    public function has_空のリストの場合にfalseを返すこと(): void
    {
        // Arrange
        $list = ValueObjectList::from([]);

        // Act
        $result = $list->has(StringValue::from('apple'));

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('remove(): 値オブジェクトを削除した新しいリストを返すこと')]
    public function remove_値オブジェクトを削除した新しいリストを返すこと(): void
    {
        // Arrange
        $list = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
            StringValue::from('cherry'),
        ]);

        // Act
        $result = $list->remove(StringValue::from('banana'));

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->has(StringValue::from('apple')));
        $this->assertFalse($result->has(StringValue::from('banana')));
        $this->assertTrue($result->has(StringValue::from('cherry')));
    }

    #[Test]
    #[TestDox('remove(): 存在しない値オブジェクトを削除しても元のリストと同じになること')]
    public function remove_存在しない値オブジェクトを削除しても元のリストと同じになること(): void
    {
        // Arrange
        $list = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
        ]);

        // Act
        $result = $list->remove(StringValue::from('orange'));

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->has(StringValue::from('apple')));
        $this->assertTrue($result->has(StringValue::from('banana')));
    }

    #[Test]
    #[TestDox('remove(): 複数の同じ値オブジェクトが存在する場合、すべて削除されること')]
    public function remove_複数の同じ値オブジェクトが存在する場合すべて削除されること(): void
    {
        // Arrange
        $list = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
            StringValue::from('apple'),
            StringValue::from('cherry'),
        ]);

        // Act
        $result = $list->remove(StringValue::from('apple'));

        // Assert
        $this->assertCount(2, $result);
        $this->assertFalse($result->has(StringValue::from('apple')));
        $this->assertTrue($result->has(StringValue::from('banana')));
        $this->assertTrue($result->has(StringValue::from('cherry')));
    }

    #[Test]
    #[TestDox('put(): 既存の値オブジェクトを新しいインスタンスで置換すること')]
    public function put_既存の値オブジェクトを新しいインスタンスで置換すること(): void
    {
        // Arrange
        $original20 = IntegerValue::from(20);
        $list = ValueObjectList::from([
            IntegerValue::from(10),
            $original20,
            IntegerValue::from(30),
        ]);
        $new20 = IntegerValue::from(20);

        // Act
        $result = $list->put($new20);

        // Assert
        $this->assertCount(3, $result);
        $values = array_map(static fn (IValueObject $v) => $v->value, $result->toArray());
        $this->assertEquals([10, 20, 30], $values);
        // 新しいインスタンスに置き換わっていることを確認
        $this->assertNotSame($original20, $result->toArray()[1]);
        $this->assertSame($new20, $result->toArray()[1]);
    }

    #[Test]
    #[TestDox('put(): 複数の同じ値オブジェクトが存在する場合、すべて置換されること')]
    public function put_複数の同じ値オブジェクトが存在する場合すべて置換されること(): void
    {
        // Arrange
        $original10_1 = IntegerValue::from(10);
        $original10_2 = IntegerValue::from(10);
        $list = ValueObjectList::from([
            $original10_1,
            IntegerValue::from(20),
            $original10_2,
            IntegerValue::from(30),
        ]);
        $new10 = IntegerValue::from(10);

        // Act
        $result = $list->put($new10);

        // Assert
        $this->assertCount(4, $result);
        $values = array_map(static fn (IValueObject $v) => $v->value, $result->toArray());
        $this->assertEquals([10, 20, 10, 30], $values);
        // すべて新しいインスタンスに置き換わっていることを確認
        $this->assertSame($new10, $result->toArray()[0]);
        $this->assertSame($new10, $result->toArray()[2]);
    }

    #[Test]
    #[TestDox('put(): 存在しない値オブジェクトの場合、リストは変更されないこと')]
    public function put_存在しない値オブジェクトの場合リストは変更されないこと(): void
    {
        // Arrange
        $list = ValueObjectList::from([
            IntegerValue::from(10),
            IntegerValue::from(20),
            IntegerValue::from(30),
        ]);

        // Act
        $result = $list->put(IntegerValue::from(40));

        // Assert
        $this->assertCount(3, $result);
        $values = array_map(static fn (IValueObject $v) => $v->value, $result->toArray());
        $this->assertEquals([10, 20, 30], $values);
    }

    #[Test]
    #[TestDox('diff(): 2つのリストの差分を返すこと')]
    public function diff_2つのリストの差分を返すこと(): void
    {
        // Arrange
        $list1 = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
            StringValue::from('cherry'),
            StringValue::from('date'),
        ]);
        $list2 = ValueObjectList::from([
            StringValue::from('banana'),
            StringValue::from('date'),
            StringValue::from('fig'),
        ]);

        // Act
        $result = $list1->diff($list2);

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->has(StringValue::from('apple')));
        $this->assertTrue($result->has(StringValue::from('cherry')));
        $this->assertFalse($result->has(StringValue::from('banana')));
        $this->assertFalse($result->has(StringValue::from('date')));
    }

    #[Test]
    #[TestDox('diff(): 空のリストとの差分は元のリストと同じになること')]
    public function diff_空のリストとの差分は元のリストと同じになること(): void
    {
        // Arrange
        $list1 = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
        ]);
        $list2 = ValueObjectList::from([]);

        // Act
        $result = $list1->diff($list2);

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->has(StringValue::from('apple')));
        $this->assertTrue($result->has(StringValue::from('banana')));
    }

    #[Test]
    #[TestDox('diff(): 同じリストとの差分は空のリストになること')]
    public function diff_同じリストとの差分は空のリストになること(): void
    {
        // Arrange
        $list1 = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
        ]);
        $list2 = ValueObjectList::from([
            StringValue::from('apple'),
            StringValue::from('banana'),
        ]);

        // Act
        $result = $list1->diff($list2);

        // Assert
        $this->assertCount(0, $result);
    }

    #[Test]
    #[TestDox('diff(): 異なる型の値オブジェクトを含むリストでも正しく動作すること')]
    public function diff_異なる型の値オブジェクトを含むリストでも正しく動作すること(): void
    {
        // Arrange
        $list1 = ValueObjectList::from([
            StringValue::from('apple'),
            IntegerValue::from(10),
            StringValue::from('banana'),
        ]);
        $list2 = ValueObjectList::from([
            IntegerValue::from(10),
            StringValue::from('cherry'),
        ]);

        // Act
        $result = $list1->diff($list2);

        // Assert
        $this->assertCount(2, $result);
        $this->assertTrue($result->has(StringValue::from('apple')));
        $this->assertTrue($result->has(StringValue::from('banana')));
        $this->assertFalse($result->has(IntegerValue::from(10)));
    }
}
