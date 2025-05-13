<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification;

use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;

/**
 * @psalm-immutable
 */
interface RendererSpecification
{
    public function canRender(CodeToken $token): bool;

    public function render(CodeToken $token, CodeRenderer $renderer): string;
}
