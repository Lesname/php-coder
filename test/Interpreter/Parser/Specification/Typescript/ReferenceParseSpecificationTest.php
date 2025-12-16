<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification\Typescript;

use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Parser\Specification\Typescript\ReferenceParseSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReferenceParseSpecification::class)]
class ReferenceParseSpecificationTest extends TestCase
{
    public function testSimpleReference(): void
    {
        $code = 'foo';

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
        $code = 'foo';

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $parser = new ReferenceParseSpecification(
            [
                'foo' => [
                    'from' => './foo',
                    'name' => 'foo',
                ],
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
        $code = 'foo';

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($code));

        $parser = new ReferenceParseSpecification(
            [
                'foo' => [
                    'from' => '../bar/foo',
                    'name' => 'foo',
                ],
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
