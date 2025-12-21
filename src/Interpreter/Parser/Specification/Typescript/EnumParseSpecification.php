<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Object\EnumCodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;

final class EnumParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $expressionSpecification,
    ) {}

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isKeyword($stream, 'enum');
    }

    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->isKeyword($stream, 'enum');
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);
        $name = (string)$stream->current();

        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $items = [];

        while ($stream->isActive() && !$this->isLexical($stream, CurlyBracketRightLexical::TYPE)) {
            $this->expectLexical($stream, LabelLexical::TYPE);
            $key = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->expectLexical($stream, EqualsSignLexical::TYPE);
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $items[$key] = $this->expressionSpecification->parse($stream, $file);

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        return new EnumCodeToken($name, $items);
    }
}
