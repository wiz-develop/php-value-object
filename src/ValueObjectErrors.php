<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject;

use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * @template TValue of ValueObjectError
 * @extends ArrayList<TValue>
 */
readonly class ValueObjectErrors extends ArrayList
{
}
