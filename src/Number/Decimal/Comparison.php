<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use Override;

/**
 * Default implementation of IComparison
 * @see WizDevelop\PhpValueObject\Number\Decimal\IComparison
 */
trait Comparison
{
    #[Override]
    public function gt(IDecimalValue $other): bool
    {
        return $this->value() > $other->value();
    }

    #[Override]
    public function gte(IDecimalValue $other): bool
    {
        return $this->value() >= $other->value();
    }

    #[Override]
    public function lt(IDecimalValue $other): bool
    {
        return $this->value() < $other->value();
    }

    #[Override]
    public function lte(IDecimalValue $other): bool
    {
        return $this->value() <= $other->value();
    }
}
