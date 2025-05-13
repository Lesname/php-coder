<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Expression\DeclareCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\ParameterRendererSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\ParameterRendererSpecification
 */
class ParameterRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ParameterRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ParameterCodeToken('biz');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $hint = $this->createMock(CodeToken::class);
        $assigned = $this->createMock(CodeToken::class);
        $attribute = new AttributeCodeToken(new ReferenceCodeToken('bar'));

        $token = new ParameterCodeToken('biz', $hint, $assigned, [$attribute]);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$hint, 'hint'],
                    [$assigned, 'assigned'],
                    [$attribute, 'attribute'],
                ],
            );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
attribute biz: hint = assigned
TXT;

        self::assertSame($expected, $rendered);
    }
}
