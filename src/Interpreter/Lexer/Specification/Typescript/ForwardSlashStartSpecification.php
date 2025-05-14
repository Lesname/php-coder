<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Specification\Typescript;

use Override;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Specification\Exception\MissesClosingIdentifier;

final class ForwardSlashStartSpecification implements Specification
{
    #[Override]
    public function isSatisfiedBy(StringStream $code): bool
    {
        return $code->current() === '/';
    }

    /**
     * @throws MissesClosingIdentifier
     * @throws ExpectedExactly
     */
    #[Override]
    public function parse(StringStream $code): Lexical
    {
        $code->expectExactly('/');

        return match ($code->current()) {
            '/' => $this->parseSingleLineComment($code),
            '*' => $this->parseMultiLineComment($code),
            default => new ForwardSlashLexical(),
        };
    }

    /**
     * @throws ExpectedExactly
     */
    private function parseSingleLineComment(StringStream $stream): Lexical
    {
        $stream->expectExactly('/');

        $comment = '';

        while ($stream->isActive()) {
            if ($stream->current() === PHP_EOL) {
                $stream->next();

                break;
            }

            $comment .= $stream->current();
            $stream->next();
        }

        return new CommentLexical(trim($comment));
    }

    /**
     * @throws ExpectedExactly
     * @throws MissesClosingIdentifier
     */
    private function parseMultiLineComment(StringStream $stream): Lexical
    {
        $stream->expectExactly('*');

        if ($stream->current() === '*') {
            $stream->next();
        }

        $comment = '';

        while ($stream->isActive()) {
            if ($stream->current() === PHP_EOL) {
                $stream->next();

                while ($stream->current() === ' ') {
                    $stream->next();
                }

                $enter = true;
            } else {
                $enter = false;
            }

            if ($stream->current() === '*') {
                $stream->next();

                if ($stream->current() === '/') {
                    $stream->next();

                    return new CommentLexical(trim($comment));
                }

                if ($enter === false) {
                    $comment .= '*';
                } else {
                    while ($stream->current() === ' ') {
                        $stream->next();
                    }
                }
            } else {
                $comment .= $stream->current();
                $stream->next();
            }
        }

        throw new MissesClosingIdentifier('multi line comment');
    }
}
