<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Value;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use PHPUnit\Framework\MockObject\Exception;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use LesCoder\Token\Value\BuiltInCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Php\Value\BuiltInRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Value\BuiltInRendererSpecification
 */
class BuiltInRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new BuiltInRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = BuiltInCodeToken::Null;

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }


    /**
     *
     * @throws UnexpectedCodeToken
     * @throws Exception
     */
    #[DataProvider('getBuiltIns')]
    public function testRender(CodeToken $token, string $expected): void
    {
        $renderer = $this->createMock(CodeRenderer::class);

        $rendered = $this->specification->render($token, $renderer);

        self::assertSame($expected, $rendered);
    }

    /**
     * @return array<mixed>
     */
    public static function getBuiltIns(): array
    {
        return [
            [BuiltInCodeToken::Null, 'null'],
            [BuiltInCodeToken::True, 'true'],
            [BuiltInCodeToken::False, 'false'],
        ];
    }
}
