<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification\Helper;

use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedEnd;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLabel;
use LesCoder\Interpreter\Parser\Specification\Typescript\Exception\UnexpectedLexical;

trait ExpectParseSpecificationHelper
{
    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLexical
     */
    protected function expectLexical(LexicalStream $stream, string $expect, string ...$orExpect): void
    {
        if ($stream->isEnd()) {
            throw new UnexpectedEnd($expect);
        }

        if (!in_array($stream->current()->getType(), [$expect, ...$orExpect], true)) {
            throw new UnexpectedLexical($stream->current(), $expect);
        }
    }

    protected function isLexical(LexicalStream $stream, string $lexical, string ...$orLexical): bool
    {
        return $stream->isActive()
            &&
            in_array(
                $stream->current()->getType(),
                [
                    $lexical,
                    ...$orLexical,
                ],
                true,
            );
    }

    /**
     * @throws UnexpectedEnd
     * @throws UnexpectedLabel
     * @throws UnexpectedLexical
     */
    protected function expectKeyword(LexicalStream $stream, string $keyword): void
    {
        if ($stream->isEnd()) {
            throw new UnexpectedEnd(LabelLexical::TYPE);
        }

        $current = $stream->current();

        if ($current->getType() !== LabelLexical::TYPE) {
            throw new UnexpectedLexical($current, LabelLexical::TYPE);
        }

        if (strtolower((string)$current) !== $keyword) {
            throw new UnexpectedLabel($current, $keyword);
        }
    }

    protected function isKeyword(LexicalStream $stream, string $keyword): bool
    {
        if ($stream->isEnd()) {
            return false;
        }

        $current = $stream->current();

        return $current->getType() === LabelLexical::TYPE
            && strtolower((string)$current) === $keyword;
    }
}
