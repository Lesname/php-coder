<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Angular;

use Override;
use RuntimeException;
use LesCoder\Token\CodeToken;
use LesCoder\Token\TextCodeToken;
use LesCoder\Token\Block\IfCodeToken;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Interpreter\CodeInterpreter;
use LesCoder\Token\Block\Switch\CaseItem;
use LesCoder\Token\Block\SwitchCodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Stream\Exception\EndOfStream;
use LesCoder\Interpreter\Parser\CodeParser;
use LesCoder\Token\Value\AssignmentCodeToken;
use LesCoder\Stream\CodeToken\CodeTokenStream;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Token\Block\Angular\ForCodeToken;
use LesCoder\Token\Element\VoidElementCodeToken;
use LesCoder\Token\Block\Angular\For\Expression;
use LesCoder\Interpreter\Lexer\Lexical\TextLexical;
use LesCoder\Token\Element\NonVoidElementCodeToken;
use LesCoder\Stream\CodeToken\IteratorCodeTokenStream;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Angular\ExpressionCodeInterpreter;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DoubleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SingleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Expression\OpenLexical;
use LesCoder\Interpreter\Parser\Angular\Exception\UnexpectedCloseName;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Expression\CloseLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\FlowControl\StartLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Element\StartCloseLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Parser\Specification\Helper\ExpectParseSpecificationHelper;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;

final class TemplateCodeParser implements CodeParser
{
    use ExpectParseSpecificationHelper;

    private const array VOID_ELEMENTS = [
        'br',
        'hr',
        'img',
        'input',
        'link',
        'base',
        'meta',
        'param',
        'area',
        'embed',
        'col',
        'track',
        'source',
    ];

    private readonly CodeInterpreter $expressionCodeInterpreter;

    public function __construct(?CodeInterpreter $expressionCodeInterpreter = null)
    {
        $this->expressionCodeInterpreter = $expressionCodeInterpreter ?? new ExpressionCodeInterpreter();
    }

    #[Override]
    public function parse(LexicalStream $stream, ?string $file): CodeTokenStream
    {
        return new IteratorCodeTokenStream(
            (function () use ($stream) {
                while ($stream->isActive()) {
                    $token = $this->parseCodeToken($stream);

                    if ($token !== null) {
                        yield $token;
                    }
                }
            })(),
        );
    }

    /**
     * @throws EndOfStream
     * @throws UnexpectedCloseName
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseCodeToken(LexicalStream $stream): ?CodeToken
    {
        if ($this->isLexical($stream, LowerThanLexical::TYPE)) {
            return $this->parseElement($stream);
        }

        if ($this->isLexical($stream, StartCloseLexical::TYPE)) {
            throw new RuntimeException();
        }

        if ($this->isLexical($stream, OpenLexical::TYPE)) {
            return $this->parseExpression($stream);
        }

        if ($this->isLexical($stream, StartLexical::TYPE)) {
            return $this->parseFlowControl($stream);
        }

        if ($this->isLexical($stream, CommentLexical::TYPE)) {
            $stream->next();

            return null;
        }

        return $this->parseText($stream);
    }

    /**
     * @throws EndOfStream
     */
    private function parseText(LexicalStream $stream): ?CodeToken
    {
        $text = '';

        while ($stream->isActive()) {
            if ($this->isLexical($stream, CommentLexical::TYPE)) {
                $stream->next();

                continue;
            }

            if (
                $this->isLexical(
                    $stream,
                    LowerThanLexical::TYPE,
                    StartCloseLexical::TYPE,
                    OpenLexical::TYPE,
                    StartLexical::TYPE,
                    CurlyBracketRightLexical::TYPE
                )
            ) {
                break;
            }

            $text .= (string)$stream->current();
            $stream->next();
        }

        $text = trim($text);

        if ($text === '') {
            return null;
        }

        return new TextCodeToken(trim($text));
    }

