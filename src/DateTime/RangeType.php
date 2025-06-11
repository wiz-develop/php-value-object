<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\DateTime;

/**
 * 期間の境界タイプを表す列挙型
 */
enum RangeType: string
{
    case CLOSED = 'closed';              // [from, to] 閉区間
    case OPEN = 'open';                  // (from, to) 開区間
    case HALF_OPEN_LEFT = 'half_open_left';   // (from, to] 左開区間
    case HALF_OPEN_RIGHT = 'half_open_right'; // [from, to) 右開区間
}