<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use ReflectionClass;
use ReflectionException;

/**
 * ドメイン層エラー 基底クラス
 * @template TValueObject of IValueObject
 */
abstract readonly class ValueObjectError implements IValueObject
{
    use ValueObjectDefault;

    protected function __construct(
        private string $code,
        private string $message,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * ValueObjectMetaから表示名を取得する
     * クラスにValueObjectMeta属性が設定されていない場合はクラス名を返す
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
}
