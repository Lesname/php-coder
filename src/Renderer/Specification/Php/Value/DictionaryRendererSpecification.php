<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Value;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class DictionaryRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true DictionaryCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof DictionaryCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(DictionaryCodeToken::class, $token);
        }

        $properties = [];

        foreach ($token->items as $item) {
            $properties[] = "{$renderer->render($item->key)} => {$renderer->render($item->value)}";
        }

        $codeProperties = implode(',' . PHP_EOL, $properties);

        if (!str_contains($codeProperties, PHP_EOL)) {
            return "[{$codeProperties}]";
        }

        $codeProperties = TextUtility::indent($codeProperties) . ',';

        return "[\n{$codeProperties}\n]";
    }
}
