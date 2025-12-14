<?php

declare(strict_types=1);

namespace LesCoder\Stream\String;

use Override;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Stream\String\Exception\ExpectedKeyword;

abstract class AbstractStringStream implements StringStream
{
    #[Override]
    public function isActive(): bool
    {
        return !$this->isEnd();
    }

    /**
     * @param non-empty-string $input
     *
     * @throws ExpectedExactly
     */
    #[Override]
    public function expectExactly(string $input): void
    {
        $inputLength = mb_strlen($input);
        assert($inputLength > 0);

        if (!$this->matchesExactly($input)) {
            throw new ExpectedExactly(
                $input,
                $this->current($inputLength),
            );
        }

        $this->next($inputLength);
    }

    /**
     * @param non-empty-string $keyword
     *
     * @throws ExpectedKeyword
     */
    #[Override]
    public function expectKeyword(string $keyword): void
    {
        $keywordLength = mb_strlen($keyword);
        assert($keywordLength > 0);

        if (!$this->matchesKeyword($keyword)) {
            throw new ExpectedKeyword(
                $keyword,
                $this->current($keywordLength),
            );
        }

        $this->next($keywordLength);
    }

    #[Override]
    public function skipWhitespace(): void
    {
        while ($this->isActive()) {
            if (trim($this->current()) !== '') {
                break;
            }

            $this->next();
        }
    }

    /**
     * @param non-empty-string $input
     */
    #[Override]
    public function matchesExactly(string $input): bool
    {
        $inputLength = mb_strlen($input);
        assert($inputLength > 0);

        return $this->isActive() && $this->current($inputLength) === $input;
    }

    /**
     * @param non-empty-string $keyword
     */
    #[Override]
    public function matchesKeyword(string $keyword): bool
    {
        $length = mb_strlen($keyword);
        $current = $this->current($length + 1);
        $quotedKeyword = preg_quote($keyword);

        return $this->isActive()
            && preg_match("#^{$quotedKeyword}([^a-z\d_\$]$|$)#i", $current) === 1;
    }
}