    /**
     * @throws EndOfStream
     * @throws UnexpectedCloseName
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseElement(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, LowerThanLexical::TYPE);
        $stream->next();

        $name = (string)$stream->current();

        if (!$this->isLexical($stream, TextLexical::TYPE) || preg_match('#^[a-z][a-z\d\-:]*$#i', $name) !== 1) {
            return new TextCodeToken('<');
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE);

        $attributes = $this->parseElementAttributes($stream);
        $stream->skip(WhitespaceLexical::TYPE);

        if ($this->isLexical($stream, ForwardSlashLexical::TYPE)) {
            $stream->next();

            $this->expectLexical($stream, GreaterThanLexical::TYPE);
            $stream->next();

            return new VoidElementCodeToken($name, $attributes);
        }

        $this->expectLexical($stream, GreaterThanLexical::TYPE);
        $stream->next();

        if (in_array(strtolower($name), self::VOID_ELEMENTS, true)) {
            return new VoidElementCodeToken($name, $attributes);
        }

        $stream->skip(WhitespaceLexical::TYPE);
        $body = [];

        while ($stream->isActive() && !$this->isLexical($stream, StartCloseLexical::TYPE)) {
            $token = $this->parseCodeToken($stream);

            if ($token !== null) {
                $body[] = $token;
            }

            $stream->skip(WhitespaceLexical::TYPE);
        }

        $this->expectLexical($stream, StartCloseLexical::TYPE);
        $stream->next();

        $this->expectLexical($stream, TextLexical::TYPE);
        $closer = (string)$stream->current();
        $stream->next();

        if ($closer !== $name) {
            throw new UnexpectedCloseName($closer, $name);
        }

        $this->expectLexical($stream, GreaterThanLexical::TYPE);
        $stream->next();

        return new NonVoidElementCodeToken($name, $attributes, $body);
    }

    /**
     * @return array<string, CodeToken>
     *
     * @throws EndOfStream
     * @throws UnexpectedLexical
     * @throws UnexpectedEnd
     */
    private function parseElementAttributes(LexicalStream $stream): array
    {
        $attributes = [];

        while ($stream->isActive()) {
            if ($this->isLexical($stream, ForwardSlashLexical::TYPE, GreaterThanLexical::TYPE)) {
                break;
            }

            $name = '';

            do {
                $name .= (string)$stream->current();
                $stream->next();
            } while ($stream->isActive() && !$this->isLexical($stream, EqualsSignLexical::TYPE, WhitespaceLexical::TYPE, GreaterThanLexical::TYPE));

            $stream->skip(WhitespaceLexical::TYPE);

            if (!$this->isLexical($stream, EqualsSignLexical::TYPE)) {
                $attributes[$name] = new TextCodeToken('');

                continue;
            }

            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE);

            if ($this->isLexical($stream, DoubleQuoteLexical::TYPE, SingleQuoteLexical::CHARACTER)) {
                $quote = $stream->current();
                $stream->next();

                $value = '';

                while ($stream->isActive() && !$this->isLexical($stream, $quote->getType())) {
                    $value .= (string)$stream->current();
                    $stream->next();
                }

                $this->expectLexical($stream, $quote->getType());
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                $attributes[$name] = str_starts_with($name, '[')
                    ? $this->parseExpressionContent($value)
                    : new TextCodeToken($value);

                continue;
            }

            if ($this->isLexical($stream, TextLexical::TYPE)) {
                $attributes[$name] = new TextCodeToken((string)$stream->current());
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                continue;
            }

            throw new UnexpectedLexical(
                $stream->current(),
                TextLexical::TYPE,
                SingleQuoteLexical::TYPE,
                DoubleQuoteLexical::TYPE,
            );
        }

