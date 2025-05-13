<?php
declare(strict_types=1);

namespace LesCoder\Renderer\Specification\Typescript;

use Override;
use LesCoder\Utility\TextUtility;
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

        $file = '';

        foreach ($token->body as $line) {
            $file .= $renderer->render($line) . PHP_EOL;
        }

        return trim($this->renderImports($token->getImports()) . $file)
            . PHP_EOL;
    }

    /**
     * @param array<string, string> $imports
     */
    private function renderImports(array $imports): string
    {
        $smartImports = [];

        foreach ($imports as $name => $from) {
            if (!isset($smartImports[$from])) {
                $smartImports[$from] = [];
            }

            $smartImports[$from][] = $name;
        }

        $codeImports = '';

        foreach ($smartImports as $from => $names) {
            if (count($names) > 3) {
                $names = PHP_EOL
                    . TextUtility::indent(implode(',' . PHP_EOL, $names)) . PHP_EOL;
            } else {
                $names = implode(', ', $names);
            }

            $codeImports .= "import {{$names}} from '{$from}';" . PHP_EOL;
        }

        if (count($imports) > 0) {
            $codeImports .= PHP_EOL;
        }

        return $codeImports;
    }
}
