<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification;

use Override;
use RuntimeException;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ExclamationLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\SameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\EqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotSameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\LowerThanOrEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\GreaterThanOrEqualsLexical;

final class ComparisonSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return in_array(
            $code->current(),
            ['>', '<', '=', '!'],
            true,
        );
    }

    /**
     * @throws ExpectedExactly
     */
    #[Override]
    public function parse(StringStream $code): Lexical
    {
        return match ($code->current()) {
            '>' => $this->parseGreaterThan($code),
            '<' => $this->parseLowerThan($code),
            '=' => $this->parseEquals($code),
            '!' => $this->parseExclamation($code),
            default => throw new RuntimeException("Unexpected '{$code->current()}'"),
        };
    }

    /**
     * @throws ExpectedExactly
     */
    private function parseGreaterThan(StringStream $code): Lexical
    {
        $code->expectExactly('>');

        if ($code->matchesExactly('=')) {
            $code->next();

            return new GreaterThanOrEqualsLexical('>=');
        }

        return new GreaterThanLexical();
    }

    /**
     * @throws ExpectedExactly
     */
    private function parseLowerThan(StringStream $code): Lexical
    {
        $code->expectExactly('<');

        if ($code->matchesExactly('=')) {
            $code->next();

            return new LowerThanOrEqualsLexical('<=');
        }

        return new LowerThanLexical();
    }

    /**
     * @throws ExpectedExactly
     */
    private function parseEquals(StringStream $code): Lexical
    {
        $code->expectExactly('=');

        if ($code->matchesExactly('=')) {
            $code->next();

            if ($code->matchesExactly('=')) {
                $code->next();

                return new SameLexical('===');
            }

            return new EqualsLexical('==');
        }

        return new EqualsSignLexical();
    }

    /**
     * @throws ExpectedExactly
     */
    private function parseExclamation(StringStream $code): Lexical
    {
        $code->expectExactly('!');

        if ($code->matchesExactly('=')) {
            $code->next();

            if ($code->matchesExactly('=')) {
                $code->next();

                return new NotSameLexical('===');
            }

            return new NotEqualsLexical('==');
        }

        return new ExclamationLexical();
    }
}
