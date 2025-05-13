<?php
declare(strict_types=1);

namespace LesCoder\Renderer;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\Exception\UnhandledToken;
use LesCoder\Renderer\Specification\RendererSpecification;

/**
 * @psalm-immutable
 */
abstract class AbstractSpecificationCodeRenderer implements CodeRenderer
{
    /**
     * @param array<RendererSpecification> $specifications
     */
    public function __construct(private readonly array $specifications = [])
    {}

    /**
     * @throws UnhandledToken
     */
    #[Override]
    public function render(CodeToken $token): string
    {
        foreach ($this->specifications as $specification) {
            if ($specification->canRender($token)) {
                return  $specification->render($token, $this);
            }
        }

        throw new UnhandledToken($token::class);
    }
}
