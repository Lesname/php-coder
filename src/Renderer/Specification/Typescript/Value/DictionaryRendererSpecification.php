<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Value;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\VariableCodeToken;
use LesCoder\Token\Value\StringCodeToken;
use LesCoder\Token\Value\DictionaryCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;
use LesCoder\Renderer\Specification\Typescript\Helper\ReservedKeywordHelper;

/**
 * @psalm-immutable
 */
final class DictionaryRendererSpecification implements RendererSpecification
{
    use ReservedKeywordHelper;

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
            if (
                $item->key instanceof StringCodeToken
                && $item->value instanceof VariableCodeToken
                && $item->key->value && $item->value->name
                && !$this->isReservedKeyword($item->value->name)
            ) {
                $list[] = $item->value->name;
            } else {
                if ($item->key instanceof StringCodeToken && ctype_alpha($item->key->value)) {
                    $key = $item->key->value;
                } else {
                    $key = $renderer->render($item->key);
                }

                $value = $renderer->render($item->value);

                $list[] = "{$key}: {$value}";
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
