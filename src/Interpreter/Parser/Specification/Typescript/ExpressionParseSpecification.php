<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\InvokeCodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Value\FloatCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\Dictionary\Item;
use LesCoder\Token\Hint\GenericCodeToken;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Token\Value\BuiltInCodeToken;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Expression\OrCodeToken;
use LesCoder\Token\Expression\AndCodeToken;
use LesCoder\Token\Expression\MathOperator;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\InitiateCodeToken;
use LesCoder\Token\Expression\GroupCodeToken;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Token\Value\CollectionCodeToken;
use LesCoder\Token\Expression\TernaryCodeToken;
use LesCoder\Token\Expression\DowncastCodeToken;
use LesCoder\Token\Expression\ComparisonOperator;
use LesCoder\Token\Expression\CoalescingCodeToken;
use LesCoder\Token\Expression\ComparisonCodeToken;
use LesCoder\Token\Expression\CalculationCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Token\Value\AnonymousFunctionCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DotLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\OrLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\AndLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PlusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\MinusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Character\AsteriskLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\CoalescingLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\SameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\EqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotSameLexical;
use LesCoder\Interpreter\Parser\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotEqualsLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\LowerThanOrEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\GreaterThanOrEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

final class ExpressionParseSpecification implements ParseSpecification
{
    use ExpectParseSpecificationHelper;

    /**
     * @param array<string, string> $imports
     */
    public function __construct(
        private readonly ParseSpecification $referenceParseSpecification,
        private readonly ParseSpecification $hintParseSpecification,
        private readonly array $imports,
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
        $value = $this->parseValue($stream, $file);

        while ($stream->isActive()) {
            if ($stream->current()->getType() === CoalescingLexical::TYPE) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                $value = new CoalescingCodeToken([$value, $this->parse($stream, $file)]);

                continue;
            }

            if ($stream->current()->getType() === OrLexical::TYPE) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                $value = new OrCodeToken([$value, $this->parse($stream, $file)]);

                continue;
            }

            if ($stream->current()->getType() === AndLexical::TYPE) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                $value = new AndCodeToken([$value, $this->parse($stream, $file)]);

