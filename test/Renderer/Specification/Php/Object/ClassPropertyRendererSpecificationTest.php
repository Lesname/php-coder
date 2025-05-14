<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Php\Object;

use Override;
use PHPUnit\Framework\TestCase;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Php\Object\ClassPropertyRendererSpecification;

#[CoversClass(ClassPropertyRendererSpecification::class)]
class ClassPropertyRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ClassPropertyRendererSpecification();
    }

    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ClassPropertyCodeToken(Visibility::Private, 'bar');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $hint = $this->createMock(CodeToken::class);
        $assigned = $this->createMock(CodeToken::class);

        $attribute = new AttributeCodeToken(new ReferenceCodeToken('bar'), []);

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(3))
            ->method('render')
            ->willReturnMap(
                [
                    [$hint, 'hint'],
                    [$assigned, 'assigned'],
                    [$attribute, 'Attribute'],
                ],
            );


        $token = new ClassPropertyCodeToken(
            Visibility::Protected,
            'Props',
            $hint,
            $assigned,
            ClassPropertyCodeToken::FLAG_STATIC,
            [$attribute],
            new CommentCodeToken('Comment'),
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
/** Comment */
Attribute protected static hint $Props = assigned
TXT;

        self::assertSame($expected, $rendered);
    }
}
