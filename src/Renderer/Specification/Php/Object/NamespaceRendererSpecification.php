<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php\Object;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Token\Object\NamespaceCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Helper\CommentRendererHelper;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class NamespaceRendererSpecification implements RendererSpecification
{
    use CommentRendererHelper;

    /**
     * @psalm-assert-if-true NamespaceCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof NamespaceCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(ClassPropertyCodeToken::class, $token);
        }

        if (count($token->body) === 0) {
            return '';
        }

        $imports = array_filter(
            $token->getBodyImports(),
            static function (string $import) use ($token): bool {
                return !str_starts_with($import, $token->name . '\\')
                    ||
                    str_contains(substr($import, strlen($token->name . '\\')), '\\');
            },
        );

        $body = $this->renderImports($token->name, $imports);

        foreach ($token->body as $line) {
            $body .= $renderer->render($line) . PHP_EOL;
        }

        $body = trim($body);

        return <<<PHP
namespace {$token->name};

{$body}

PHP;
    }

    /**
     * @param array<string, string> $imports
     */
    private function renderImports(string $namespace, array $imports): string
    {
        $codeImports = '';

        foreach (array_unique($imports) as $name => $from) {
            if ($from === $namespace) {
                continue;
            }

            $cFrom = str_starts_with($from, '\\')
                ? substr($from, 1)
                : $from;


            if ($name === $from || str_ends_with($from, "\\$name")) {
                $codeImports .= "use {$cFrom};\n";
            } else {
                $codeImports .= "use {$cFrom} as {$name};\n";
            }
        }

        if (count($imports) > 0) {
            $codeImports .= PHP_EOL;
        }

        return $codeImports;
    }
}
