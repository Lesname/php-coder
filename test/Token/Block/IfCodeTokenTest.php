<?php
declare(strict_types=1);

namespace LesCoderTest\Token\Block;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Block\IfCodeToken;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IfCodeToken::class)]
class IfCodeTokenTest extends TestCase
{
    public function testGetImports(): void
    {
        $exp = $this->createMock(CodeToken::class);
        $exp
            ->method('getImports')
            ->willReturn(['fiz' => 'biz']);

        $truthy = $this->createMock(CodeToken::class);
        $truthy
            ->method('getImports')
            ->willReturn(['foo' => 'bar']);

        $falsey = $this->createMock(CodeToken::class);
        $falsey
            ->method('getImports')
            ->willReturn(['bar' => 'foo']);

        $if = new IfCodeToken(
            $exp,
            [$truthy],
            [$falsey],
        );

        self::assertSame(
            [
                'fiz' => 'biz',
                'foo' => 'bar',
                'bar' => 'foo',
            ],
            $if->getImports(),
        );
    }
}
