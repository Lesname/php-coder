<?php
declare(strict_types=1);

namespace LesCoderTest\Token;

use LesCoder\Token\AttributeCodeToken;
use LesCoder\Token\CodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Token\AttributeCodeToken
 */
final class AttributeCodeTokenTest extends TestCase
{
    public function testGetImports(): void
    {
        $f = $this->createMock(CodeToken::class);
        $f
            ->method('getImports')
            ->willReturn(
                [
                    'a' => 'a',
                    'b' => 'b',
                ],
            );

        $s = $this->createMock(CodeToken::class);
        $s
            ->method('getImports')
            ->willReturn(
                [
                    'b' => 'b',
                    'c' => 'c',
                ],
            );

        $token = new AttributeCodeToken(
            new ReferenceCodeToken('foo', 'bar'),
            [
                $f,
                $s,
            ],
        );

        self::assertSame(
            [
                'foo' => 'bar',
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
            ],
            $token->getImports(),
        );
    }
}
