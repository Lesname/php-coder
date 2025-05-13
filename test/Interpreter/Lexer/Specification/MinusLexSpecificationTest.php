<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\MinusSpecification;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\MinusSpecification
 */
class MinusLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new MinusSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        $satisfied = new DirectStringStream('-');
        $notSatisfied = new DirectStringStream(' ');

        self::assertTrue($this->specification->isSatisfiedBy($satisfied));
        self::assertFalse($this->specification->isSatisfiedBy($notSatisfied));
    }
}
