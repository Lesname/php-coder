<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Helper;

use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;

/**
 * @psalm-immutable
 */
trait TokenBodyRendererHelper
{
    /**
     * @param array<CodeToken> $tokens
     */
    protected function renderTokenBody(array $tokens, CodeRenderer $renderer): string
    {
        if (count($tokens) === 0) {
            return '';
        }

        $previous = null;
        $body = '';

        foreach ($tokens as $token) {
            if ($previous && $previous::class !== $token::class) {
                $body .= PHP_EOL;
            }

            $body .= TextUtility::indent($renderer->render($token)) . PHP_EOL;

            $previous = $token;
        }

        return $body;
    }
}
