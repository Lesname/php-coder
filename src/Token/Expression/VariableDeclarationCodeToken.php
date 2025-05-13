<?php
declare(strict_types=1);

namespace LesCoder\Token\Expression;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\NoImportsHelper;

/**
 * @psalm-immutable
 */
final class VariableDeclarationCodeToken implements CodeToken
{
    use NoImportsHelper;

    public function __construct(
        public readonly bool $readonly,
        public readonly string $name,
        public readonly ?CodeToken $hint = null,
    ) {}
}
