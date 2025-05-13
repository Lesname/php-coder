<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Interpreter\Lexer\Lexical\Expression\AndLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\AmpersandLexical;

final class AmpersandSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return $code->current() === '&';
    }

    /**
     * @throws ExpectedExactly
     */
    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $code->expectExactly('&');

        if ($code->current() === '&') {
            $code->next();

            return new AndLexical('&&');
        }

        return new AmpersandLexical();
    }
}
