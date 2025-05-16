<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use LesCoder\Token\Expression\Exception\UnknownOperator;

enum MathOperator
{
    case Divide;
    case Addition;
    case Multiply;
    case Subtract;

    /**
     * @throws UnknownOperator
     */
    public static function fromOperator(string $operator): self
    {
        return match ($operator) {
            '-' => self::Subtract,
            '+' => self::Addition,
            '/' => self::Divide,
            '*' => self::Multiply,
            default => throw new UnknownOperator($operator),
        };
    }

    /**
     * @psalm-pure
     */
    public function asOperator(): string
    {
        return match ($this) {
            self::Subtract => '-',
            self::Addition => '+',
            self::Divide => '/',
            self::Multiply => '*',
        };
    }
}
