<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class UnexpectedCharacter extends AbstractException
{
    public function __construct(public readonly string $got, public readonly string $expected)
    {
        parent::__construct("Unexpected character: {$got}, expected: {$expected}");
    }
}
