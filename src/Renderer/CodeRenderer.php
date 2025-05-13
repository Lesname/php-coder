<?php
declare(strict_types=1);

namespace LesCoder\Renderer;

use LesCoder\Token\CodeToken;

/**
 * @psalm-immutable
 */
interface CodeRenderer
{
    public function render(CodeToken $token): string;
}