                continue;
            }

            if ($this->isMathOperator($stream)) {
                $operator = MathOperator::fromOperator((string)$stream->current());
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $value = new CalculationCodeToken($value, $this->parse($stream, $file), $operator);

                continue;
            }

            if ($this->isComparisonOperator($stream)) {
                $operator = ComparisonOperator::fromOperator((string)$stream->current());
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $value = new ComparisonCodeToken($value, $this->parse($stream, $file), $operator);

                continue;
            }

            if ($stream->current()->getType() === QuestionMarkLexical::TYPE) {
                $value = $this->parseTernary($stream, $value, $file);

                continue;
            }

            if ($stream->current()->getType() === LabelLexical::TYPE) {
                if ((string)$stream->current() === 'as') {
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                    $as = $this->parseValue($stream, $file);

                    $value = new DowncastCodeToken(
                        $value,
                        $as,
                    );

                    continue;
                }
            }

            break;
        }

        return $value;
    }

    private function isMathOperator(LexicalStream $stream): bool
    {
        return in_array(
            $stream->current()->getType(),
            [
                ForwardSlashLexical::TYPE,
                MinusLexical::TYPE,
                PlusLexical::TYPE,
                AsteriskLexical::TYPE,
            ],
            true,
        );
    }

    private function isComparisonOperator(LexicalStream $stream): bool
    {
        return in_array(
            $stream->current()->getType(),
            [
                EqualsLexical::TYPE,
                SameLexical::TYPE,
                NotEqualsLexical::TYPE,
                NotSameLexical::TYPE,
                GreaterThanLexical::TYPE,
                GreaterThanOrEqualsLexical::TYPE,
                LowerThanLexical::TYPE,
                LowerThanOrEqualsLexical::TYPE,
            ],
            true,
        );
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseTernary(LexicalStream $stream, CodeToken $expression, ?string $file): CodeToken
    {
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $truthy = $this->parse($stream);

        if ($stream->isEnd()) {
            throw new UnexpectedEnd(ColonLexical::TYPE);
        }

        if ($stream->current()->getType() !== ColonLexical::TYPE) {
            throw new UnexpectedLexical($stream->current(), ColonLexical::TYPE);
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new TernaryCodeToken($expression, $truthy, $this->parse($stream, $file));
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseValue(LexicalStream $stream, ?string $file): CodeToken
    {
        $value = match ($stream->current()->getType()) {
            MinusLexical::TYPE,
            DotLexical::TYPE,
            IntegerLexical::TYPE => $this->parseValueNumber($stream),
            StringLexical::TYPE => $this->parseValueString($stream),
            LabelLexical::TYPE => $this->parseValueLabel($stream, $file),
            ParenthesisLeftLexical::TYPE => $this->parseValueParenthesis($stream, $file),
            CurlyBracketLeftLexical::TYPE => $this->parseValueCurlyBracket($stream, $file),
            SquareBracketLeftLexical::TYPE => $this->parseValueSquareBracket($stream, $file),
            default => (function () use ($stream) {
                throw new UnexpectedLexical(
                    $stream->current(),
                    MinusLexical::TYPE,
                    DotLexical::TYPE,
                    IntegerLexical::TYPE,
                    StringLexical::TYPE,
                    LabelLexical::TYPE,
                    ParenthesisLeftLexical::TYPE,
                    CurlyBracketLeftLexical::TYPE,
                    SquareBracketLeftLexical::TYPE,
                );
            })(),
        };

        while ($stream->isActive()) {
            if ($stream->current()->getType() === SquareBracketLeftLexical::TYPE) {
                $value = $this->parseDynamicAccess($stream, $value, $file);

                continue;
            }

            if ($stream->current()->getType() === DotLexical::TYPE) {
                $value = $this->parseDotAccess($stream, $value);

                continue;
            }

            if ($stream->current()->getType() === ParenthesisLeftLexical::TYPE) {
                $value = $this->parseInvoke($stream, $value, $file);

                continue;
            }

            if ($value instanceof VariableCodeToken && $this->isLexical($stream, EqualsSignLexical::TYPE)) {
                $this->expectLexical($stream, EqualsSignLexical::TYPE);
                $stream->next();

                $this->expectLexical($stream, GreaterThanLexical::TYPE);
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                $expression = $this->parse($stream, $file);

                $value = new AnonymousFunctionCodeToken(
                    [new ParameterCodeToken($value->name)],
                    body: [$expression],
                );

                continue;
            }

            break;
        }

        return $value;
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseDynamicAccess(LexicalStream $stream, CodeToken $value, ?string $file): CodeToken
    {
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $key = $this->parse($stream, $file);

        if ($stream->isEnd()) {
            throw new UnexpectedEnd(SquareBracketRightLexical::TYPE);
        }

        if ($stream->current()->getType() !== SquareBracketRightLexical::TYPE) {
            throw new UnexpectedLexical($stream->current(), SquareBracketRightLexical::TYPE);
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new AccessCodeToken($value, $key);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseDotAccess(LexicalStream $stream, CodeToken $value): CodeToken
    {
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);

        $key = new StringCodeToken((string)$stream->current());
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new AccessCodeToken($value, $key);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseInvoke(LexicalStream $stream, CodeToken $value, ?string $file): CodeToken
    {
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        $parameters = [];

        while ($stream->isActive() && $stream->current()->getType() !== ParenthesisRightLexical::TYPE) {
            $parameters[] = $this->parse($stream, $file);

            if ($stream->isEnd() || $stream->current()->getType() !== CommaLexical::TYPE) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        if ($stream->isEnd()) {
            throw new UnexpectedEnd(ParenthesisRightLexical::TYPE);
        }

        if ($stream->current()->getType() !== ParenthesisRightLexical::TYPE) {
            throw new UnexpectedLexical($stream->current(), ParenthesisRightLexical::TYPE);
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new InvokeCodeToken($value, $parameters);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseValueNumber(LexicalStream $stream): CodeToken
    {
        if ($stream->current()->getType() === MinusLexical::TYPE) {
            $base = '-';
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        } else {
            $base = '';
        }

        $this->expectLexical($stream, IntegerLexical::TYPE, DotLexical::TYPE);

        if ($this->isLexical($stream, IntegerLexical::TYPE)) {
            $base .= (string)$stream->current();
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        if ($this->isLexical($stream, DotLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            if ($stream->isActive() && $stream->current()->getType() === IntegerLexical::TYPE) {
                $decimals = (string)$stream->current();
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            } else {
                $decimals = '0';
            }

            return new FloatCodeToken((float)"{$base}.{$decimals}");
        }

        return new IntegerCodeToken((int)$base);
    }

    private function parseValueString(LexicalStream $stream): CodeToken
    {
        $lexical = $stream->current();
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new StringCodeToken((string)$lexical);
    }

    private function parseValueLabel(LexicalStream $stream, ?string $file): CodeToken
    {
        $label = (string)$stream->current();

        if ($label === 'new') {
            return $this->parseValueInitialization($stream, $file);
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return match (strtolower($label)) {
            'false' => BuiltInCodeToken::False,
            'true' => BuiltInCodeToken::True,
            'null' => BuiltInCodeToken::Null,
            'parent' => BuiltInCodeToken::Parent,
            default => (function () use ($label, $stream) {
                $codeToken = isset($this->imports[$label])
                    ? new ReferenceCodeToken($label, $this->imports[$label])
                    : new VariableCodeToken($label);

                while ($stream->isActive()) {
                    if (!$codeToken instanceof VariableCodeToken && $stream->current()->getType() === LowerThanLexical::TYPE) {
                        $stream->next();
                        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                        $generics = [];

                        while ($stream->isActive() && !$this->isLexical($stream, GreaterThanLexical::TYPE)) {
                            $generics[] = $this->hintParseSpecification->parse($stream);
                            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                            /** @phpstan-ignore booleanAnd.leftAlwaysTrue */
                            if ($stream->isActive() && $stream->current()->getType() !== CommaLexical::TYPE) {
                                break;
                            }

                            $stream->next();
                            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
                        }

                        $this->expectLexical($stream, GreaterThanLexical::TYPE);
                        $stream->next();
                        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                        $codeToken = new GenericCodeToken($codeToken, $generics);
                    } elseif ($stream->current()->getType() === ParenthesisLeftLexical::TYPE) {
                        $stream->next();
                        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                        $parameters = [];

                        while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
                            $parameters[] = $this->parse($stream);
                            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                            /** @phpstan-ignore booleanAnd.leftAlwaysTrue */
                            if ($stream->isActive() && $stream->current()->getType() !== CommaLexical::TYPE) {
                                break;
                            }

                            $stream->next();
                            $stream->skip(WhitespaceLexical::TYPE, ParenthesisRightLexical::TYPE);
                        }

                        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
                        $stream->next();
                        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                        $codeToken = new InvokeCodeToken($codeToken, $parameters);
                    } elseif ($stream->current()->getType() === DotLexical::TYPE) {
                        $codeToken = $this->parseDotAccess($stream, $codeToken);
                    } else {
                        break;
                    }
                }


                return $codeToken;
            })(),
        };
    }

    private function parseValueInitialization(LexicalStream $stream, ?string $file): CodeToken
    {
        $this->expectKeyword($stream, 'new');
        $stream->next();

        $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);

        $initiated = $this->referenceParseSpecification->parse($stream, $file);
        $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);

        $parameters = [];

        if ($this->isLexical($stream, ParenthesisLeftLexical::TYPE)) {
            $stream->next();
            $stream->skip(CommentLexical::TYPE, WhitespaceLexical::TYPE);

            while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
                $parameters[] = $this->parse($stream, $file);
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

                if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                    break;
                }

                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
            }

            $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
            $stream->next();
        }

        return new InitiateCodeToken($initiated, $parameters);
    }

    private function parseValueParenthesis(LexicalStream $stream, ?string $file): CodeToken
    {
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        // Anonymous function without parameters
        if ($this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            return $this->parseValueAnonymousFunction($stream, [], $file);
        }

        $sub = $this->parse($stream, $file);

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, ColonLexical::TYPE, EqualsSignLexical::TYPE)) {
            if (!$sub instanceof VariableCodeToken) {
                $type = get_debug_type($sub);

                throw new UnexpectedCodeToken();
            }

            return $this->parseValueAnonymousFunction($stream, [new ParameterCodeToken($sub->name)], $file);
        }

        return new GroupCodeToken($sub);
    }

    /**
     * @param array<ParameterCodeToken> $parameters
     */
    private function parseValueAnonymousFunction(LexicalStream $stream, array $parameters, ?string $file): CodeToken
    {
        if ($this->isLexical($stream, ColonLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $returns = $this->hintParseSpecification->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        } else {
            $returns = null;
        }

        $this->expectLexical($stream, EqualsSignLexical::TYPE);
        $stream->next();
        $this->expectLexical($stream, GreaterThanLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        if ($this->isLexical($stream, CurlyBracketLeftLexical::TYPE)) {
            $body = $this->parseValueAnonymousFunctionBody($stream);
        } else {
            $body = [$this->parse($stream, $file)];
        }

        return new AnonymousFunctionCodeToken(
            $parameters,
            $returns,
            $body,
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
    private function parseValueAnonymousFunctionBody(LexicalStream $stream): array
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
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseValueCurlyBracket(LexicalStream $stream, ?string $file): CodeToken
    {
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

            $this->expectLexical($stream, ColonLexical::TYPE);

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $value = $this->parse($stream, $file);
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

            $items[] = new Item($key, $value);

            if ($stream->current()->getType() !== CommaLexical::TYPE) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        if ($stream->isEnd()) {
            throw new UnexpectedEnd(CurlyBracketRightLexical::TYPE);
        }

        if ($stream->current()->getType() !== CurlyBracketRightLexical::TYPE) {
            throw new UnexpectedLexical($stream->current(), CurlyBracketRightLexical::TYPE);
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new DictionaryCodeToken($items);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseValueSquareBracket(LexicalStream $stream, ?string $file): CodeToken
    {
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        $items = [];

        while ($stream->isActive() && $stream->current()->getType() !== SquareBracketRightLexical::TYPE) {
            $items[] = $this->parse($stream, $file);

            if ($stream->current()->getType() !== CommaLexical::TYPE) {
                break;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);
        }

        if ($stream->isEnd()) {
            throw new UnexpectedEnd(SquareBracketRightLexical::TYPE);
        }

        if ($stream->current()->getType() !== SquareBracketRightLexical::TYPE) {
            throw new UnexpectedLexical($stream->current(), SquareBracketRightLexical::TYPE);
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE, CommentLexical::TYPE);

        return new CollectionCodeToken($items);
    }
}
