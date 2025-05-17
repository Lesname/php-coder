<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Object\ClassCodeToken;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\AbstractClassPart;
use LesCoder\Token\Expression\ExportCodeToken;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Token\Object\ClassGetPropertyCodeToken;
use LesCoder\Token\Object\ClassSetPropertyCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Parser\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;

final class ClassParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $attributesParseSpecification,
        private readonly ParseSpecification $expressionParseSpecification,
        private readonly ParseSpecification $referenceParseSpecification,
        private readonly ParseSpecification $hintParseSpecification,
    ) {
    }

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return $this->isKeyword($stream, 'abstract')
            || $this->isKeyword($stream, 'class')
            || $this->attributesParseSpecification->isSatisfiedBy($stream);
    }

    /**
     * @throws Exception\UnexpectedLabel
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $attributes = $this->parseAttributes($stream, $file);
        $flags = 0;

        if ($this->isKeyword($stream, 'export')) {
            $export = true;

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        } else {
            $export = false;
        }

        if ($this->isKeyword($stream, 'abstract')) {
            $flags |= ClassCodeToken::FLAG_ABSTRACT;

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectKeyword($stream, 'class');
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);
        $name = (string)$stream->current();

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $generics = $this->parseGenerics($stream, $file);

        $extends = $this->parseExtends($stream, $file);
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $implements = $this->parseImplements($stream, $file);
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $properties = $methods = [];

        while ($stream->isActive() && $stream->current()->getType() !== CurlyBracketRightLexical::TYPE) {
            $classPart = $this->parseClassPart($stream, $file);

            if ($classPart instanceof ClassMethodCodeToken) {
                $methods[] = $classPart;
            } elseif ($classPart instanceof ClassPropertyCodeToken || $classPart instanceof ClassGetPropertyCodeToken || $classPart instanceof ClassSetPropertyCodeToken) {
                $properties[] = $classPart;
            } else {
                throw new UnexpectedCodeToken();
            }

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        $class = new ClassCodeToken(
            $name,
            $extends,
            $implements,
            $attributes,
            $properties,
            $methods,
            $flags,
            $generics,
        );

        return $export
            ? new ExportCodeToken($class)
            : $class;
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

    private function parseExtends(LexicalStream $stream, ?string $file): ?CodeToken
    {
        if ($this->isKeyword($stream, 'extends')) {
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            return $this->referenceParseSpecification->parse($stream, $file);
        }

        return null;
    }

    /**
     * @return array<CodeToken>
     */
    private function parseImplements(LexicalStream $stream, ?string $file): array
    {
        $implements = [];

        if ($this->isKeyword($stream, 'implements')) {
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            do {
                $implements[] = $this->referenceParseSpecification->parse($stream, $file);
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                    break;
                }

                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } while (true);
        }

        return $implements;
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseClassPart(LexicalStream $stream, ?string $file): CodeToken
    {
        $attributes = $this->parseAttributes($stream, $file);
        $flags = 0;

        if ($this->isKeyword($stream, 'public')) {
            $visibility = Visibility::Public;
            $stream->next();
        } elseif ($this->isKeyword($stream, 'protected')) {
            $visibility = Visibility::Protected;
            $stream->next();
        } elseif ($this->isKeyword($stream, 'private')) {
            $visibility = Visibility::Private;
            $stream->next();
        } else {
            $visibility = Visibility::Public;
        }

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isKeyword($stream, 'static')) {
            $flags |= AbstractClassPart::FLAG_STATIC;
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        if ($this->isKeyword($stream, 'override')) {
            $flags |= AbstractClassPart::FLAG_OVERRIDE;
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, LabelLexical::TYPE);
        $name = (string)$stream->current();
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return $this->isLexical($stream, ParenthesisLeftLexical::TYPE) || $this->isLexical($stream, LowerThanLexical::TYPE)
            ? $this->parseClassPartMethod($stream, $visibility, $name, $attributes, $flags, $file)
            : $this->parseClassPartProperty($stream, $visibility, $name, $attributes, $flags, $file);
    }

    /**
     * @param array<AttributeCodeToken> $attributes
     *
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseClassPartMethod(
        LexicalStream $stream,
        Visibility $visibility,
        string $name,
        array $attributes,
        int $flags,
        ?string $file,
    ): CodeToken {
        if ($this->isLexical($stream, LowerThanLexical::TYPE)) {
            $this->parseGenerics($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $parameters = [];

        while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
            $parameterAttributes = $this->parseAttributes($stream, $file);

            if ($this->isKeyword($stream, 'public')) {
                $parameterVisibility = Visibility::Public;
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } elseif ($this->isKeyword($stream, 'protected')) {
                $parameterVisibility = Visibility::Protected;
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } elseif ($this->isKeyword($stream, 'private')) {
                $parameterVisibility = Visibility::Private;
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } else {
                $parameterVisibility = null;
            }

            $this->expectLexical($stream, LabelLexical::TYPE);
            $parameterName = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($this->isLexical($stream, ColonLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $hint = $this->hintParseSpecification->parse($stream, $file);

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } elseif ($this->isLexical($stream, QuestionMarkLexical::TYPE)) {
                $stream->next();

                $this->expectLexical($stream, ColonLexical::TYPE);
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $hint = $this->hintParseSpecification->parse($stream, $file);
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } else {
                $hint = null;
            }

            if ($this->isLexical($stream, EqualsSignLexical::TYPE)) {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $assigned = $this->expressionParseSpecification->parse($stream, $file);

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } else {
                $assigned = null;
            }

            if ($parameterVisibility) {
                $parameters[] = new ClassPropertyCodeToken(
                    $parameterVisibility,
                    $parameterName,
                    $hint,
                    $assigned,
                    attributes: $parameterAttributes,
                );
            } else {
                $parameters[] = new ParameterCodeToken(
                    $parameterName,
                    $hint,
                    $assigned,
                    $parameterAttributes,
                );
            }

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
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($this->isKeyword($stream, 'is')) {
                $returns = BuiltInCodeToken::Boolean;

                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $this->hintParseSpecification->parse($stream, $file);
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            }
        } else {
            $returns = null;
        }

        return new ClassMethodCodeToken(
            $visibility,
            $name,
            $parameters,
            $returns,
            $this->parseClassPartMethodBody($stream),
            $flags,
            $attributes,
        );
    }

    /**
     * Currently the method body is just ignored
     *
     * @return array<CodeToken>
     *
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseClassPartMethodBody(LexicalStream $stream): array
    {
        if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
            $stream->next();

            return [];
        }

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

        return [];
    }

    /**
     * @param array<AttributeCodeToken> $attributes
     *
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseClassPartProperty(
        LexicalStream $stream,
        Visibility $visibility,
        string $name,
        array $attributes,
        int $flags,
        ?string $file,
    ): CodeToken {
        if ($name === 'get' && $this->isLexical($stream, LabelLexical::TYPE)) {
            return $this->parseClassPartGetProperty($stream, $visibility, $attributes, $flags, $file);
        }

        if ($name === 'set' && $this->isLexical($stream, LabelLexical::TYPE)) {
            return $this->parseClassPartSetProperty($stream, $visibility, $attributes, $flags);
        }

        if ($this->isLexical($stream, QuestionMarkLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $flags |= ClassPropertyCodeToken::FLAG_OPTIONAL;
        }

        if ($this->isLexical($stream, ColonLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $hint = $this->hintParseSpecification->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
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

        if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return new ClassPropertyCodeToken(
            $visibility,
            $name,
            $hint,
            $assigned,
            $flags,
            $attributes,
        );
    }

    /**
     * @param array<AttributeCodeToken> $attributes
     *
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseClassPartGetProperty(
        LexicalStream $stream,
        Visibility $visibility,
        array $attributes,
        int $flags,
        ?string $file,
    ): CodeToken {
        $name = (string)$stream->current();
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        // Ignore parameters for now
        while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
            $stream->next();
        }

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, ColonLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $hint = $this->hintParseSpecification->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        } else {
            $hint = null;
        }

        $this->parseClassPartMethodBody($stream);
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return new ClassGetPropertyCodeToken(
            $visibility,
            $name,
            $hint,
            flags: $flags,
            attributes: $attributes,
        );
    }

    /**
     * @param array<AttributeCodeToken> $attributes
     *
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseClassPartSetProperty(
        LexicalStream $stream,
        Visibility $visibility,
        array $attributes,
        int $flags,
    ): CodeToken {
        $name = (string)$stream->current();
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, LabelLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->expectLexical($stream, ColonLexical::TYPE);
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $hint = $this->hintParseSpecification->parse($stream);
        } else {
            $hint = null;
        }


        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->parseClassPartMethodBody($stream);
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return new ClassSetPropertyCodeToken(
            $visibility,
            $name,
            $hint,
            flags: $flags,
            attributes: $attributes,
        );
    }

    /**
     * @return array<AttributeCodeToken>
     *
     * @throws UnexpectedCodeToken
     */
    private function parseAttributes(LexicalStream $stream, ?string $file): array
    {
        $attributeParser = $this->attributesParseSpecification;
        $attributes = [];

        while ($stream->isActive() && $attributeParser->isSatisfiedBy($stream)) {
            $attribute = $attributeParser->parse($stream, $file);

            if (!$attribute instanceof AttributeCodeToken) {
                throw new UnexpectedCodeToken();
            }

            $attributes[] = $attribute;
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        return $attributes;
    }
}
