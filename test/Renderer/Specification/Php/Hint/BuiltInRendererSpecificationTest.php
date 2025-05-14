<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Hint;

use Override;
use LesCoder\Token\CodeToken;
use PHPUnit\Framework\MockObject\Exception;
use LesCoder\Renderer\CodeRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Php\Hint\BuiltInRendererSpecification;
use PHPUnit\Framework\TestCase;

#[CoversClass(BuiltInRendererSpecification::class)]
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

        $token = BuiltInCodeToken::Any;

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
        $renderer
            ->expects(self::never())
            ->method('render');

        $rendered = $this->specification->render($token, $renderer);

        self::assertSame($expected, $rendered);
    }

    /**
     * @return array<mixed>
     */
    public static function getBuiltIns(): array
    {
        return [
            [BuiltInCodeToken::Any, 'mixed'],
        ];
    }
}
