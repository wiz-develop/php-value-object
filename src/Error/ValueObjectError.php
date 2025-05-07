<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use ReflectionClass;
use ReflectionException;
use WizDevelop\PhpValueObject\IValueObject;
use WizDevelop\PhpValueObject\ValueObjectMeta;

/**
 * 値オブジェクトエラー
 */
final readonly class ValueObjectError extends ErrorValue
{
    /**
     * ValueObjectMetaから表示名を取得する
     * クラスにValueObjectMeta属性が設定されていない場合はクラス名を返す
     *
     * @template TValueObject of IValueObject
     * @param class-string<TValueObject> $className
     */
    public static function getDisplayName(string $className): string
    {
        try {
            $reflector = new ReflectionClass($className);
            $attributes = $reflector->getAttributes(ValueObjectMeta::class);

            if (count($attributes) > 0) {
                $metaAttribute = $attributes[0]->newInstance();

                return $metaAttribute->displayName;
            }
        } catch (ReflectionException $e) {
            // 何もしない
        }

        // 属性が見つからない場合はクラス名の短縮形を返す
        $parts = explode('\\', $className);

        return end($parts);
    }

    public static function general(): GeneralError
    {
        return new GeneralError();
    }

    public static function string(): StringValueError
    {
        return new StringValueError();
    }

    public static function number(): NumberValueError
    {
        return new NumberValueError();
    }

    public static function collection(): CollectionValueError
    {
        return new CollectionValueError();
    }

    public static function boolean(): BooleanValueError
    {
        return new BooleanValueError();
    }
}
