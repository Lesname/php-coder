<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\InterfacePropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\InterfacePropertyRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\Object\InterfacePropertyRendererSpecification
 */
class InterfacePropertyRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new InterfacePropertyRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new InterfacePropertyCodeToken($not);

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $name = $this->createMock(CodeToken::class);
        $hint = $this->createMock(CodeToken::class);
        $attribute = new AttributeCodeToken(new ReferenceCodeToken('baz'), []);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$name, 'name'],
                    [$hint, 'hint'],
                    [$attribute, 'Attribute'],
                ],
            );

        $comment = new CommentCodeToken('bar');

        $token = new InterfacePropertyCodeToken(
            $name,
            $hint,
            [$attribute],
            $comment,
            false,
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
Attribute
/** bar */
name?: hint
TXT;

        self::assertSame($expected, $rendered);
    }
}
