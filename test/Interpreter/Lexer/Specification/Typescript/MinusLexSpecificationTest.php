<?php
declare(strict_types=1);

namespace Interpreter\Lexer\Specification\Typescript;

use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\Typescript\MinusSpecification;

#[CoversClass(MinusSpecification::class)]
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
