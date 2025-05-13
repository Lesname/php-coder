<?php
declare(strict_types=1);

namespace LesCoderTest\Token\Hint;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Hint\GenericCodeToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Token\Hint\GenericCodeToken
 */
final class GenericCodeTokenTest extends TestCase
{
    public function testgetImports(): void
    {
        $base = $this->createMock(CodeToken::class);
        $base
            ->method('getImports')
            ->willReturn(
                [
                    'a' => 'a',
                    'b' => 'b',
                ],
            );
        $key = $this->createMock(CodeToken::class);
        $key
            ->method('getImports')
            ->willReturn(
                [
                    'd' => 'd',
                ],
            );
        $value = $this->createMock(CodeToken::class);
        $value
            ->method('getImports')
            ->willReturn(
                [
                    'b' => 'b',
                    'c' => 'c',
                ],
            );

        $token = new GenericCodeToken($base, [$key, $value]);

        self::assertSame(
            [
                'a' => 'a',
                'b' => 'b',
                'd' => 'd',
                'c' => 'c',
            ],
            $token->getImports(),
        );
    }
}
