<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Integer;

use Override;

/**
 * Default implementation of IComparison
 * @see WizDevelop\PhpValueObject\Number\Integer\IComparison
 * @see WizDevelop\PhpValueObject\Number\Integer\IIntegerValue
 */
trait Comparison
{
    #[Override]
    final public function gt(IIntegerValue $other): bool
    {
        return $this->value() > $other->value();
    }

    #[Override]
    final public function gte(IIntegerValue $other): bool
    {
        return $this->value() >= $other->value();
    }

    #[Override]
    final public function lt(IIntegerValue $other): bool
    {
        return $this->value() < $other->value();
    }

    #[Override]
    final public function lte(IIntegerValue $other): bool
    {
        return $this->value() <= $other->value();
    }
}
