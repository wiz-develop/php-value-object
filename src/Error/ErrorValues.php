<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Error;

use WizDevelop\PhpValueObject\Collection\ArrayList;

/**
 * @template TValue of ErrorValue
 * @extends ArrayList<TValue>
 */
readonly class ErrorValues extends ArrayList
{
}
