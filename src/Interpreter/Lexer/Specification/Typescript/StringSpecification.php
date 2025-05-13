<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification\Typescript;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\Exception\MissesClosingIdentifier;

final class StringSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return in_array(
            $code->current(),
            ['"', "'", '`'],
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

                return new StringLexical("{$delimiter}{$text}{$delimiter}");
            }

            $text .= $code->current();
            $code->next();
        }

        throw new MissesClosingIdentifier('string');
    }
}
