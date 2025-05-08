<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

/**
 * DateTimeValue エラー
 */
final readonly class DateTimeValueError
{
    /**
     * 日時の範囲が無効
     * @param class-string<DateTimeValueBase>                       $className
     * @param '年'|'月'|'日'|'時'|'分'|'秒'|'マイクロ秒'                       $attributeName
     */
    public static function invalidRange(
        string $className,
        string $attributeName,
        string $value,
        ?string $minValue = null,
        ?string $maxValue = null,
    ): ValueObjectError {
        $displayName = ValueObjectError::getDisplayName($className);
        $message = "{$displayName}は有効な{$attributeName}の範囲内である必要があります。(値:{$value})";

        if ($minValue !== null && $maxValue !== null) {
            $message .= " - 有効範囲: {$minValue} から {$maxValue}";
        } elseif ($minValue !== null) {
            $message .= " - 最小値: {$minValue}";
        } elseif ($maxValue !== null) {
            $message .= " - 最大値: {$maxValue}";
        }

        return ValueObjectError::of(
            code: 'value_object.datetime.invalid_range',
            message: $message,
        );
    }
}
