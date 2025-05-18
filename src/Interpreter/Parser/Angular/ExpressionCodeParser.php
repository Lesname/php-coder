<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Angular;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\InvokeCodeToken;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Token\Value\FloatCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\Dictionary\Item;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Token\Value\BuiltInCodeToken;
use LesCoder\Token\Value\IntegerCodeToken;
use LesCoder\Token\Expression\OrCodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Stream\Exception\EndOfStream;
use LesCoder\Token\Expression\MathOperator;
use LesCoder\Token\Expression\AndCodeToken;
use LesCoder\Interpreter\Parser\CodeParser;
use LesCoder\Token\Expression\GroupCodeToken;
use LesCoder\Token\Value\CollectionCodeToken;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Token\Expression\FilterCodeToken;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Token\Expression\TernaryCodeToken;
use LesCoder\Token\Expression\ComparisonOperator;
use LesCoder\Token\Expression\ComparisonCodeToken;
use LesCoder\Token\Expression\CoalescingCodeToken;
use LesCoder\Token\Expression\CalculationCodeToken;
use LesCoder\Stream\CodeToken\ArrayCodeTokenStream;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DotLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\OrLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PlusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\AndLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PipeLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\MinusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Parser\Angular\Exception\InvalidName;
use LesCoder\Interpreter\Parser\Angular\Exception\StreamActive;
use LesCoder\Interpreter\Lexer\Lexical\Character\AsteriskLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\CoalescingLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\SameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\EqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotSameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotEqualsLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLabel;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\LowerThanOrEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\GreaterThanOrEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

final class ExpressionCodeParser implements CodeParser
{
    use ExpectParseSpecificationHelper;

    private const int PRECEDENCE_ALL = 9;
    private const int PRECEDENCE_ACCESS = 0;
    private const int PRECEDENCE_OPERATORS = 5;
    private const int PRECEDENCE_CHAIN = 7;

    private const COMPARISON_TYPES = [
        EqualsLexical::TYPE,
        NotEqualsLexical::TYPE,
        SameLexical::TYPE,
        NotSameLexical::TYPE,

        GreaterThanLexical::TYPE,
        GreaterThanOrEqualsLexical::TYPE,

        LowerThanLexical::TYPE,
        LowerThanOrEqualsLexical::TYPE,
    ];

    #[Override]
    public function parse(LexicalStream $stream, ?string $file): CodeTokenStream
    {
        $token = $this->parseExpression($stream);
        $stream->skip(WhitespaceLexical::TYPE);

        if ($stream->isActive()) {
            throw new StreamActive();
        }

        return new ArrayCodeTokenStream([$token]);
    }

    private function parseExpression(LexicalStream $stream, int $precedence = self::PRECEDENCE_ALL): CodeToken
    {
        $expression = $this->parseValue($stream);
        $stream->skip(WhitespaceLexical::TYPE);

        while ($stream->isActive()) {
            if ($precedence >= self::PRECEDENCE_ACCESS) {
                if ($this->isLexical($stream, SquareBracketLeftLexical::TYPE)) {
                    $stream->next();
                    $stream->skip(WhitespaceLexical::TYPE);

                    $part = $this->parseExpression($stream);

                    $stream->skip(WhitespaceLexical::TYPE);

                    $this->expectLexical($stream, SquareBracketRightLexical::TYPE);
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE);

                    $expression = new AccessCodeToken($expression, $part);

                    continue;
                }

                if ($this->isLexical($stream, DotLexical::TYPE)) {
                    $stream->next();

                    $expression = $this->parseAccess($stream, $expression, false);
                    $stream->skip(WhitespaceLexical::TYPE);

                    continue;
                }
            }

            if ($precedence >= self::PRECEDENCE_OPERATORS) {
                if ($this->isLexical($stream, MinusLexical::TYPE, PlusLexical::TYPE, AsteriskLexical::TYPE, ForwardSlashLexical::TYPE)) {
                    $expression = $this->parseCalculation($stream, $expression);
                    $stream->skip(WhitespaceLexical::TYPE);

                    continue;
                }

                if ($this->isLexical($stream, ...self::COMPARISON_TYPES)) {
                    $expression = $this->parseComparison($stream, $expression);
                    $stream->skip(WhitespaceLexical::TYPE);

                    continue;
                }
            }

            if ($precedence >= self::PRECEDENCE_CHAIN) {
                if ($this->isLexical($stream, CoalescingLexical::TYPE)) {
                    $expression = $this->parseCoalesce($stream, $expression);
                    $stream->skip(WhitespaceLexical::TYPE);

                    continue;
                }

                if ($this->isLexical($stream, OrLexical::TYPE, AndLexical::TYPE)) {
                    $expression = $this->parseChain($stream, $expression);
                    $stream->skip(WhitespaceLexical::TYPE);

                    continue;
                }

                if ($this->isLexical($stream, QuestionMarkLexical::TYPE)) {
                    $stream->next();

                    if ($this->isLexical($stream, DotLexical::TYPE)) {
                        $stream->next();

                        $expression = $this->parseAccess($stream, $expression, true);
                        $stream->skip(WhitespaceLexical::TYPE);

                        continue;
                    }

                    $stream->skip(WhitespaceLexical::TYPE);

                    $expression = $this->parseTernary($stream, $expression);
                    $stream->skip(WhitespaceLexical::TYPE);

                    continue;
                }
            }

            if ($precedence >= self::PRECEDENCE_ALL) {
                if ($this->isLexical($stream, PipeLexical::TYPE)) {
                    $expression = $this->parseFilter($stream, $expression);
                    $stream->skip(WhitespaceLexical::TYPE);

                    continue;
                }
            }

            break;
        }

