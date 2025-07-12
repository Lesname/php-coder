<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification;

use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\RecursiveParseSpecification;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Interpreter\Parser\Specification\Exception\NoParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Exception\ExpectedParseSpecification;

#[CoversClass(RecursiveParseSpecification::class)]
class RecursiveParseSpecificationTest extends TestCase
{
    public function testDirectSpecification(): void
    {
        $stream = $this->createMock(LexicalStream::class);

        $token = $this->createMock(CodeToken::class);

        $specification = $this->createMock(ParseSpecification::class);
        $specification->expects(self::exactly(2))->method('isSatisfiedBy')->with($stream)->willReturn(true);
        $specification->expects(self::once())->method('parse')->with($stream)->willReturn($token);

        $groupSpecification = new RecursiveParseSpecification([$specification]);

        self::assertTrue($groupSpecification->isSatisfiedBy($stream));
        self::assertSame($token, $groupSpecification->parse($stream));
    }

    public function testCallableSpecification(): void
    {
        $stream = $this->createMock(LexicalStream::class);

        $token = $this->createMock(CodeToken::class);

        $specification = $this->createMock(ParseSpecification::class);
        $specification->expects(self::once())->method('isSatisfiedBy')->with($stream)->willReturn(true);
        $specification->expects(self::once())->method('parse')->with($stream)->willReturn($token);

        $capture = null;

        $groupSpecification = new RecursiveParseSpecification(
            [
                function (ParseSpecification $parentSpecification) use (&$capture, $specification) {
                    $capture = $parentSpecification;

                    return $specification;
                }
            ]
        );
        self::assertSame($groupSpecification, $capture);
        self::assertSame($token, $groupSpecification->parse($stream));
    }

    public function testInvalidSpecification(): void
    {
        self::expectException(ExpectedParseSpecification::class);

        // ignore is for test needed
        /** @phpstan-ignore argument.type */
        new RecursiveParseSpecification(['test']);
    }

    public function testNoParseSpecification(): void
    {
        self::expectException(NoParseSpecification::class);

        $stream = $this->createMock(LexicalStream::class);
        $stream
            ->method('current')
            ->willReturn($this->createMock(Lexical::class));

        $specification = $this->createMock(ParseSpecification::class);
        $specification->expects(self::once())->method('isSatisfiedBy')->with($stream)->willReturn(false);
        $specification->expects(self::never())->method('parse');

        $groupSpecification = new RecursiveParseSpecification([$specification]);

        $groupSpecification->parse($stream);
    }
}
