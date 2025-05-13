<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification\Typescript;

use RuntimeException;
use LesCoder\Token\CodeToken;
use PHPUnit\Framework\TestCase;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Object\NamespaceCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\NamespaceParseSpecification;

#[CoversClass(NamespaceParseSpecification::class)]
class NamespaceParseSpecificationTest extends TestCase
{
    private const TEST_CODE = <<<'TS'
namespace Fiz {
    export
    
    declare
}
TS;

    public function testParse(): void
    {
        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream(self::TEST_CODE));

        $exportCodeToken = $this->createMock(CodeToken::class);
        $declareCodeToken = $this->createMock(CodeToken::class);

        $subParseSpecification = $this->createMock(ParseSpecification::class);
        $subParseSpecification
            ->expects(self::exactly(2))
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) use ($exportCodeToken, $declareCodeToken) {
                    if ($stream->isEnd()) {
                        throw new \RuntimeException('Unexpected end of stream');
                    }

                    $current = $stream->current();
                    $stream->next();

                    if ($current instanceof LabelLexical) {
                        if ((string)$current === 'export') {
                            return $exportCodeToken;
                        }

                        if ((string)$current === 'declare') {
                            return $declareCodeToken;
                        }
                    }

                    throw new RuntimeException();
                },
            );

        $classParser = new NamespaceParseSpecification($subParseSpecification);

        $class = $classParser->parse($lexicals);

        self::assertInstanceOf(NamespaceCodeToken::class, $class);

        self::assertEquals(
            new NamespaceCodeToken(
                'Fiz',
                [
                    $exportCodeToken,
                    $declareCodeToken,
                ],
            ),
            $class,
        );

        self::assertTrue($lexicals->isEnd());
    }
}
