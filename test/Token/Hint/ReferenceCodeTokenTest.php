<?php
declare(strict_types=1);

namespace LesCoderTest\Token\Hint;

use LesCoder\Token\Hint\ReferenceCodeToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Token\Hint\ReferenceCodeToken
 */
final class ReferenceCodeTokenTest extends TestCase
{
    public function testGetImports(): void
    {
        $token = new ReferenceCodeToken('fiz', 'bar');

        self::assertEquals(['fiz' => 'bar'], $token->getImports());
    }
}
