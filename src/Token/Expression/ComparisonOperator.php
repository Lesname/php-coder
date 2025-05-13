<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use RuntimeException;

/**
 * @psalm-immutable
 */
enum ComparisonOperator
{
    case Equal;
    case Identical;
    case NotEqual;
    case NotIdentical;
    case Less;
    case Greater;
    case LessThanOrEqual;
    case GreaterThanOrEqual;
    case InstanceOf;

    public static function fromOperator(string $operator): self
    {
        return match ($operator) {
            '==' => self::Equal,
            '===' => self::Identical,
            '!=' => self::NotEqual,
            '!==' => self::NotIdentical,
            '<' => self::Less,
            '>' => self::Greater,
            '<=' => self::LessThanOrEqual,
            '>=' => self::GreaterThanOrEqual,
            'instanceof' => self::InstanceOf,
            default => throw new RuntimeException("Unknown operator '{$operator}'"),
        };
    }
}
