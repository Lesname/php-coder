<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Exception;

use LesCoder\Token\CodeToken;
use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class UnexpectedCodeToken extends AbstractException
{
    /**
     * @param class-string<CodeToken> $expected
     */
    public function __construct(public readonly string $expected, public readonly CodeToken $gotten)
    {
        $gottenType = get_debug_type($this->gotten);

        parent::__construct("Expected '{$expected}' gotten '{$gottenType}'");
    }
}
