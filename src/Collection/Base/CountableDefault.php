<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Base;

use Override;

/**
 * @see \Countable
 */
trait CountableDefault
{
    #[Override]
    public function count(): int
    {
        return count($this->elements);
    }
}
