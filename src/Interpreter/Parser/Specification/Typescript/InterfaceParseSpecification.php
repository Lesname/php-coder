<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Token\Hint\GenericCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\InterfaceCodeToken;
use LesCoder\Token\Hint\IndexSignatureCodeToken;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Token\Object\InterfaceMethodCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Token\Object\InterfacePropertyCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\NoReadonlyMethod;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\MethodMustHaveName;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

final class InterfaceParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $hintParseSpecification,
        private readonly ParseSpecification $expressionParseSpecification,
    ) {}

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isKeyword($stream, 'interface');
    }

    /**
     * @throws Exception\UnexpectedLabel
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $this->expectKeyword($stream, 'interface');
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);
        $name = (string)$stream->current();
        $stream->next();

        $generics = $this->parseGenerics($stream, $file);

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $extends = $this->parseExtends($stream, $file);

        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();

        $body = $this->parseInterfaceBody($stream, $file);

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new InterfaceCodeToken(
            $name,
            $extends,
            [],
            $body['properties'],
            $body['methods'],
            $generics,
        );
    }

    /**
     * @return array<CodeToken>
     *
     * @throws Exception\UnexpectedEnd
     * @throws Exception\UnexpectedLexical
     */
    private function parseExtends(LexicalStream $stream, ?string $file): array
    {
        $extends = [];

        if ($this->isKeyword($stream, 'extends')) {
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            do {
                $this->expectLexical($stream, LabelLexical::TYPE);
                $extend = new ReferenceCodeToken((string)$stream->current());
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                if ($this->isLexical($stream, LowerThanLexical::TYPE)) {
                    $generics = $this->parseGenerics($stream, $file);

                    $extend = new GenericCodeToken($extend, $generics);
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                }

                $extends[] = $extend;

                if ($this->isLexical($stream, CommaLexical::TYPE)) {
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    continue;
                }

                break;
            } while ($stream->isActive());
        }

        return $extends;
    }

    /**
     * @return array{properties: array<InterfacePropertyCodeToken>, methods: array<InterfaceMethodCodeToken>}
     *
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseInterfaceBody(LexicalStream $stream, ?string $file): array
    {
        $properties = $methods = [];

        $stream->skip(WhitespaceLexical::TYPE);

        while ($stream->isActive() && !$this->isLexical($stream, CurlyBracketRightLexical::TYPE)) {
            $comment = null;

            if ($this->isLexical($stream, CommentLexical::TYPE)) {
                $comment = new CommentCodeToken((string)$stream->current());
                $stream->next();
            }

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            /**
             * @todo support unnamed member
             * @see https://github.com/Lesname/php-coder/issues/1
             */
            if ($this->isLexical($stream, ParenthesisLeftLexical::TYPE)) {
                $this->parseUnnamedMember($stream);

                if ($this->isLexical($stream, CommaLexical::TYPE, SemicolonLexical::TYPE)) {
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                }

                continue;
            }

            $readonly = false;

            if ($this->isLexical($stream, LabelLexical::TYPE)) {
                $name = (string)$stream->current();
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                if ($name === 'readonly' && $this->isLexical($stream, LabelLexical::TYPE)) {
                    $name = (string)$stream->current();
                    $stream->next();

                    $readonly = true;
                }

                $name = new StringCodeToken($name);
            } elseif ($this->isLexical($stream, SquareBracketLeftLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $this->expectLexical($stream, LabelLexical::TYPE);
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $this->expectLexical($stream, ColonLexical::TYPE);
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $hint = $this->hintParseSpecification->parse($stream);
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $this->expectLexical($stream, SquareBracketRightLexical::TYPE);
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $name = new IndexSignatureCodeToken($hint);
            } else {
                throw new UnexpectedLexical($stream->current(), LabelLexical::TYPE, SquareBracketLeftLexical::TYPE);
            }

            if ($this->isLexical($stream, QuestionMarkLexical::TYPE)) {
                $required = false;

                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                $this->expectLexical($stream, ColonLexical::TYPE);
            } else {
                $required = true;
            }

            if ($this->isLexical($stream, ParenthesisLeftLexical::TYPE)) {
                if (!$name instanceof StringCodeToken) {
                    throw new MethodMustHaveName();
                }

                if ($readonly) {
                    throw new NoReadonlyMethod();
                }

                $methods[] = $this->parseInterfaceMethod($stream, $name->value, $required, $file);
            } elseif ($this->isLexical($stream, ColonLexical::TYPE)) {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $hint = $this->hintParseSpecification->parse($stream, $file);
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $properties[] = new InterfacePropertyCodeToken($name, $hint, comment: $comment, required: $required, readonly:  $readonly);
            } elseif ($this->isLexical($stream, CommaLexical::TYPE, SemicolonLexical::TYPE, CurlyBracketRightLexical::TYPE)) {
                if ($this->isLexical($stream, CommaLexical::TYPE, SemicolonLexical::TYPE)) {
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                }

                $properties[] = new InterfacePropertyCodeToken(
                    $name,
                    BuiltInCodeToken::Any,
                    comment: $comment
                );

                continue;
            } elseif ($stream->isEnd()) {
                throw new UnexpectedEnd();
            } else {
                throw new UnexpectedLexical(
                    $stream->current(),
                    ParenthesisLeftLexical::TYPE,
                    CommentLexical::TYPE,
                    CommaLexical::TYPE,
                    SemicolonLexical::TYPE,
                    CurlyBracketRightLexical::TYPE,
                );
            }

            if ($this->isLexical($stream, CommaLexical::TYPE, SemicolonLexical::TYPE)) {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            }
        }

        return [
            'properties' => $properties,
            'methods' => $methods,
        ];
    }

    private function parseUnnamedMember(LexicalStream $stream): void
    {
        $this->parseInterfaceMethod($stream, null);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseInterfaceMethod(LexicalStream $stream, ?string $name, bool $required = true, ?string $file = null): InterfaceMethodCodeToken
    {
        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $parameters = [];

        while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
            $this->expectLexical($stream, LabelLexical::TYPE);
            $paramName = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($this->isLexical($stream, QuestionMarkLexical::TYPE)) {
                $stream->next();

                $this->expectLexical($stream, ColonLexical::TYPE);
                $optional = true;
            } else {
                $optional = false;
            }

            if ($this->isLexical($stream, ColonLexical::TYPE)) {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $hint = $this->hintParseSpecification->parse($stream, $file);
            } else {
                $hint = null;
            }

            if ($this->isLexical($stream, EqualsSignLexical::TYPE)) {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $assigned = $this->expressionParseSpecification->parse($stream, $file);
            } else {
                $assigned = null;
            }

            $parameters[] = new ParameterCodeToken($paramName, $hint, $assigned, optional: $optional);

            if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, ColonLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $returns = $this->hintParseSpecification->parse($stream, $file);
        } else {
            $returns = null;
        }

        if ($name === '') {
            throw new MethodMustHaveName();
        }

        return new InterfaceMethodCodeToken($name, $parameters, $returns, $required);
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
                if ($this->isLexical($stream, LabelLexical::TYPE)) {
                    $reference = new ReferenceCodeToken((string)$stream->current());
                } elseif ($this->isLexical($stream, StringLexical::TYPE)) {
                    $reference = new StringCodeToken((string)$stream->current());
                } else {
                    throw new UnexpectedLexical($stream->current(), LabelLexical::TYPE, StringLexical::TYPE);
                }

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
                    $reference,
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
