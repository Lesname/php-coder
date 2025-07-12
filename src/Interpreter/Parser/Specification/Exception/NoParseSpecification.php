<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Exception;

use LesCoder\Exception\AbstractException;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

/**
 * @psalm-immutable
 */
final class NoParseSpecification extends AbstractException
{
    public function __construct(public readonly Lexical $for)
    {
        parent::__construct("No parse specification found for '{$for}'");
    }
}
