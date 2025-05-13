<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\AmpersandSpecification;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\AmpersandSpecification
 */
class AmpersandLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new AmpersandSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        $satisfied = new DirectStringStream('&');
        $notSatisfied = new DirectStringStream(' ');

        self::assertTrue($this->specification->isSatisfiedBy($satisfied));
        self::assertFalse($this->specification->isSatisfiedBy($notSatisfied));
    }
}
