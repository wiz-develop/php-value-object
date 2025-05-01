<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Exception;

use RuntimeException;

/**
 * Collectionから要素が複数取得された場合の例外
 */
final class MultipleCollectionsFoundException extends RuntimeException
{
    public function __construct(int $count)
    {
        parent::__construct("{$count} items were found.");
    }
}
