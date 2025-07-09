<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\General\Expression;

use Override;
use RuntimeException;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Expression\ComparisonOperator;
use LesCoder\Token\Expression\ComparisonCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class ComparisonRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true ComparisonCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof ComparisonCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ComparisonCodeToken::class, $token);
        }

        $operator = match ($token->operator) {
            ComparisonOperator::Equal => '==',
            ComparisonOperator::Identical => '===',
            ComparisonOperator::NotEqual => '!=',
            ComparisonOperator::NotIdentical => '!==',
            ComparisonOperator::LessThan => '<',
            ComparisonOperator::GreaterThan => '>',
            ComparisonOperator::LessThanOrEqual => '<=',
            ComparisonOperator::GreaterThanOrEqual => '>=',
            ComparisonOperator::InstanceOf => 'instanceof',
            ComparisonOperator::In => throw new RuntimeException('No supported'),
        };

        return "{$renderer->render($token->left)} {$operator} {$renderer->render($token->right)}";
    }
}
