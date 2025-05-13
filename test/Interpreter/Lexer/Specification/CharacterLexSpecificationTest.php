<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use PHPUnit\Framework\TestCase;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Lexical\Character\EqualsSignLexical;
use LesCoder\Interpreter\Lexer\Specification\CharacterSpecification;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\CharacterSpecification
 */
class CharacterLexSpecificationTest extends TestCase
{
    public function testCharacterIsSatisfied(): void
    {
        $specification = new CharacterSpecification('=', EqualsSignLexical::class);

        self::assertTrue($specification->isSatisfiedBy(new DirectStringStream('=')));
        self::assertFalse($specification->isSatisfiedBy(new DirectStringStream('!')));
    }

    public function testParse(): void
    {
        $specification = new CharacterSpecification('=', EqualsSignLexical::class);

        $stream = new DirectStringStream('=');
        $result = $specification->parse($stream);

        self::assertInstanceOf(EqualsSignLexical::class, $result);
    }
}
