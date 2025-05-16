<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class UnexpectedMode extends AbstractException
{
    /** @var array<string> */
    public readonly array $expected;

    public function __construct(public readonly string $gotten, string $expected, string ...$orExpected)
    {
        $this->expected = [$expected, ...$orExpected];

        parent::__construct("Gotten '{$gotten}, expected " . implode(' or ', $this->expected));
    }
}
