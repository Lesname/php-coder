<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Hint;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Hint\DictionaryCodeToken;
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

        if (count($token->items) === 0) {
            return '{}';
        }

        $list = [];

        foreach ($token->items as $item) {
            if ($item['key'] instanceof StringCodeToken && preg_match('/^[a-z_\$][a-z\d_\$]*$/i', $item['key']->value) === 1) {
                $key = $item['key']->value;
            } else {
                $key = $renderer->render($item['key']);
            }

            $value = $renderer->render($item['value']);
            $access = $item['required'] || !$item['key'] instanceof StringCodeToken
                ? ':'
                : '?:';

            if (isset($item['comment'])) {
                $list[] = "/** {$item['comment']} */\n"
                    . "{$key}{$access} {$value}";
            } else {
                $list[] = "{$key}{$access} {$value}";
            }
        }

        if (count($list) === 1) {
            $item = $list[array_key_first($list)];

            if (!str_contains($item, PHP_EOL)) {
                return "{ {$item} }";
            }
        }

        $items = TextUtility::indent(implode(',' . PHP_EOL, $list) . ',');

        return '{' . PHP_EOL
            . $items . PHP_EOL
            . '}';
    }
}
