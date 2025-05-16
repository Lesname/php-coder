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
use LesCoder\Interpreter\Lexer\Lexical\Character\AtSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DoubleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SingleQuoteLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Expression\OpenLexical;
use LesCoder\Interpreter\Lexer\Lexical\Angular\Expression\CloseLexical;
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
        $stream = new DirectStringStream(file_get_contents(__DIR__ . '/stub/simple.html'));
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
            new LowerThanLexical(),
            new TextLexical('input'),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('*ngIf'),
            new EqualsSignLexical(),
            new DoubleQuoteLexical(),
            new TextLexical('foo'),
            new DoubleQuoteLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('type'),
            new EqualsSignLexical(),
            new DoubleQuoteLexical(),
            new TextLexical('text'),
            new DoubleQuoteLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('class'),
            new EqualsSignLexical(),
            new DoubleQuoteLexical(),
            new TextLexical('two'),
            new DoubleQuoteLexical(),
            new WhitespaceLexical(PHP_EOL),
            new ForwardSlashLexical(),
            new GreaterThanLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('Bar'),
            new WhitespaceLexical(PHP_EOL),
            new OpenLexical('{{'),
            new WhitespaceLexical(' '),
            new SingleQuoteLexical(),
            new TextLexical("submit"),
            new SingleQuoteLexical(),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('translate'),
            new WhitespaceLexical(' '),
            new CloseLexical('}}'),
            new WhitespaceLexical(PHP_EOL),
            new LowerThanLexical(),
            new ForwardSlashLexical(),
            new TextLexical('div'),
            new GreaterThanLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CommentLexical('<div>'),
            new WhitespaceLexical(PHP_EOL),
            new LowerThanLexical(),
            new TextLexical('ng-container'),
            new WhitespaceLexical(' '),
            new TextLexical('*ngFor'),
            new EqualsSignLexical(),
            new DoubleQuoteLexical(),
            new TextLexical('let'),
            new WhitespaceLexical(' '),
            new TextLexical('foo'),
            new WhitespaceLexical(' '),
            new TextLexical('in'),
            new WhitespaceLexical(' '),
            new TextLexical('bar'),
            new DoubleQuoteLexical(),
            new GreaterThanLexical(),
            new WhitespaceLexical(PHP_EOL),
            new OpenLexical('{{'),
            new WhitespaceLexical(' '),
            new SingleQuoteLexical(),
            new TextLexical("fiz"),
            new SingleQuoteLexical(),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new TextLexical("biz"),
            new WhitespaceLexical(' '),
            new CloseLexical('}}'),
            new WhitespaceLexical(PHP_EOL),
            new LowerThanLexical(),
            new ForwardSlashLexical(),
            new TextLexical('ng-container'),
            new GreaterThanLexical(),
        ];

        foreach ($expected as $lexical) {
            self::assertEquals($lexical, $lexicals->current());
            $lexicals->next();
        }

        self::assertTrue($lexicals->isEnd());
    }

    public function testTokenizeFlowControlFor(): void
    {
        $stream = new DirectStringStream(file_get_contents(__DIR__ . '/stub/flow-control-for.html'));
        $lexer = new TemplateCodeLexer();

        $lexicals = $lexer->tokenize($stream);

        $expected = [
            new AtSignLexical(),
            new TextLexical('for'),
            new ParenthesisLeftLexical(),
            new TextLexical('item'),
            new WhitespaceLexical(' '),
            new TextLexical('of'),
            new WhitespaceLexical(' '),
            new TextLexical('items;'),
            new WhitespaceLexical(' '),
            new TextLexical('track'),
            new WhitespaceLexical(' '),
            new TextLexical('item.id'),
            new ParenthesisRightLexical(),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new OpenLexical('{{'),
            new WhitespaceLexical(' '),
            new TextLexical('item.name'),
            new WhitespaceLexical(' '),
            new CloseLexical('}}'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(' '),
            new AtSignLexical(),
            new TextLexical('empty'),
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
            new LowerThanLexical(),
            new ForwardSlashLexical(),
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
        $stream = new DirectStringStream(file_get_contents(__DIR__ . '/stub/flow-control-if.html'));
        $lexer = new TemplateCodeLexer();

        $lexicals = $lexer->tokenize($stream);

        $expected = [
            new AtSignLexical(),
            new TextLexical('if'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('users$'),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new TextLexical('async;'),
            new WhitespaceLexical(' '),
            new TextLexical('as'),
            new WhitespaceLexical(' '),
            new TextLexical('users'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new AtSignLexical(),
            new TextLexical('if'),
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
            new OpenLexical('{{'),
            new WhitespaceLexical(' '),
            new TextLexical('users.length'),
            new WhitespaceLexical(' '),
            new CloseLexical('}}'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(' '),
            new AtSignLexical(),
            new TextLexical('else'),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new TextLexical('none'),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(PHP_EOL),
            new CurlyBracketRightLexical(),
            new WhitespaceLexical(' '),
            new AtSignLexical(),
            new TextLexical('elseif'),
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
            new AtSignLexical(),
            new TextLexical('else'),
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
        $stream = new DirectStringStream(file_get_contents(__DIR__ . '/stub/flow-control-switch.html'));
        $lexer = new TemplateCodeLexer();

        $lexicals = $lexer->tokenize($stream);

        $expected = [
            new AtSignLexical(),
            new TextLexical('switch'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new TextLexical('condition'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new CurlyBracketLeftLexical(),
            new WhitespaceLexical(PHP_EOL),
            new AtSignLexical(),
            new TextLexical('case'),
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
            new AtSignLexical(),
            new TextLexical('case'),
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
            new AtSignLexical(),
            new TextLexical('default'),
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
