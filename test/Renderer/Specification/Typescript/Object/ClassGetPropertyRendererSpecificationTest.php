<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Object\ClassGetPropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\ClassGetPropertyRendererSpecification;

#[CoversClass(ClassGetPropertyRendererSpecification::class)]
class ClassGetPropertyRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ClassGetPropertyRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ClassGetPropertyCodeToken(Visibility::Private, 'test');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $hint = $this->createMock(CodeToken::class);
        $bodyLine = $this->createMock(CodeToken::class);
        $attribute = new AttributeCodeToken(new ReferenceCodeToken('baz'), []);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$hint, 'hint'],
                    [$bodyLine, 'bl'],
                    [$attribute, 'Attribute'],
                ],
            );

        $comment = new CommentCodeToken('Comment');

        $token = new ClassGetPropertyCodeToken(
            Visibility::Private,
            'foo',
            $hint,
            [$bodyLine],
            attributes: [$attribute],
            comment: $comment,
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
Attribute
/** Comment */
private get foo(): hint {
    bl
}
TXT;

        self::assertSame($expected, $rendered);
    }
}
