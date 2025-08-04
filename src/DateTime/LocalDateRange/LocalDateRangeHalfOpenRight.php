<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\DateTime\LocalDateRange;

use Override;
use WizDevelop\PhpValueObject\DateTime\LocalDate;
use WizDevelop\PhpValueObject\DateTime\LocalDateRange;
use WizDevelop\PhpValueObject\DateTime\RangeType;

/**
 * @extends LocalDateRange<LocalDate, LocalDate>
 */
final readonly class LocalDateRangeHalfOpenRight extends LocalDateRange
{
    #[Override]
    public static function rangeType(): RangeType
    {
        return RangeType::HALF_OPEN_RIGHT;
    }
}
