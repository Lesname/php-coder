<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Angular\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class InvalidName extends AbstractException
{
    public function __construct(public readonly string $name)
    {
        parent::__construct("Invalid name '{$name}'");
    }
}
