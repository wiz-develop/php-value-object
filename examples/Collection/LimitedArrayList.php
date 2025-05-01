<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\Collection;

use Override;
use WizDevelop\PhpValueObject\Collection\ArrayList;

/**
 * 要素数制限付きArrayList
 *
 * @template TValue
 * @extends ArrayList<TValue>
 */
final readonly class LimitedArrayList extends ArrayList
{
    /**
     * 最小要素数
     */
    #[Override]
    protected static function minCount(): int
    {
        return 2; // 2要素以上必要
    }

    /**
     * 最大要素数
     */
    #[Override]
    protected static function maxCount(): int
    {
        return 5; // 5要素以下必要
    }
}
