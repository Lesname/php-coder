<?php
declare(strict_types=1);

namespace LesCoderTest\Token\Value;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Value\Dictionary\Item;
use LesCoder\Token\Value\DictionaryCodeToken;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DictionaryCodeToken::class)]
final class DictionaryCodeTokenTest extends TestCase
{
    public function testGetImports(): void
    {
        $key = $this->createMock(CodeToken::class);
        $key
            ->method('getImports')
            ->willReturn(
                [
                    'a' => 'a',
                    'b' => 'b',
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

        $item = new Item(
            $key,
            $value,
        );

        $dictionary = new DictionaryCodeToken(
            [
                $item,
            ],
        );

        self::assertSame(
            [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
            ],
            $dictionary->getImports(),
        );
    }
}
