<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Helper;

use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;

/**
 * @psalm-immutable
 */
trait TokenListRenderHelper
{
    /**
     * @param array<CodeToken> $parameters
     */
    protected function renderTokenList(array $parameters, CodeRenderer $renderer, string $glue, bool $closeGlue = false): string
    {
        $indent = count($parameters) > 2;
        $rendereredParameters = [];
        $lenght = 0;

        foreach ($parameters as $parameter) {
            $rendereredParameter = $renderer->render($parameter);
            $lenght += strlen($rendereredParameter);
            $indent = $indent || str_contains($rendereredParameter, PHP_EOL);

            $rendereredParameters[] = $rendereredParameter;
        }

        if ($indent || (count($parameters) > 1 && $lenght > 99)) {
            return PHP_EOL
                . TextUtility::indent(
                    implode(
                        $glue . PHP_EOL,
                        $rendereredParameters,
                    )
                )
                . ($closeGlue ? $glue : '')
                . PHP_EOL;
        }

        return implode($glue . ' ', $rendereredParameters);
    }
}
