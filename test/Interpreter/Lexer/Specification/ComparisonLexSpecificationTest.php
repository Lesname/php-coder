<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Lexer\Specification;

use Override;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;
use LesCoder\Interpreter\Lexer\Specification\Specification;
use LesCoder\Interpreter\Lexer\Lexical\Character\LowerThanLexical;
use LesCoder\Interpreter\Lexer\Lexical\Character\GreaterThanLexical;
use LesCoder\Interpreter\Lexer\Specification\ComparisonSpecification;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\SameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\EqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotSameLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\NotEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\LowerThanOrEqualsLexical;
use LesCoder\Interpreter\Lexer\Lexical\Expression\Comparison\GreaterThanOrEqualsLexical;

/**
 * @covers \LesCoder\Interpreter\Lexer\Specification\ComparisonSpecification
 */
class ComparisonLexSpecificationTest extends TestCase
{
    private Specification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ComparisonSpecification();
    }

    public function testIsSatisfiedBy(): void
    {
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('>')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('<')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('=')));
        self::assertTrue($this->specification->isSatisfiedBy(new DirectStringStream('!')));

        self::assertFalse($this->specification->isSatisfiedBy(new DirectStringStream(' ')));
    }

    /**
     * @param class-string $expect
     */
    #[DataProvider('getTestParseValues')]
    public function testParse(string $text, string $expect): void
    {
        $string = new DirectStringStream($text);

        $lexical = $this->specification->parse($string);

        self::assertInstanceOf($expect, $lexical);
        self::assertTrue($string->isEnd());
    }

    /**
     * @return array<array{string, class-string<Lexical>}>
     */
    public static function getTestParseValues(): array
    {
        return [
            ['>', GreaterThanLexical::class],
            ['>=', GreaterThanOrEqualsLexical::class],
            ['<', LowerThanLexical::class],
            ['<=', LowerThanOrEqualsLexical::class],
            ['===', SameLexical::class],
            ['!==', NotSameLexical::class],
            ['==', EqualsLexical::class],
            ['!=', NotEqualsLexical::class],
        ];
    }
}