        return $expression;
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLabel
     * @throws UnexpectedLexical
     * @throws EndOfStream
     */
    private function parseValue(LexicalStream $stream): CodeToken
    {
        $lexical = $stream->current();

        if ($this->isLexical($stream, SquareBracketLeftLexical::TYPE)) {
            return $this->parseList($stream);
        }

        if ($this->isLexical($stream, CurlyBracketLeftLexical::TYPE)) {
            return $this->parseDict($stream);
        }

        if ($this->isLexical($stream, ParenthesisLeftLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE);

            $expression = new GroupCodeToken($this->parseExpression($stream));
            $stream->skip(WhitespaceLexical::TYPE);

            $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
            $stream->next();

            return $expression;
        }

        if ($this->isLexical($stream, MinusLexical::TYPE, IntegerLexical::TYPE)) {
            return $this->parseNumber($stream);
        }

        if ($this->isLexical($stream, StringLexical::TYPE)) {
            return $this->parseString($stream);
        }

        if ($lexical instanceof LabelLexical) {
            $builtin = [
                'false' => BuiltInCodeToken::False,
                'true' => BuiltInCodeToken::True,
                'null' => BuiltInCodeToken::Null,
            ];

            foreach ($builtin as $match => $token) {
                if ($this->isKeyword($stream, $match)) {
                    $this->expectKeyword($stream, $match);
                    $stream->next();

                    return $token;
                }
            }

            if (preg_match('/[a-zA-Z\x7f-\xff_$]/', (string)$lexical) === 1) {
                $codeToken = $this->parseVariable($stream);

                /** @phpstan-ignore ternary.alwaysFalse */
                return $this->isLexical($stream, ParenthesisLeftLexical::TYPE)
                    ? $this->parseInvoke($stream, $codeToken)
                    : $codeToken;
            }

            throw new InvalidName((string)$lexical);
        }

        throw new UnexpectedLexical(
            $lexical,
            SquareBracketLeftLexical::TYPE,
            CurlyBracketLeftLexical::TYPE,
            ParenthesisLeftLexical::TYPE,
            MinusLexical::TYPE,
            IntegerLexical::TYPE,
            StringLexical::TYPE,
        );
    }

