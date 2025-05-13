<?php
declare(strict_types=1);

namespace LesCoderTest\Renderer\Specification\Typescript\Object;

use Override;
use PHPUnit\Framework\TestCase;
use PHP_CodeSniffer\Reports\Code;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\CommentCodeToken;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\Object\ClassCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Typescript\Object\ClassRendererSpecification;

/**
 * @covers \LesCoder\Renderer\Specification\Typescript\Object\ClassRendererSpecification
 */
class ClassRendererSpecificationTest extends TestCase
{
    public RendererSpecification $specification;

    #[Override]
    protected function setUp(): void
    {
        $this->specification = new ClassRendererSpecification();
    }


    public function testCanRender(): void
    {
        $not = $this->createMock(CodeToken::class);

        $token = new ClassCodeToken('foo');

        self::assertTrue($this->specification->canRender($token));
        self::assertFalse($this->specification->canRender($not));
    }

    public function testRender(): void
    {
        $extends = $this->createMock(CodeToken::class);
        $implements = $this->createMock(CodeToken::class);
        $attribute = new AttributeCodeToken(new ReferenceCodeToken('baz'), []);
        $property = new ClassPropertyCodeToken(Visibility::Protected, 'bar');
        $method = new ClassMethodCodeToken(Visibility::Protected, 'baz');
        $generic = new GenericParameterCodeToken($this->createMock(CodeToken::class));

        $renderer = $this->createMock(CodeRenderer::class);
        $renderer
            ->expects(self::exactly(6))
            ->method('render')
            ->willReturnMap(
                [
                    [$extends, 'Extends'],
                    [$implements, 'Implements'],
                    [$attribute, 'Attribute'],
                    [$property, 'property'],
                    [$method, 'method'],
                    [$generic, 'generic'],
                ],
            );

        $comment = new CommentCodeToken('Comment');

        $token = new ClassCodeToken(
            'name',
            $extends,
            [$implements],
            [$attribute],
            [$property],
            [$method],
            flags: ClassCodeToken::FLAG_ABSTRACT,
            generics: [$generic],
            comment: $comment,
        );

        $rendered = $this->specification->render($token, $renderer);

        $expected = <<<'TXT'
Attribute
/** Comment */
export abstract class name<generic> extends Extends implements Implements {
    property;

    method
}

TXT;

        self::assertSame($expected, $rendered);
    }
}
