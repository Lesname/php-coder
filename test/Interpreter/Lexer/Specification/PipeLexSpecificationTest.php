<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\PipeSpecification;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PipeSpecification::class)]
class PipeLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new PipeSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('|')));
        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream(' ')));
    }
}