    private function parseVariable(LexicalStream $stream): CodeToken
    {
        $name = (string)$stream->current();
        $stream->next();

        if (preg_match('/[a-zA-Z\x7f-\xff_$]/', $name) !== 1) {
            throw new InvalidName($name);
        }

        return new VariableCodeToken($name);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseString(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, StringLexical::TYPE);
        $text = (string)$stream->current();
        $stream->next();

        return new StringCodeToken($text);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseNumber(LexicalStream $stream): CodeToken
    {
        if ($this->isLexical($stream, MinusLexical::TYPE)) {
            $number = '-';
            $stream->next();
        } else {
            $number = '';
        }

        $this->expectLexical($stream, IntegerLexical::TYPE);

        $number .= (string)$stream->current();
        $stream->next();

        if ($this->isLexical($stream, DotLexical::TYPE)) {
            $stream->next();
            $number .= '.';

            $this->expectLexical($stream, IntegerLexical::TYPE);
            $number .= (string)$stream->current();
            $stream->next();

            return new FloatCodeToken((float)$number);
        }

        return new IntegerCodeToken((int)$number);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseList(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, SquareBracketLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $items = [];

        if (!$this->isLexical($stream, SquareBracketRightLexical::TYPE)) {
            $items[] = $this->parseExpression($stream);
            $stream->skip(WhitespaceLexical::TYPE);

            while ($stream->isActive() && $this->isLexical($stream, CommaLexical::TYPE)) {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                if (!$this->isLexical($stream, SquareBracketRightLexical::TYPE)) {
                    $items[] = $this->parseExpression($stream);
                    $stream->skip(WhitespaceLexical::TYPE);
                }
            }
        }

        $this->expectLexical($stream, SquareBracketRightLexical::TYPE);
        $stream->next();

        return new CollectionCodeToken($items);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseDict(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $items = [];

        if (!$this->isLexical($stream, CurlyBracketRightLexical::TYPE)) {
            $this->expectLexical($stream, LabelLexical::TYPE, StringLexical::TYPE);
            $key = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE);

            $this->expectLexical($stream, ColonLexical::TYPE);
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE);

            $value = $this->parseExpression($stream);
            $items[] = new Item(new StringCodeToken($key), $value);

            while ($stream->isActive() && $this->isLexical($stream, CommaLexical::TYPE)) {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                if (!$this->isLexical($stream, LabelLexical::TYPE)) {
                    break;
                }

                $this->expectLexical($stream, LabelLexical::TYPE);
                $key = (string)$stream->current();
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                $this->expectLexical($stream, ColonLexical::TYPE);
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                $value = $this->parseExpression($stream);
                $stream->skip(WhitespaceLexical::TYPE);

                $items[] = new Item(new StringCodeToken($key), $value);
            }
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        return new DictionaryCodeToken($items);
    }

    private function parseCalculation(LexicalStream $stream, CodeToken $left): CodeToken
    {
        $operator = (string)$stream->current();
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE);

        return new CalculationCodeToken(
            $left,
            $this->parseExpression($stream, self::PRECEDENCE_OPERATORS),
            MathOperator::fromOperator($operator),
        );
    }

    private function parseComparison(LexicalStream $stream, CodeToken $left): CodeToken
    {
        $operator = (string)$stream->current();
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        return new ComparisonCodeToken(
            $left,
            $this->parseExpression($stream, self::PRECEDENCE_OPERATORS),
            ComparisonOperator::fromOperator($operator),
        );
    }

    private function parseChain(LexicalStream $stream, CodeToken $left): CodeToken
    {
        $token = $stream->current();
        $items = [$left];

        while ($this->isLexical($stream, $token->getType())) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE);

            $items[] = $this->parseExpression($stream, self::PRECEDENCE_OPERATORS);
            $stream->skip(WhitespaceLexical::TYPE);
        }

        return match ($token->getType()) {
            AndLexical::TYPE => new AndCodeToken($items),
            OrLexical::TYPE => new OrCodeToken($items),
            default => throw new UnexpectedLexical($token, AndLexical::TYPE, OrLexical::TYPE),
        };
    }

    private function parseCoalesce(LexicalStream $stream, CodeToken $left): CodeToken
    {
        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE);

        $right = $this->parseExpression($stream);
        $stream->skip(WhitespaceLexical::TYPE);

        return new CoalescingCodeToken([$left, $right]);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseFilter(LexicalStream $stream, CodeToken $expression): CodeToken
    {
        $this->expectLexical($stream, PipeLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, LabelLexical::TYPE);
        $name = (string)$stream->current();
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $parameters = [];

        while ($this->isLexical($stream, ColonLexical::TYPE)) {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE);

            $parameters[] = $this->parseExpression($stream, self::PRECEDENCE_CHAIN);
        }

        return new FilterCodeToken($name, $expression, $parameters);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseTernary(LexicalStream $stream, CodeToken $expression): CodeToken
    {
        $truthy = $this->parseExpression($stream, self::PRECEDENCE_CHAIN);
        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, ColonLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $falsey = $this->parseExpression($stream, self::PRECEDENCE_CHAIN);
        $stream->skip(WhitespaceLexical::TYPE);

        return new TernaryCodeToken($expression, $truthy, $falsey);
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLabel
     * @throws UnexpectedLexical
     */
    private function parseInvoke(LexicalStream $stream, CodeToken $invoked): CodeToken
    {
        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $parameters = [];

        while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
            $parameters[] = $this->parseValue($stream);
            $stream->skip(WhitespaceLexical::TYPE);

            if ($this->isLexical($stream, CommaLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE);

                continue;
            }

            break;
        }

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        return new InvokeCodeToken($invoked, $parameters);
    }

    private function parseAccess(LexicalStream $stream, CodeToken $called, bool $nullable): CodeToken
    {
        if ($this->isLexical($stream, SquareBracketLeftLexical::TYPE)) {
            $this->expectLexical($stream, SquareBracketLeftLexical::TYPE);
            $stream->next();

            $property = $this->parseExpression($stream);
            $stream->skip(WhitespaceLexical::TYPE);

            $this->expectLexical($stream, SquareBracketRightLexical::TYPE);
            $stream->next();
        } elseif ($this->isLexical($stream, LabelLexical::TYPE)) {
            $name = (string)$stream->current();
            $stream->next();

            $property = new StringCodeToken($name);
        } else {
            throw new UnexpectedLexical(
                $stream->current(),
                SquareBracketLeftLexical::TYPE,
                LabelLexical::TYPE,
            );
        }

        return new AccessCodeToken($called, $property, $nullable ? AccessCodeToken::FLAG_NULLABLE : 0);
    }
}
