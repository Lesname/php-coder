<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification\Typescript;

use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Specification\Typescript\StringSpecification;
use LesCoder\Interpreter\Lexer\Specification\Exception\MissesClosingIdentifier;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\Typescript\StringSpecification
 */
class StringLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new StringSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('"')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream("'")));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('`')));

        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream(' ')));
    }

    #[DataProvider('getTestString')]
    public function testParse(string $input): void
    {
        /** @var StringLexical $token */
        $token = $this->specification->parse(new DirectStringStream($input));

        self::assertInstanceOf(StringLexical::class, $token);
        self::assertSame(trim($input), (string)$token);
    }

    /**
     * @return iterable<array<string>>
     */
    public static function getTestString(): iterable
    {
        return [
            [
                <<<'TYPESCRIPT'
"foo"

TYPESCRIPT,
                <<<'TYPESCRIPT'
'bar'

TYPESCRIPT,
                <<<'TYPESCRIPT'
`foo
bar`

TYPESCRIPT,
            ],
        ];
    }

    public function testNonEndingString(): void
    {
        $this->expectException(MissesClosingIdentifier::class);
        $string = "'";

        $this->specification->parse(new DirectStringStream($string));
    }
}
