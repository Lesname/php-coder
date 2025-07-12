<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\Dictionary\Item;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Stream\Exception\EndOfStream;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Value\AssignmentCodeToken;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Token\Expression\TypeDeclarationCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;

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
     * @throws EndOfStream
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectKeyword($stream, 'type');
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $codeToken = match (true) {
            $this->isLexical($stream, LabelLexical::TYPE) => $this->parseSingle($stream, $file),
            $this->isLexical($stream, CurlyBracketLeftLexical::TYPE) => $this->parseGroup($stream, $file),
            default => throw new UnexpectedLexical($stream->current(), LabelLexical::TYPE, CurlyBracketLeftLexical::TYPE),
        };

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return $codeToken;
    }

    private function parseSingle(LexicalStream $stream, ?string $file = null): CodeToken
    {
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

    private function parseGroup(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        $items = [];

        while ($stream->isActive() && $stream->current()->getType() !== CurlyBracketRightLexical::TYPE) {
            if ($this->isLexical($stream, StringLexical::TYPE)) {
                $key = new StringCodeToken((string)$stream->current());
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } elseif ($this->isLexical($stream, LabelLexical::TYPE)) {
                $key = new StringCodeToken((string)$stream->current());
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } else {
                throw new UnexpectedLexical($stream->current(), LabelLexical::TYPE);
            }

            if ($this->isLexical($stream, CommaLexical::TYPE, CurlyBracketRightLexical::TYPE)) {
                $value = new VariableCodeToken($key->value);
            } else {
                $this->expectLexical($stream, ColonLexical::TYPE);

                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $value = $this->parse($stream, $file);
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            }

            $items[] = new Item($key, $value);

            if ($stream->current()->getType() !== CommaLexical::TYPE) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        return new DictionaryCodeToken($items);
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
