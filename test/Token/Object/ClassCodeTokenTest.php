<?php
declare(strict_types=1);

namespace LesCoderTest\Token\Object;

use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\LineCodeToken;
use LesCoder\Token\Object\ClassCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\ParameterCodeToken;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassCodeToken::class)]
final class ClassCodeTokenTest extends TestCase
{
    public function testGetImports(): void
    {
        $attributeParameter = $this->createMock(CodeToken::class);
        $attributeParameter
            ->method('getImports')
            ->willReturn(
                [
                    'a' => 'a',
                    'b' => 'b',
                ],
            );

        $property = $this->createMock(CodeToken::class);
        $property
            ->method('getImports')
            ->willReturn(
                [
                    'b' => 'b',
                    'c' => 'c',
                ],
            );

        $methodParameterHint = $this->createMock(CodeToken::class);
        $methodParameterHint
            ->method('getImports')
            ->willReturn(
                ['p' => 'p'],
            );

        $methodParameter = new ParameterCodeToken('p', $methodParameterHint);

        $methodReturn = $this->createMock(CodeToken::class);
        $methodReturn
            ->method('getImports')
            ->willReturn(
                ['r' => 'r'],
            );

        $methodBody = $this->createMock(CodeToken::class);
        $methodBody
            ->method('getImports')
            ->willReturn(
                ['d' => 'd'],
            );

        $mock = new ClassCodeToken(
            'fiz',
            new ReferenceCodeToken('ex', 'ex'),
            [new ReferenceCodeToken('impl', 'impl')],
            [
                new AttributeCodeToken(
                    new ReferenceCodeToken('attr', 'attr'),
                    [$attributeParameter],
                ),
            ],
            [
                new ClassPropertyCodeToken(
                    Visibility::Public,
                    'prop',
                    assigned: $property,
                ),
            ],
            [
                new ClassMethodCodeToken(
                    Visibility::Private,
                    'bar',
                    [$methodParameter],
                    $methodReturn,
                    [new LineCodeToken($methodBody)],
                ),
            ],
        );

        self::assertSame(
            [
                'ex' => 'ex',
                'impl' => 'impl',
                'attr' => 'attr',
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
                'p' => 'p',
                'r' => 'r',
                'd' => 'd',
            ],
            $mock->getImports(),
        );
    }
}
