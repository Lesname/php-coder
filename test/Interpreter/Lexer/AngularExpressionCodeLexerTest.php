<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer;

use PHPUnit\Framework\Attributes\DataProvider;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Lexer\Lexical\WhitespaceLexical;
use LesCoder\Interpreter\Lexer\AngularExpressionCodeLexer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Lexical\Value\IntegerLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\DotLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\OrLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PlusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\AndLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\PipeLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\MinusLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CommaLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\ColonLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\AsteriskLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\CoalescingLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\QuestionMarkLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Slash\ForwardSlashLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\EqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\Parenthesis\ParenthesisRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\CurlyBracket\CurlyBracketRightLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketLeftLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\SquareBracket\SquareBracketRightLexical;

#[CoversClass(AngularExpressionCodeLexer::class)]
class AngularExpressionCodeLexerTest extends TestCase
{
    public function testLexVariable(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('bar');

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new LabelLexical('bar'),
            $stream->current(),
        );

        $stream->next();
        self::assertTrue($stream->isEnd());
    }

    public function testLexInvoke(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('bar(biz)');

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new LabelLexical('bar'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new ParenthesisLeftLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new LabelLexical('biz'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new ParenthesisRightLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertTrue($stream->isEnd());
    }

    #[DataProvider('getStringQuote')]
    public function testLexString(string $quote): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream("{$quote}fizbizz{$quote}");

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new StringLexical("fizbizz"),
            $stream->current(),
        );
        $stream->next();

        self::assertTrue($stream->isEnd());
    }

    /**
     * @return iterable<mixed>
     */
    public static function getStringQuote(): iterable
    {
        return [
            ['"'],
            ["'"],
        ];
    }

    public function testLexNumberInteger(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('-123');

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new MinusLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new IntegerLexical('123'),
            $stream->current(),
        );
        $stream->next();

        self::assertTrue($stream->isEnd());
    }

    public function testLexNumberFloat(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('123.321');

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new IntegerLexical('123'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new DotLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new IntegerLexical('321'),
            $stream->current(),
        );
        $stream->next();

        self::assertTrue($stream->isEnd());
    }

    public function testLexList(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('["foo",false]');

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new SquareBracketLeftLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new StringLexical('foo'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new CommaLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new LabelLexical('false'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new SquareBracketRightLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertTrue($stream->isEnd());
    }

    public function testLexDict(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream("{foo:'bar',bar:true}");

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new CurlyBracketLeftLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new LabelLexical('foo'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new ColonLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new StringLexical('bar'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new CommaLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new LabelLexical('bar'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new ColonLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new LabelLexical('true'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new CurlyBracketRightLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertTrue($stream->isEnd());
    }

    public function testLexGroup(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('(bar)');

        $stream = $lexer->tokenize($input);

        self::assertEquals(
            new ParenthesisLeftLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new LabelLexical('bar'),
            $stream->current(),
        );
        $stream->next();

        self::assertEquals(
            new ParenthesisRightLexical(),
            $stream->current(),
        );
        $stream->next();

        self::assertTrue($stream->isEnd());
    }

    public function testLexMath(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('((bar --1) + (2 * 3)) - (6 / 2)');

        $stream = $lexer->tokenize($input);

        $expect = [
            new ParenthesisLeftLexical(),
            new ParenthesisLeftLexical(),
            new LabelLexical('bar'),
            new WhitespaceLexical(' '),
            new MinusLexical(),
            new MinusLexical(),
            new IntegerLexical('1'),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new PlusLexical(),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new IntegerLexical('2'),
            new WhitespaceLexical(' '),
            new AsteriskLexical(),
            new WhitespaceLexical(' '),
            new IntegerLexical('3'),
            new ParenthesisRightLexical(),
            new ParenthesisRightLexical(),
            new WhitespaceLexical(' '),
            new MinusLexical(),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new IntegerLexical('6'),
            new WhitespaceLexical(' '),
            new ForwardSlashLexical(),
            new WhitespaceLexical(' '),
            new IntegerLexical('2'),
            new ParenthesisRightLexical(),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }

    public function testNestedAccess(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('bar().biz.foo["bar"][1]?.fiz');

        $stream = $lexer->tokenize($input);

        $expect = [
            new LabelLexical('bar'),
            new ParenthesisLeftLexical(),
            new ParenthesisRightLexical(),
            new DotLexical(),
            new LabelLexical('biz'),
            new DotLexical(),
            new LabelLexical('foo'),
            new SquareBracketLeftLexical(),
            new StringLexical('bar'),
            new SquareBracketRightLexical(),
            new SquareBracketLeftLexical(),
            new IntegerLexical('1'),
            new SquareBracketRightLexical(),
            new QuestionMarkLexical(),
            new DotLexical(),
            new LabelLexical('fiz'),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }

    public function testChain(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('bar && (foo || biz)');

        $stream = $lexer->tokenize($input);

        $expect = [
            new LabelLexical('bar'),
            new WhitespaceLexical(' '),
            new AndLexical('&&'),
            new WhitespaceLexical(' '),
            new ParenthesisLeftLexical(),
            new LabelLexical('foo'),
            new WhitespaceLexical(' '),
            new OrLexical('||'),
            new WhitespaceLexical(' '),
            new LabelLexical('biz'),
            new ParenthesisRightLexical(),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }

    public function testCoalesce(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('bar ?? foo');

        $stream = $lexer->tokenize($input);

        $expect = [
            new LabelLexical('bar'),
            new WhitespaceLexical(' '),
            new CoalescingLexical('??'),
            new WhitespaceLexical(' '),
            new LabelLexical('foo'),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }

    public function testFilter(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('bar | fiz:biz');

        $stream = $lexer->tokenize($input);

        $expect = [
            new LabelLexical('bar'),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new LabelLexical('fiz'),
            new ColonLexical(),
            new LabelLexical('biz'),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }

    public function testTernary(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('foo ? bar : baz');

        $stream = $lexer->tokenize($input);

        $expect = [
            new LabelLexical('foo'),
            new WhitespaceLexical(' '),
            new QuestionMarkLexical(),
            new WhitespaceLexical(' '),
            new LabelLexical('bar'),
            new WhitespaceLexical(' '),
            new ColonLexical(),
            new WhitespaceLexical(' '),
            new LabelLexical('baz'),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }

    public function testAdditionAccess(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('"foo" + fiz.biz | translate');

        $stream = $lexer->tokenize($input);

        $expect = [
            new StringLexical('foo'),
            new WhitespaceLexical(' '),
            new PlusLexical(),
            new WhitespaceLexical(' '),
            new LabelLexical('fiz'),
            new DotLexical(),
            new LabelLexical('biz'),
            new WhitespaceLexical(' '),
            new PipeLexical(),
            new WhitespaceLexical(' '),
            new LabelLexical('translate'),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }

    public function testCompareTernary(): void
    {
        $lexer = new AngularExpressionCodeLexer();
        $input = new DirectStringStream('\'foo\' == "bar" ? 1 : 2');

        $stream = $lexer->tokenize($input);

        $expect = [
            new StringLexical('foo'),
            new WhitespaceLexical(' '),
            new EqualsLexical('=='),
            new WhitespaceLexical(' '),
            new StringLexical('bar'),
            new WhitespaceLexical(' '),
            new QuestionMarkLexical(),
            new WhitespaceLexical(' '),
            new IntegerLexical('1'),
            new WhitespaceLexical(' '),
            new ColonLexical(),
            new WhitespaceLexical(' '),
            new IntegerLexical('2'),
        ];

        foreach ($expect as $token) {
            self::assertEquals($token, $stream->current());
            $stream->next();
        }

        self::assertTrue($stream->isEnd());
    }
}
