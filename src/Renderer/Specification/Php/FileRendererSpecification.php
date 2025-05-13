<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Php;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Token\FileCodeToken;
use LesCoder\Renderer\CodeRenderer;
use LesCoder\Renderer\Specification\RendererSpecification;
use LesCoder\Renderer\Specification\Exception\UnexpectedCodeToken;

/**
 * @psalm-immutable
 */
final class FileRendererSpecification implements RendererSpecification
{
    /**
     * @psalm-assert-if-true FileCodeToken $token
     */
    #[Override]
    public function canRender(CodeToken $token): bool
    {
        return $token instanceof FileCodeToken;
    }

    /**
     * @throws UnexpectedCodeToken
     */
    #[Override]
    public function render(CodeToken $token, CodeRenderer $renderer): string
    {
        if (!$this->canRender($token)) {
            throw new UnexpectedCodeToken(FileCodeToken::class, $token);
        }

        $file = <<<PHP
<?php
declare(strict_types=1);

{$this->renderImports($token->getImports())}
PHP;

        foreach ($token->body as $line) {
            $file .= $renderer->render($line) . PHP_EOL;
        }

        return rtrim($file) . PHP_EOL;
    }

    /**
     * @param array<string, string> $imports
     */
    private function renderImports(array $imports): string
    {
        $codeImports = '';

        foreach ($imports as $name => $from) {
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
