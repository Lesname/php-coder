<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class MissesClosingIdentifier extends AbstractException
{
    public function __construct(public readonly string $type)
    {
        parent::__construct("Started a {$type}, but missing a closing identifier");
    }
}
