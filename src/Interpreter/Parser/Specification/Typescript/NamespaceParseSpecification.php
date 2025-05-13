<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Object\NamespaceCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;

final class NamespaceParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isKeyword($stream, 'namespace');
    }

    /**
     * @todo, fix body
     *
     * @throws Exception\UnexpectedEnd
     * @throws Exception\UnexpectedLabel
     * @throws Exception\UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectKeyword($stream, 'namespace');
        $stream->next();

        $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);
        $name = (string)$stream->current();
        $stream->next();

        $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);

        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();

        $ignore = 0;

        while ($stream->isActive() && (!$this->isLexical($stream, CurlyBracketRightLexical::TYPE) || $ignore > 0)) {
            if ($this->isLexical($stream, CurlyBracketLeftLexical::TYPE)) {
                $ignore += 1;
            } elseif ($this->isLexical($stream, CurlyBracketRightLexical::TYPE)) {
                $ignore -= 1;
            }

            $stream->next();
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        return new NamespaceCodeToken($name, []);
    }
}
