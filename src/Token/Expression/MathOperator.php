<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use RuntimeException;

enum MathOperator
{
    case Divide;
    case Addition;
    case Multiply;
    case Subtract;

    public static function fromOperator(string $operator): self
    {
        return match ($operator) {
            '-' => self::Subtract,
            '+' => self::Addition,
            '/' => self::Divide,
            '*' => self::Multiply,
            default => throw new RuntimeException("Unknown operator '{$operator}'"),
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
