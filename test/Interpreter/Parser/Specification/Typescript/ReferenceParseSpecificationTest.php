<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification\Typescript;

use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Parser\Specification\Typescript\ReferenceParseSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Interpreter\Parser\Specification\Typescript\ReferenceParseSpecification
 */
class ReferenceParseSpecificationTest extends TestCase
{
    public function testSimpleReference(): void
    {
        $code = <<<'TYPESCRIPT'
foo
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $parser = new ReferenceParseSpecification([]);

        $code = $parser->parse($lexicals);

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new ReferenceCodeToken('foo'),
            $code,
        );
    }

    public function testResolvedReferenceSameDir(): void
    {
        $code = <<<'TYPESCRIPT'
foo
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $parser = new ReferenceParseSpecification(
            [
                'foo' => './foo'
            ],
        );

        $code = $parser->parse($lexicals, '/fiz/bar.ts');

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new ReferenceCodeToken('foo', '/fiz/foo'),
            $code,
        );
    }

    public function testResolvedReferenceUpDir(): void
    {
        $code = <<<'TYPESCRIPT'
foo
TYPESCRIPT;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $parser = new ReferenceParseSpecification(
            [
                'foo' => '../bar/foo'
            ],
        );

        $code = $parser->parse($lexicals, '/fiz/bar.ts');

        self::assertTrue($lexicals->isEnd());

        self::assertEquals(
            new ReferenceCodeToken('foo', '/fiz/../bar/foo'),
            $code,
        );
    }
}
