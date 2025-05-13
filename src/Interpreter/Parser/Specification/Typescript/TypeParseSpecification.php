<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Value\AssignmentCodeToken;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Token\Expression\TypeDeclarationCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;

final class TypeParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $hintParseSpecification,
    ) {}

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isKeyword($stream, 'type');
    }

    /**
     * @throws Exception\UnexpectedEnd
     * @throws Exception\UnexpectedLabel
     * @throws Exception\UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectKeyword($stream, 'type');
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);

        $type = new TypeDeclarationCodeToken((string)$stream->current());

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->parseGenerics($stream, $file);

        $this->expectLexical($stream, EqualsSignLexical::TYPE);

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $hint = $this->hintParseSpecification->parse($stream, $file);

        $this->expectLexical($stream, SemicolonLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new AssignmentCodeToken($type, $hint);
    }

    /**
     * @return array<GenericParameterCodeToken>
     */
    private function parseGenerics(LexicalStream $stream, ?string $file): array
    {
        $generics = [];

        if ($this->isLexical($stream, LowerThanLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            while ($stream->isActive() && !$this->isLexical($stream, GreaterThanLexical::TYPE)) {
                $this->expectLexical($stream, LabelLexical::TYPE);
                $name = (string)$stream->current();

                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                if ($this->isKeyword($stream, 'extends')) {
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    $extends = $this->hintParseSpecification->parse($stream, $file);
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                } else {
                    $extends = null;
                }

                if ($this->isLexical($stream, EqualsSignLexical::TYPE)) {
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    $assigned = $this->hintParseSpecification->parse($stream, $file);
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                } else {
                    $assigned = null;
                }

                $generics[] = new GenericParameterCodeToken(
                    new ReferenceCodeToken($name),
                    $extends,
                    $assigned,
                );

                if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                    break;
                }

                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            }

            $this->expectLexical($stream, GreaterThanLexical::TYPE);
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return $generics;
    }
}
