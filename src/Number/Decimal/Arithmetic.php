<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Number\Decimal;

use DivisionByZeroError;
use Override;
use RoundingMode;
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * Default implementation of IArithmetic
 * @see WizDevelop\PhpValueObject\Number\Decimal\IArithmetic
 * @see WizDevelop\PhpValueObject\Number\Decimal\DecimalValueBase
 * @see WizDevelop\PhpValueObject\Number\Decimal\IDecimalValueFactory
 */
trait Arithmetic
{
    #[Override]
    final public function add(DecimalValueBase $other, ?int $scale = null): static
    {
        return static::from($this->value->add($other->value, $scale));
    }

    /**
     * @return Result<static,ValueObjectError>
     */
    #[Override]
    final public function tryAdd(DecimalValueBase $other, ?int $scale = null): Result
    {
        return static::tryFrom($this->value->add($other->value, $scale));
    }

    #[Override]
    final public function sub(DecimalValueBase $other, ?int $scale = null): static
    {
        return static::from($this->value->sub($other->value, $scale));
    }

    /**
     * @return Result<static,ValueObjectError>
     */
    #[Override]
    final public function trySub(DecimalValueBase $other, ?int $scale = null): Result
    {
        return static::tryFrom($this->value->sub($other->value, $scale));
    }

    #[Override]
    final public function mul(DecimalValueBase $other, ?int $scale = null): static
    {
        return static::from($this->value->mul($other->value, $scale));
    }

    /**
     * @return Result<static,ValueObjectError>
     */
    #[Override]
    final public function tryMul(DecimalValueBase $other, ?int $scale = null): Result
    {
        return static::tryFrom($this->value->mul($other->value, $scale));
    }

    #[Override]
    final public function div(DecimalValueBase $other, ?int $scale = null): static
    {
        if ($other->isZero()) {
            throw new DivisionByZeroError('Division by zero');
        }

        return static::from($this->value->div($other->value, $scale));
    }

    /**
     * @return Result<static,ValueObjectError>
     */
    #[Override]
    final public function tryDiv(DecimalValueBase $other, ?int $scale = null): Result
    {
        if ($other->isZero()) {
            return Result\err(ValueObjectError::number()->invalidDivideByZero(
                className: static::class,
            ));
        }

        return static::tryFrom($this->value->div($other->value, $scale));
    }

    #[Override]
    public function floor(): static
    {
        return static::from($this->value->floor());
    }

    #[Override]
    public function ceil(): static
    {
        return static::from($this->value->ceil());
    }

    #[Override]
    public function round(?int $precision = null, RoundingMode $mode = RoundingMode::HalfAwayFromZero): static
    {
        return static::from($this->value->round($precision ?? static::scale(), $mode));
    }
}
