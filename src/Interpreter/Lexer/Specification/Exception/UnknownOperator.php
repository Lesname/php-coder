<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class UnknownOperator extends AbstractException
{
    public function __construct(public readonly string $operator)
    {
        parent::__construct("Unknown operator: {$operator}");
    }
}
