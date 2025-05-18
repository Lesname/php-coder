<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Angular;

use Override;
use Iterator;
use RuntimeException;
use LesCoder\Stream\String\StringStream;
use LesCoder\Interpreter\Lexer\CodeLexer;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Stream\Lexical\IteratorLexicalStream;
use LesCoder\Interpreter\Lexer\Lexical\TextLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PipeLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DoubleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SingleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Expression\OpenLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Expression\CloseLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\FlowControl\StartLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Element\StartCloseLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

final class TemplateCodeLexer implements CodeLexer
{
    private const array CHARACTERS = [
        GreaterThanLexical::CHARACTER => GreaterThanLexical::class,
        EqualsSignLexical::CHARACTER => EqualsSignLexical::class,
        ForwardSlashLexical::CHARACTER => ForwardSlashLexical::class,
        PipeLexical::CHARACTER => PipeLexical::class,
        DoubleQuoteLexical::CHARACTER => DoubleQuoteLexical::class,
        SingleQuoteLexical::CHARACTER => SingleQuoteLexical::class,
        ParenthesisLeftLexical::CHARACTER => ParenthesisLeftLexical::class,
        ParenthesisRightLexical::CHARACTER => ParenthesisRightLexical::class,
        SemicolonLexical::CHARACTER => SemicolonLexical::class,
        CommaLexical::CHARACTER => CommaLexical::class,
        SquareBracketLeftLexical::CHARACTER => SquareBracketLeftLexical::class,
        SquareBracketRightLexical::CHARACTER => SquareBracketRightLexical::class,
    ];

    private const array NON_TEXT_CHARACTERS = [
        GreaterThanLexical::CHARACTER,
        EqualsSignLexical::CHARACTER,
        ForwardSlashLexical::CHARACTER,
        DoubleQuoteLexical::CHARACTER,
        SingleQuoteLexical::CHARACTER,
        ParenthesisLeftLexical::CHARACTER,
        ParenthesisRightLexical::CHARACTER,
        SemicolonLexical::CHARACTER,
        CommaLexical::CHARACTER,
        SquareBracketLeftLexical::CHARACTER,
        SquareBracketRightLexical::CHARACTER,
        '<',
        '{',
        '}',
        '@',
        ' ',
        PHP_EOL,
        "\t",
    ];

    #[Override]
    public function tokenize(StringStream $stream): LexicalStream
    {
        return new IteratorLexicalStream(
            (function () use ($stream): Iterator {
                while ($stream->isActive()) {
                    yield $this->tokenizeLexical($stream);
                }
            })(),
        );
    }

    private function tokenizeLexical(StringStream $stream): Lexical
    {
        $char = $stream->current();

        if (isset(self::CHARACTERS[$char])) {
            $class = self::CHARACTERS[$char];

            $stream->next();

            return new $class();
        }

        if ($char === '<') {
            if ($stream->current(4) === '<!--') {
                return $this->tokenizeComment($stream);
            }

            if ($stream->current(2) === '</') {
                $stream->next(2);

                return new StartCloseLexical('</');
            }

            $stream->next();

            return new LowerThanLexical();
        }

        if ($char === '{') {
            $stream->next();

            if ($stream->current() === '{') {
                $stream->next();
                return new OpenLexical('{{');
            }

            return new CurlyBracketLeftLexical();
        }

        if ($char === '}') {
            $stream->next();

            if ($stream->current() === '}') {
                $stream->next();

                return new CloseLexical('}}');
            }

            return new CurlyBracketRightLexical();
        }

        if ($char === '@') {
            $stream->next();
            $name = '';

            while ($stream->isActive() && ctype_alpha($stream->current())) {
                $name .= $stream->current();
                $stream->next();
            }

            return new StartLexical($name);
        }

        if (preg_match('/\s/', $char) === 1) {
            return $this->tokenizeWhitespace($stream);
        }

        return $this->tokenizeText($stream);
    }

    private function tokenizeComment(StringStream $stream): Lexical
    {
        if ($stream->current(4) !== '<!--') {
            throw new RuntimeException('Comment must start with <!--');
        }

        $stream->next(4);

        $comment = '';

        while ($stream->isActive() && $stream->current(3) !== '-->') {
            $comment .= $stream->current();
            $stream->next();
        }

        if ($stream->current(3) === '-->') {
            $stream->next(3);
        }

        return new CommentLexical($comment);
    }

    private function tokenizeWhitespace(StringStream $stream): Lexical
    {
        $whitespace = '';

        do {
            $whitespace .= $stream->current();
            $stream->next();
        } while ($stream->isActive() && preg_match('/\s/', $stream->current()) === 1);

        return new WhitespaceLexical($whitespace);
    }

    private function tokenizeText(StringStream $stream): Lexical
    {
        $text = '';

        do {
            $text .= $stream->current();
            $stream->next();
        } while ($stream->isActive() && !in_array($stream->current(), self::NON_TEXT_CHARACTERS, true));

        return new TextLexical($text);
    }
}
