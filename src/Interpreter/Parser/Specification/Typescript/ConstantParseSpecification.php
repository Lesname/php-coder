<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\ConstantCodeToken;
use LesCoder\Token\Value\AssignmentCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;

final class ConstantParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $expressionSpecification,
        private readonly ParseSpecification $hintParseSpecification,
    ) {}

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isKeyword($stream, 'const');
    }

    /**
     * @throws Exception\UnexpectedEnd
     * @throws Exception\UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->isKeyword($stream, 'const');
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);
        $name = (string)$stream->current();
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, ColonLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        $expression = $this->hintParseSpecification->parse($stream, $file);

        if ($this->isLexical($stream, EqualsSignLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $expression = $this->expressionSpecification->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return new AssignmentCodeToken(
            new ConstantCodeToken($name),
            $expression,
        );
    }
}
