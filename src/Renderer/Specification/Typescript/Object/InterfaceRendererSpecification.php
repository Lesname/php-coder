<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript\Object;

use Override;
use LesCoder\Utility\TextUtility;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\InterfaceCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class InterfaceRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true InterfaceCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof InterfaceCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(InterfaceCodeToken::class, $token);
        }

        $header = '';
        $body = '';

        foreach ($token->attributes as $attribute) {
            $header .= $renderer->render($attribute) . PHP_EOL;
        }

        if (count($token->extends) > 0) {
            $extends = ' extends '
                . implode(
                    ', ',
                    array_map(
                        static fn(CodeToken $token): string => $renderer->render($token),
                        $token->extends,
                    )
                );
        } else {
            $extends = '';
        }

        foreach ($token->properties as $property) {
            $body .= TextUtility::indent("{$renderer->render($property)};") . PHP_EOL;
        }

        if ($token->generics) {
            $generics = sprintf(
                '<%s>',
                implode(
                    ', ',
                    array_map(
                        static fn (CodeToken $generic): string => $renderer->render($generic),
                        $token->generics,
                    ),
                ),
            );
        } else {
            $generics = '';
        }

        foreach ($token->methods as $method) {
            if ($body !== '') {
                $body .= PHP_EOL;
            }

            $body .= TextUtility::indent($renderer->render($method)) . PHP_EOL;
        }

        return <<<TYPESCRIPT
{$header}export interface {$token->name}{$generics}{$extends} {
{$body}}

TYPESCRIPT;
    }
}
