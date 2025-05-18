<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript\Exception;

use LesCoder\Exception\AbstractException;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

/**
 * @psalm-immutable
 */
final class UnexpectedLexical extends AbstractException
{
    /**
     * @var array<string>
     */
    public readonly array $expected;

    public function __construct(public readonly Lexical $gotten, string $expected, string ...$moreExpected)
    {
        $this->expected = [$expected, ...$moreExpected];
        $expectedString = implode(', ', $this->expected);
        parent::__construct("Expected {$expectedString}, gotten {$gotten->getType()}");
    }
}
