<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\WhitespaceSpecification;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\WhitespaceSpecification
 */
class WhitespaceLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new WhitespaceSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream("\t")));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream("\n\r")));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream("\n")));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream(' ')));

        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream('ë')));
    }
}
