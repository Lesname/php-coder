<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Exception;

use LesCoder\Token\CodeToken;
use LesCoder\Exception\AbstractException;

/**
 * @psalm-immutable
 */
final class UnhandledToken extends AbstractException
{
    /**
     * @param class-string<CodeToken> $className
     */
    public function __construct(public string $className)
    {
        parent::__construct("Code token '{$className}' is unhandled");
    }
}
