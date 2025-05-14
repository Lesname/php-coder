<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\LabelSpecification;

#[CoversClass(LabelSpecification::class)]
class LabelLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new LabelSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('a')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('C')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('_')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('ë')));

        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream(' ')));
    }
}