        return $attributes;
    }

    /**
     * @throws EndOfStream
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseExpression(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, OpenLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $expression = $this->parseAngularExpression($stream, CloseLexical::TYPE);
        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, CloseLexical::TYPE);
        $stream->next();

        return $expression;
    }

    /**
     * @throws EndOfStream
     */
    private function parseExpressionContent(string $stringExpression): CodeToken
    {
        $expressionStream = $this
            ->expressionCodeInterpreter
            ->interpret(new DirectStringStream($stringExpression));

        $expression = $expressionStream->current();
        $expressionStream->next();

        if ($expressionStream->isActive()) {
            throw new RuntimeException();
        }

        return $expression;
    }

    /**
     * @throws EndOfStream
     * @throws UnexpectedCloseName
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseFlowControl(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, StartLexical::TYPE);

        $control = (string)$stream->current();

        return match ($control) {
            'switch' => $this->parseFlowControlSwitch($stream),
            'for' => $this->parseFlowControlFor($stream),
            'let' => $this->parseFlowControlLet($stream),
            'if' => $this->parseFlowControlIf($stream),
            default => throw new RuntimeException("Unknown flow control: $control"),
        };
    }

    /**
     * @throws EndOfStream
     * @throws UnexpectedCloseName
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    private function parseFlowControlFor(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, StartLexical::TYPE);

        if ((string)$stream->current() !== 'for') {
            throw new RuntimeException();
        }

        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, TextLexical::TYPE);
        $as = (string)$stream->current();
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, TextLexical::TYPE);

        if ((string)$stream->current() !== 'of') {
            throw new RuntimeException();
        }

        $stream->next();
        $stream->skip(WhitespaceLexical::TYPE);

        $iterator = $this->parseAngularExpression($stream, SemicolonLexical::TYPE);

        $this->expectLexical($stream, SemicolonLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $track = null;
        $reassign = null;

        while ($stream->isActive() && !$this->isLexical($stream, ParenthesisRightLexical::TYPE)) {
            $this->expectLexical($stream, TextLexical::TYPE);
            $itemName = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE);

            if ($itemName === 'track') {
                if ($track !== null) {
                    throw new RuntimeException();
                }

                $track = $this->parseAngularExpression($stream, SemicolonLexical::TYPE, ParenthesisRightLexical::TYPE);
            } elseif ($itemName === 'let') {
                if ($reassign !== null) {
                    throw new RuntimeException();
                }

                $reassign = [];

                while ($this->isLexical($stream, TextLexical::TYPE)) {
                    $assignTo = (string)$stream->current();
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE);

                    $this->expectLexical($stream, EqualsSignLexical::TYPE);
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE);

                    $this->expectLexical($stream, TextLexical::TYPE);
                    $assignFrom = (string)$stream->current();
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE);

                    $reassign[$assignFrom] = $assignTo;

                    if (!$this->isLexical($stream, CommaLexical::TYPE)) {
                        break;
                    }

                    $this->expectLexical($stream, CommaLexical::TYPE);
                    $stream->next();

                    $stream->skip(WhitespaceLexical::TYPE);
                }
            } else {
                throw new RuntimeException("{$itemName} is not supported");
            }

            if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
                $stream->next();
                $stream->skip(WhitespaceLexical::TYPE);
            }
        }

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $body = $this->parseFlowControlBody($stream);
        $stream->skip(WhitespaceLexical::TYPE);

        if ($this->isLexical($stream, StartLexical::TYPE) && (string)$stream->current() === 'empty') {
            $stream->next();
            $stream->skip(WhitespaceLexical::TYPE);

            $empty = $this->parseFlowControlBody($stream);
            $stream->skip(WhitespaceLexical::TYPE);
        } else {
            $empty = [];
        }

        if ($track === null) {
            throw new RuntimeException();
        }

        return new ForCodeToken(
            new Expression(
                $iterator,
                $as,
                $track,
                $reassign ?? [],
            ),
            $body,
            $empty,
        );
    }

    private function parseFlowControlIf(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, StartLexical::TYPE, TextLexical::TYPE);

        if ((string)$stream->current() !== 'if') {
            throw new RuntimeException();
        }

        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $statement = $this->parseAngularExpression($stream, SemicolonLexical::TYPE, ParenthesisRightLexical::TYPE);

        if ($this->isLexical($stream, SemicolonLexical::TYPE)) {
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE);

            $this->expectLexical($stream, TextLexical::TYPE);
            $current = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE);

            if ($current !== 'as') {
                throw new RuntimeException();
            }

            $this->expectLexical($stream, TextLexical::TYPE);
            $as = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE);

            $statement = new AssignmentCodeToken(new VariableCodeToken($as), $statement);
        }

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $truthy = $this->parseFlowControlBody($stream);
        $stream->skip(WhitespaceLexical::TYPE);

        $falsey = [];

        if ($this->isLexical($stream, StartLexical::TYPE)) {
            $start = (string)$stream->current();

            if ($start === 'else') {
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                if ($this->isLexical($stream, TextLexical::TYPE)) {
                    $falsey[] = $this->parseFlowControlIf($stream);
                } else {
                    $falsey = $this->parseFlowControlBody($stream);
                }
            }
        }

        return new IfCodeToken(
            $statement,
            $truthy,
            $falsey,
        );
    }

    private function parseFlowControlSwitch(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, StartLexical::TYPE);

        if ((string)$stream->current() !== 'switch') {
            throw new RuntimeException();
        }

        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $statement = $this->parseAngularExpression($stream, SemicolonLexical::TYPE, ParenthesisRightLexical::TYPE);
        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $default = null;
        $cases = [];

        while ($this->isLexical($stream, StartLexical::TYPE)) {
            $startControl = (string)$stream->current();
            $stream->next();

            $stream->skip(WhitespaceLexical::TYPE);

            if ($startControl === 'case') {
                $this->expectLexical($stream, ParenthesisLeftLexical::TYPE);
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                $caseExpression = $this->parseAngularExpression($stream, ParenthesisRightLexical::TYPE);

                $this->expectLexical($stream, ParenthesisRightLexical::TYPE);
                $stream->next();

                $stream->skip(WhitespaceLexical::TYPE);

                $caseBody = $this->parseFlowControlBody($stream);
                $stream->skip(WhitespaceLexical::TYPE);

                $cases[] = new CaseItem(
                    $caseExpression,
                    $caseBody
                );

                continue;
            }

            if ($startControl === 'default') {
                if ($default !== null) {
                    throw new RuntimeException();
                }

                $default = $this->parseFlowControlBody($stream);
                $stream->skip(WhitespaceLexical::TYPE);

                continue;
            }

            throw new RuntimeException($startControl);
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        return new SwitchCodeToken(
            $statement,
            $cases,
            $default ?? []
        );
    }

    private function parseFlowControlLet(LexicalStream $stream): CodeToken
    {
        $this->expectLexical($stream, StartLexical::TYPE);

        if ((string)$stream->current() !== 'let') {
            throw new RuntimeException();
        }

        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, TextLexical::TYPE);
        $to = new VariableCodeToken((string)$stream->current());
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $this->expectLexical($stream, EqualsSignLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $as = $this->parseAngularExpression($stream, SemicolonLexical::TYPE);

        $this->expectLexical($stream, SemicolonLexical::TYPE);
        $stream->next();

        return new AssignmentCodeToken($to, $as);
    }

    /**
     * @throws EndOfStream
     */
    private function parseAngularExpression(LexicalStream $stream, string $stop, string ...$orStop): CodeToken
    {
        $stringExpression = '';

        while ($stream->isActive() && !$this->isLexical($stream, $stop, ...$orStop)) {
            $stringExpression .= $this->toAngularExpressionPart($stream);
        }

        return $this->parseExpressionContent(trim($stringExpression));
    }

    public function toAngularExpressionPart(LexicalStream $stream): string
    {
        $expression = (string)$stream->current();
        $stream->next();

        $groups = [
            '"' => '"',
            "'" => "'",
            '(' => ')',
            '{' => '}',
            '[' => ']',
        ];

        if (isset($groups[$expression])) {
            $end = $groups[$expression];

            while ($stream->isActive() && (string)$stream->current() !== $end) {
                $expression .= $this->toAngularExpressionPart($stream);
            }

            if ($stream->isEnd()) {
                throw new RuntimeException();
            }

            if ((string)$stream->current() !== $end) {
                throw new RuntimeException();
            }

            $expression .= (string)$stream->current();
            $stream->next();
        }

        return $expression;
    }

    /**
     * @return array<CodeToken>
     *
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     * @throws EndOfStream
     * @throws UnexpectedCloseName
     */
    private function parseFlowControlBody(LexicalStream $stream): array
    {
        $this->expectLexical($stream, CurlyBracketLeftLexical::TYPE);
        $stream->next();

        $stream->skip(WhitespaceLexical::TYPE);

        $body = [];

        while ($stream->isActive() && !$this->isLexical($stream, CurlyBracketRightLexical::TYPE)) {
            $token = $this->parseCodeToken($stream);
            $stream->skip(WhitespaceLexical::TYPE);

            if ($token) {
                $body[] = $token;
            }
        }

        $this->expectLexical($stream, CurlyBracketRightLexical::TYPE);
        $stream->next();

        return $body;
    }
}
