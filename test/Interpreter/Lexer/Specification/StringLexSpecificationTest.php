<?php
declare(strict_types=1);

namespace Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Stream\String\DirectStringStream;
use PHPUnit\Framework\Attributes\DataProvider;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Lexical\Value\StringLexical;
use LesCoder\Interpreter\Lexer\Specification\StringSpecification;
use LesCoder\Interpreter\Lexer\Specification\Exception\MissesClosingIdentifier;

#[CoversClass(StringSpecification::class)]
class StringLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new StringSpecification(["'", '"', '`']);
    }

    public function testIsSatisfiedBy(): void
    {
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('"')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream("'")));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('`')));

        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream(' ')));
    }

    #[DataProvider('getTestString')]
    public function testParse(string $input, string $expected): void
    {
        /** @var StringLexical $token */
        $token = $this->specification->parse(new DirectStringStream($input));

        self::assertInstanceOf(StringLexical::class, $token);
        self::assertSame($expected, (string)$token);
    }

    /**
     * @return iterable<array<string>>
     */
    public static function getTestString(): iterable
    {
        return [
            [
                '"foo"', 'foo',
                "'bar'", 'bar',
                <<<'TYPESCRIPT'
`foo
bar`
TYPESCRIPT, 'foo' . PHP_EOL . 'bar',
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
