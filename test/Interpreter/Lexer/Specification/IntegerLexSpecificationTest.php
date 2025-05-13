<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\IntegerSpecification;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\IntegerSpecification
 */
class IntegerLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new IntegerSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        for ($i = 0; $i <= 9; $i += 1) {
            self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream((string)$i)));
        }

        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream(' ')));
    }
}
