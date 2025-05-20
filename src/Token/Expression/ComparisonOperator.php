<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use LesCoder\Token\Expression\Exception\UnknownOperator;

/**
 * @psalm-immutable
 */
enum ComparisonOperator
{
    case Equal;
    case Identical;
    case NotEqual;
    case NotIdentical;
    case LessThan;
    case GreaterThan;
    case LessThanOrEqual;
    case GreaterThanOrEqual;
    case InstanceOf;
    case In;

    /**
     * @throws UnknownOperator
     */
    public static function fromOperator(string $operator): self
    {
        return match ($operator) {
            '==' => self::Equal,
            '===' => self::Identical,
            '!=' => self::NotEqual,
            '!==' => self::NotIdentical,
            '<' => self::LessThan,
            '>' => self::GreaterThan,
            '<=' => self::LessThanOrEqual,
            '>=' => self::GreaterThanOrEqual,
            'instanceof' => self::InstanceOf,
            default => throw new UnknownOperator($operator),
        };
    }
}
