<?php
declare(strict_types=1);

namespace LesCoderTest\Token;

use LesCoder\Token\AbstractCollectionCodeToken;
use LesCoder\Token\CodeToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Token\AbstractCollectionCodeToken
 */
final class AbstractCollectionCodeTokenTest extends TestCase
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

        $mock = new class ([$f, $s]) extends AbstractCollectionCodeToken
        {};

        self::assertSame(
            [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
            ],
            $mock->getImports(),
        );
    }
}
