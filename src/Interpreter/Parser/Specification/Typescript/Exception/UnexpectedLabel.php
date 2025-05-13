<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript\Exception;

use LesCoder\Exception\AbstractException;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

/**
 * @psalm-immutable
 */
final class UnexpectedLabel extends AbstractException
{
    public function __construct(Lexical $token, string $expected)
    {
        parent::__construct("Expected '{$expected}', gotten '{$token}'");
    }
}
