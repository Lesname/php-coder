<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\AtSignLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;

final class AttributeParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $expressionParseSpecification,
        private readonly ParseSpecification $referenceParseSpecification,
    ) {}

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isLexical($stream, AtSignLexical::TYPE);
    }

    /**
     * @throws Exception\UnexpectedEnd
     * @throws Exception\UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectLexical($stream, AtSignLexical::TYPE);
        $stream->next();

        $reference = $this->referenceParseSpecification->parse($stream, $file);
        assert($reference instanceof ReferenceCodeToken);

        $attributeParameters = [];

        if ($this->isLexical($stream, ParenthesisLeftLexical::TYPE)) {
            $stream->next();
            $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);


            while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
                $attributeParameters[] = $this->expressionParseSpecification->parse($stream, $file);

                $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);

                if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                    break;
                }

                $stream->next();
                $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);
            }

            $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
            $stream->next();
        }

        return new AttributeCodeToken($reference, $attributeParameters);
    }
}
