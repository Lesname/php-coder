<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use RuntimeException;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Hint\UnionCodeToken;
use LesCoder\Token\Value\FloatCodeToken;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Hint\GenericCodeToken;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Hint\DictionaryCodeToken;
use LesCoder\Token\Expression\GroupCodeToken;
use LesCoder\Token\Value\CollectionCodeToken;
use LesCoder\Token\Hint\IntersectionCodeToken;
use LesCoder\Token\Hint\IndexSignatureCodeToken;
use LesCoder\Token\Expression\TypeGuardCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Token\Value\AnonymousFunctionCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DotLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PipeLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\MinusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\AmpersandLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

final class HintParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    public function __construct(
        private readonly ParseSpecification $referenceParseSpecification,
    ) {
    }

    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return in_array(
            $stream->current()->getType(),
            [
                MinusLexical::TYPE,
                DotLexical::TYPE,
                IntegerLexical::TYPE,
                StringLexical::TYPE,
                LabelLexical::TYPE,
                ParenthesisLeftLexical::TYPE,
                CurlyBracketLeftLexical::TYPE,
                SquareBracketLeftLexical::TYPE,
            ],
            true,
        );
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        $mode = null;
        $hints = [];

        do {
            $hints[] = $this->parseHint($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($stream->isEnd()) {
                break;
            }

            if ($this->isLexical($stream, AmpersandLexical::TYPE)) {
                if ($mode === 'union') {
                    throw new RuntimeException();
                }

                $mode = 'intersection';
            } elseif ($this->isLexical($stream, PipeLexical::TYPE)) {
                if ($mode === 'intersection') {
                    throw new RuntimeException();
                }

                $mode = 'union';
            } else {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        } while ($stream->isActive());

        if (count($hints) === 1) {
            return $hints[0];
        }

        return match ($mode) {
            'union' => new UnionCodeToken($hints),
            'intersection' => new IntersectionCodeToken($hints),
            default => throw new RuntimeException("Cannot handle mode '{$mode}'"),
        };
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     *
     * @todo support generic
     */
    private function parseHint(LexicalStream $stream, ?string $file): CodeToken
    {
        $hint = match ($stream->current()->getType()) {
            MinusLexical::TYPE,
            DotLexical::TYPE,
            IntegerLexical::TYPE => $this->parseHintNumber($stream),
            StringLexical::TYPE => $this->parseHintString($stream),
            LabelLexical::TYPE => $this->parseHintLabel($stream, $file),
            ParenthesisLeftLexical::TYPE => $this->parseHintParenthesis($stream, $file),
            CurlyBracketLeftLexical::TYPE => $this->parseHintCurlyBracket($stream, $file),
            SquareBracketLeftLexical::TYPE => $this->parseHintSquareBracket($stream, $file),
            default => (function () use ($stream) {
                $current = $stream->current();

                throw new RuntimeException("Unexpected '{$current->getType()}'");
            })(),
        };

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        while ($stream->isActive()) {
            if ($this->isLexical($stream, LowerThanLexical::TYPE)) {
                $hint = $this->parseGeneric($stream, $hint, $file);

                continue;
            }

            if ($this->isLexical($stream, SquareBracketLeftLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                if ($this->isLexical($stream, LabelLexical::TYPE)) {
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    $this->expectLexical($stream, SquareBracketRightLexical::TYPE);

                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                } else {
                    $this->expectLexical($stream, SquareBracketRightLexical::TYPE);
                    $stream->next();

                    $hint = new GenericCodeToken(
                        BuiltInCodeToken::Collection,
                        [$hint],
                    );
                }

                continue;
            }

            break;
        }

        return $hint;
    }

    private function parseHintLabel(LexicalStream $stream, ?string $file): CodeToken
    {
        $current = $stream->current();
        $label = (string)$current;

        if ($label === 'readonly') {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            return $this->parse($stream, $file);
        }

        if ($label === 'object') {
            $stream->next();
            return BuiltInCodeToken::Dictionary;
        }

        if ($label === 'Object') {
            $stream->next();
            // In typescript Object is anything
            return BuiltInCodeToken::Any;
        }

        if ($label === 'Array') {
            $stream->next();
            return BuiltInCodeToken::Collection;
        }

        // @todo fix keyof support
        if ($label === 'keyof') {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->parse($stream, $file);

            return BuiltInCodeToken::Any;
        }

        // @todo fix typeof support
        if ($label === 'typeof') {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $this->parse($stream, $file);

            return BuiltInCodeToken::Any;
        }

        $token = match (strtolower($label)) {
            'any' => BuiltInCodeToken::Any,
            'bool' => BuiltInCodeToken::Boolean,
            'false' => BuiltInCodeToken::False,
            'number' => new UnionCodeToken([BuiltInCodeToken::Float, BuiltInCodeToken::Integer]),
            'null' => BuiltInCodeToken::Null,
            'never' => BuiltInCodeToken::Never,
            'string' => BuiltInCodeToken::String,
            'true' => BuiltInCodeToken::True,
            'void' => BuiltInCodeToken::Void,
            default => null,
        };

        if ($token === null) {
            return $this->parseHintReference($stream, $file);
        } else {
            $stream->next();
        }

        return $token;
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseHintReference(LexicalStream $stream, ?string $file): CodeToken
    {
        $hint = $this->referenceParseSpecification->parse($stream, $file);

        while ($stream->isActive()) {
            if ($this->isLexical($stream, LowerThanLexical::TYPE)) {
                $stream->next();

                throw new RuntimeException('@todo support generic');
            }

            if ($this->isLexical($stream, DotLexical::TYPE)) {
                $stream->next();

                $this->expectLexical($stream, LabelLexical::TYPE);
                $access = (string)$stream->current();
                $stream->next();

                $hint = new AccessCodeToken(
                    $hint,
                    new StringCodeToken($access),
                );
            }

            break;
        }

        return $hint;
    }

    /**
     * @throws UnexpectedLexical
     */
    private function parseHintNumber(LexicalStream $stream): CodeToken
    {
        if ($stream->current()->getType() === MinusLexical::TYPE) {
            $modifier = -1;
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        } else {
            $modifier = 1;
        }

        if ($stream->isActive() && $stream->current()->getType() === IntegerLexical::TYPE) {
            $base = (string)$stream->current();
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        if ($stream->isActive() && $stream->current()->getType() === DotLexical::TYPE) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($stream->isActive() && $stream->current()->getType() === IntegerLexical::TYPE) {
                $decimals = (string)$stream->current();
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } else {
                $decimals = '0';
            }

            $base ??= '0';

            return new FloatCodeToken(((float)"{$base}.{$decimals}") * $modifier);
        } elseif (!isset($base)) {
            throw new UnexpectedLexical($stream->current(), IntegerLexical::TYPE, DotLexical::TYPE);
        }

        return new IntegerCodeToken((int)$base * $modifier);
    }

    private function parseHintString(LexicalStream $stream): CodeToken
    {
        $lexical = $stream->current();
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new StringCodeToken(substr((string)$lexical, 1, -1));
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     *
     * @todo support anonymous function
     */
    private function parseHintParenthesis(LexicalStream $stream, ?string $file): CodeToken
    {
        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            return $this->parseHintAnonymousFunction($stream, [], $file);
        }

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        $sub = $this->parse($stream, $file);

        if ($this->isLexical($stream, ColonLexical::TYPE)) {
            if (!$sub instanceof ReferenceCodeToken) {
                throw new RuntimeException();
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $hint = $this->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $parameters = [
                new ParameterCodeToken(
                    $sub->name,
                    $hint,
                ),
            ];

            while ($this->isLexical($stream, CommaLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $this->expectLexical($stream, LabelLexical::TYPE);
                $parameterName = (string)$stream->current();
                $stream->next();

                if ($this->isLexical($stream, ColonLexical::TYPE)) {
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    $hint = $this->parse($stream);
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                } else {
                    $hint = null;
                }

                $parameters[] = new ParameterCodeToken($parameterName, $hint);
            }

            $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            return $this->parseHintAnonymousFunction($stream, $parameters, $file);
        }

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new GroupCodeToken($sub);
    }

    /**
     * @param array<ParameterCodeToken> $parameters
     */
    private function parseHintAnonymousFunction(LexicalStream $stream, array $parameters, ?string $file): CodeToken
    {
        $this->expectLexical($stream, EqualsSignLexical::TYPE);
        $stream->next();

        $this->expectLexical($stream, GreaterThanLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $returns = $this->parse($stream, $file);

        if ($returns instanceof ReferenceCodeToken && $returns->from === null && $this->isKeyword($stream, 'is')) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $hint = $this->parse($stream);

            $returns = new TypeGuardCodeToken(
                $returns->name,
                $hint,
            );
        }

        return new AnonymousFunctionCodeToken(
            $parameters,
            $returns,
        );
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseHintCurlyBracket(LexicalStream $stream, ?string $file): CodeToken
    {
        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $items = [];

        while ($stream->isActive() && $stream->current()->getType() !== CurlyBracketRightLexical::TYPE) {
            if ($this->isLexical($stream, LabelLexical::TYPE) || $this->isLexical($stream, StringLexical::TYPE)) {
                $key = new StringCodeToken((string)$stream->current());
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } elseif ($this->isLexical($stream, SquareBracketLeftLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $this->expectLexical($stream, LabelLexical::TYPE);
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                if ($this->isKeyword($stream, 'in')) {
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    if ($this->isKeyword($stream, 'keyof')) {
                        $this->expectKeyword($stream, 'keyof');
                        $stream->next();
                        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                    }

                    $this->expectLexical($stream, LabelLexical::TYPE);
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    // @todo fix type
                    $hint = BuiltInCodeToken::Any;
                } else {
                    $this->expectLexical($stream, ColonLexical::TYPE);
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    $hint = $this->parseHint($stream, $file);
                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                }

                $this->expectLexical($stream, SquareBracketRightLexical::TYPE);
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $key = new IndexSignatureCodeToken($hint);
            } else {
                throw new UnexpectedLexical($stream->current(), LabelLexical::TYPE);
            }

            if ($stream->isEnd()) {
                throw new UnexpectedEnd(ColonLexical::TYPE, QuestionMarkLexical::TYPE);
            }

            if ($stream->current()->getType() === QuestionMarkLexical::TYPE) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                $required = false;
            } else {
                $required = true;
            }

            $this->expectLexical($stream, ColonLexical::TYPE);
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $value = $this->parse($stream, $file);

            $items[] = [
                'required' => $required,
                'value' => $value,
                'key' => $key,
            ];

            if (!($this->isLexical($stream, CommaLexical::TYPE) || $this->isLexical($stream, SemicolonLexical::TYPE))) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new DictionaryCodeToken($items);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseHintSquareBracket(LexicalStream $stream, ?string $file): CodeToken
    {
        $this->expectLexical($stream, SquareBracketLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        $items = [];

        while ($stream->isActive() && $stream->current()->getType() !== SquareBracketRightLexical::TYPE) {
            $items[] = $this->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            // Item is optional
            if ($this->isLexical($stream, QuestionMarkLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            }

            if ($stream->current()->getType() !== CommaLexical::TYPE) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, SquareBracketRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new CollectionCodeToken($items);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseGeneric(LexicalStream $stream, CodeToken $base, ?string $file): CodeToken
    {
        $this->expectLexical($stream, LowerThanLexical::TYPE);
        $stream->next();

        $parameters = [];

        while ($stream->isActive() && !$this->isLexical($stream, GreaterThanLexical::TYPE)) {
            $parameters[] = $this->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        $this->expectLexical($stream, GreaterThanLexical::TYPE);
        $stream->next();

        return new GenericCodeToken($base, $parameters);
    }
}
