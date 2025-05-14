<?php
declare(strict_types=1);

namespace LesCoderTest\Token\Object;

use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\InterfaceCodeToken;
use PHPUnit\Framework\Attributes\CoversClass;
use LesCoder\Token\Object\InterfaceMethodCodeToken;
use LesCoder\Token\Object\InterfacePropertyCodeToken;
use LesCoder\Token\ParameterCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use PHPUnit\Framework\TestCase;

#[CoversClass(InterfaceCodeToken::class)]
final class InterfaceCodeTokenTest extends TestCase
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

        $mock = new InterfaceCodeToken(
            'fiz',
            [new ReferenceCodeToken('ex', 'ex')],
            [
                new AttributeCodeToken(
                    new ReferenceCodeToken('attr', 'attr'),
                    [$attributeParameter],
                ),
            ],
            [
                new InterfacePropertyCodeToken(
                    new StringCodeToken('prop'),
                    $property,
                ),
            ],
            [
                new InterfaceMethodCodeToken(
                    'bar',
                    [$methodParameter],
                    $methodReturn,
                ),
            ],
        );

        self::assertSame(
            [
                'ex' => 'ex',
                'attr' => 'attr',
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
                'p' => 'p',
                'r' => 'r',
            ],
            $mock->getImports(),
        );
    }
}
