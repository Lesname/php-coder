<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript\Exception;

use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class UnexpectedEnd extends AbstractException
{
    /**
     * @var array<string>
     */
    public readonly array $expected;

    public function __construct(string ...$expected)
    {
        $this->expected = $expected;
        $expectedString = implode(', ', $this->expected);
        parent::__construct("Expected {$expectedString}, gotten end");
    }
}
