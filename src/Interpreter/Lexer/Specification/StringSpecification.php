<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Specification\Exception\MissesClosingIdentifier;

final class StringSpecification implements Specification
{
    /**
     * @param non-empty-array<string> $enclosers
     */
    public function __construct(public readonly array $enclosers)
    {}

    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return in_array(
            $code->current(),
            $this->enclosers,
            true,
        );
    }

    /**
     * @throws MissesClosingIdentifier
     */
    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $delimiter = $code->current();
        $code->next();

        $text = '';

        while ($code->isActive()) {
            if ($code->current() === $delimiter) {
                $code->next();

                return new StringLexical("{$text}");
            }

            $text .= $code->current();
            $code->next();
        }

        throw new MissesClosingIdentifier('string');
    }
}
