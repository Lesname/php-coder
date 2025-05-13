<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\AccessCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\AccessRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Php\Object\AccessRendererSpecification
 */
class AccessRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new AccessRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new AccessCodeToken($not, $not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRenderNonNullable(): void
    {
        $call = $this->createMock(CodeToken::class);
        $prop = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$call, 'call'],
                    [$prop, 'prop'],
                ],
            );

        $token = new AccessCodeToken($call, $prop);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
call->{prop}
TXT;

        self::assertSame($expected, $rendered);
    }

    public function testRenderNullable(): void
    {
        $call = $this->createMock(CodeToken::class);
        $prop = $this->createMock(CodeToken::class);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(2))
            ->method('render')
            ->willReturnMap(
                [
                    [$call, 'call'],
                    [$prop, 'prop'],
                ],
            );

        $token = new AccessCodeToken($call, $prop, AccessCodeToken::FLAG_NULLABLE);

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
call?->{prop}
TXT;

        self::assertSame($expected, $rendered);
    }
}
