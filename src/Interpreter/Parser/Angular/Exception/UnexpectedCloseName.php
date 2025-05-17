<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Angular\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class UnexpectedCloseName extends AbstractException
{
    public function __construct(public readonly string $gotten, public readonly string $expected)
    {
        parent::__construct("Expected '{$expected}', gotten '{$gotten}'");
    }
}
