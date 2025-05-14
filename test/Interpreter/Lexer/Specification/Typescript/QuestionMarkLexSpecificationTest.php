<?php
declare(strict_types=1);

namespace Interpreter\Lexer\Specification\Typescript;

use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\Typescript\QuestionMarkSpecification;

#[CoversClass(QuestionMarkSpecification::class)]
class QuestionMarkLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new QuestionMarkSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('?')));

        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream(' ')));
    }
}
