<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Collection\Exception;

use RuntimeException;

/**
 * Collectionから要素を取得できなかった場合の例外
 */
final class CollectionNotFoundException extends RuntimeException
{
    public function __construct(?string $className = null, ?string $message = null)
    {
        if ($message === null) {
            parent::__construct("{$className} が見つかりませんでした。");
        } else {
            parent::__construct($message);
        }
    }
}
