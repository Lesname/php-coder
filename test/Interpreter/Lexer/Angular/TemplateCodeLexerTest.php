<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Angular;

use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Lexical\TextLexical;
use LesCoder\Interpreter\Lexer\Lexical\CommentLexical;
use LesCoder\Interpreter\Lexer\Angular\TemplateCodeLexer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PipeLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SemicolonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DoubleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SingleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\FlowControl\StartLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Element\StartCloseLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;

#[CoversClass(TemplateCodeLexer::class)]
class TemplateCodeLexerTest extends TestCase
{
    public function testTokenizeSimple(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/simple.html');
        assert(is_string($contents));

        $stream = new DirectStringStream($contents);
        $lexer = new TemplateCodeLexer();

        $lexicals = $lexer->tokenize($stream);

        $expected = [
            new TextLexical('Foo'),
            new WhitespaceLexical(PHP_EOL),
            new LowerThanLexical(),
            new TextLexical('div'),
            new GreaterThanLexical(),
            new WhitespaceLexical(PHP_EOL),
            new LowerThanLexical(),
            new TextLexical('input'),
            new WhitespaceLexical(' '),
            new TextLexical('type'),
            new EqualsSignLexical(),
            new DoubleQuoteLexical(),
            new TextLexical('text'),
            new DoubleQuoteLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('class'),
            new EqualsSignLexical(),
            new SingleQuoteLexical(),
            new TextLexical('one'),
            new SingleQuoteLexical(),
            new GreaterThanLexical(),
            new WhitespaceLexical(PHP_EOL),
            new StartLexical('if'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('foo'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurLyBracketLeftLexical(),
            new WhitespaceLexical("\n    "),
            new LowerThanLexical(),
            new TextLexical('input'),
            new WhitespaceLexical("\n        "),
            new TextLexical('type'),
            new EqualsSignLexical(),
            new DoubleQuoteLexical(),
            new TextLexical('text'),
            new DoubleQuoteLexical(),
            new WhitespaceLexical("\n        "),
            new TextLexical('class'),
            new EqualsSignLexical(),
            new DoubleQuoteLexical(),
            new TextLexical('two'),
            new DoubleQuoteLexical(),
            new WhitespaceLexical("\n    "),
            new ForwardSlashLexical(),
            new GreaterThanLexical(),
            new WhitespaceLexical("\n"),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('Bar'),
            new WhitespaceLexical("\n"),
            new CurlyBracketLeftLexical(),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(' '),
            new SingleQuoteLexical(),
            new TextLexical("submit"),
            new SingleQuoteLexical(),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('translate'),
            new WhitespaceLexical(' '),
            new CurlyBracketRightLexical(),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new StartCloseLexical('</'),
            new TextLexical('div'),
            new GreaterThanLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CommentLexical('<div>'),
            new WhitespaceLexical(PHP_EOL),
            new StartLexical('for'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('foo'),
            new WhitespaceLexical(' '),
            new TextLexical('of'),
            new WhitespaceLexical(' '),
            new TextLexical('bar'),
            new SemicolonLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('track'),
            new WhitespaceLexical(' '),
            new TextLexical('$index'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical("\n    "),
            new CurlyBracketLeftLexical(),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(' '),
            new SingleQuoteLexical(),
            new TextLexical("fiz"),
            new SingleQuoteLexical(),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new TextLexical("biz"),
            new WhitespaceLexical(' '),
            new CurlyBracketRightLexical(),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
        ];

        foreach ($expected as $lexical) {
            self::assertEquals($lexical, $lexicals->current());
            $lexicals->next();
        }

        self::assertTrue($lexicals->isEnd());
    }

    public function testTokenizeFlowControlFor(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/flow-control-for.html');
        assert(is_string($contents));

        $stream = new DirectStringStream($contents);
        $lexer = new TemplateCodeLexer();

        $lexicals = $lexer->tokenize($stream);

        $expected = [
            new StartLexical('for'),
            new ParenthesisLeftLexical(),
            new TextLexical('item'),
            new WhitespaceLexical(' '),
            new TextLexical('of'),
            new WhitespaceLexical(' '),
            new TextLexical('items'),
            new SemicolonLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('track'),
            new WhitespaceLexical(' '),
            new TextLexical('item.id'),
            new ParenthesisRightLexical(),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketLeftLexical(),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('item.name'),
            new WhitespaceLexical(' '),
            new CurlyBracketRightLexical(),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(' '),
            new StartLexical('empty'),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new LowerThanLexical(),
            new TextLexical('li'),
            new GreaterThanLexical(),
            new TextLexical('There'),
            new WhitespaceLexical(' '),
            new TextLexical('are'),
            new WhitespaceLexical(' '),
            new TextLexical('no'),
            new WhitespaceLexical(' '),
            new TextLexical('items.'),
            new StartCloseLexical('</'),
            new TextLexical('li'),
            new GreaterThanLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
        ];

        foreach ($expected as $lexical) {
            self::assertEquals($lexical, $lexicals->current());
            $lexicals->next();
        }

        self::assertTrue($lexicals->isEnd());
    }

    public function testTokenizeFlorControlIf(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/flow-control-if.html');
        assert(is_string($contents));

        $stream = new DirectStringStream($contents);
        $lexer = new TemplateCodeLexer();

        $lexicals = $lexer->tokenize($stream);

        $expected = [
            new StartLexical('if'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('users$'),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('async'),
            new SemicolonLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('as'),
            new WhitespaceLexical(' '),
            new TextLexical('users'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new StartLexical('if'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('users.length'),
            new WhitespaceLexical(' '),
            new GreaterThanLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('0'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketLeftLexical(),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('users.length'),
            new WhitespaceLexical(' '),
            new CurlyBracketRightLexical(),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(' '),
            new StartLexical('else'),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('none'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(' '),
            new StartLexical('elseif'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('a'),
            new WhitespaceLexical(' '),
            new EqualsSignLexical(),
            new EqualsSignLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('b'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('elseif'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(' '),
            new StartLexical('else'),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('else'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
        ];

        foreach ($expected as $lexical) {
            self::assertEquals($lexical, $lexicals->current());
            $lexicals->next();
        }

        self::assertTrue($lexicals->isEnd());
    }

    public function testTokenizeFlowControlSwitch(): void
    {
        $contents = file_get_contents(__DIR__ . '/stub/flow-control-switch.html');
        assert(is_string($contents));

        $stream = new DirectStringStream($contents);
        $lexer = new TemplateCodeLexer();

        $lexicals = $lexer->tokenize($stream);

        $expected = [
            new StartLexical('switch'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('condition'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new StartLexical('case'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('caseA'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('Case'),
            new WhitespaceLexical(' '),
            new TextLexical('A.'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new StartLexical('case'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('caseB'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('Case'),
            new WhitespaceLexical(' '),
            new TextLexical('B.'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new StartLexical('default'),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('Default'),
            new WhitespaceLexical(' '),
            new TextLexical('case.'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
        ];

        foreach ($expected as $lexical) {
            self::assertEquals($lexical, $lexicals->current());
            $lexicals->next();
        }

        self::assertTrue($lexicals->isEnd());
    }
}
