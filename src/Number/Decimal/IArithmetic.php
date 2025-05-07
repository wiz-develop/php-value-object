<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use RoundingMode;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * 算術演算可能な少数の値オブジェクト
 */
interface IArithmetic
{
    /**
     * 加算
     */
    public function add(DecimalValueBase $other, ?int $scale = null): static;

    /**
     * 加算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function tryAdd(DecimalValueBase $other, ?int $scale = null): Result;

    /**
     * 減算
     */
    public function sub(DecimalValueBase $other, ?int $scale = null): static;

    /**
     * 減算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function trySub(DecimalValueBase $other, ?int $scale = null): Result;

    /**
     * 乗算
     */
    public function mul(DecimalValueBase $other, ?int $scale = null): static;

    /**
     * 乗算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function tryMul(DecimalValueBase $other, ?int $scale = null): Result;

    /**
     * 除算
     */
    public function div(DecimalValueBase $other, ?int $scale = null): static;

    /**
     * 除算（例外を投げない）
     * @return Result<static,ValueObjectError>
     */
    public function tryDiv(DecimalValueBase $other, ?int $scale = null): Result;

    /**
     * 切り捨て処理
     */
    public function floor(): static;

    /**
     * 切り上げ処理
     */
    public function ceil(): static;

    /**
     * 丸め処理
     * @param int          $precision 小数点以下の桁数
     * @param RoundingMode $mode      丸めモード
     */
    public function round(int $precision = 0, RoundingMode $mode = RoundingMode::HalfAwayFromZero): static;
}
