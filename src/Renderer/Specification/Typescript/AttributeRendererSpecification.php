<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class AttributeRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true AttributeCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof AttributeCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(AttributeCodeToken::class, $token);
        }

        $codeParameters = [];

        foreach ($token->parameters as $parameter) {
            $codeParameters[] = $renderer->render($parameter);
        }

        if (count($codeParameters) > 1) {
            $codeParameters = PHP_EOL
                . TextUtility::indent(implode(',' . PHP_EOL, $codeParameters) . ',')
                . PHP_EOL;
        } else {
            $codeParameters = array_pop($codeParameters) ?? '';
        }

        return "@{$token->reference->name}({$codeParameters})";
    }
}
