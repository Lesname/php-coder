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
     * @throws ExpectedExactly
     */
    #[Override]
    public function expectExactly(string $string): void
    {
        if (!$this->matchesExactly($string)) {
            throw new ExpectedExactly(
                $string,
                $this->current(mb_strlen($string)),
            );
        }

        $this->next(mb_strlen($string));
    }

    /**
     * @throws ExpectedKeyword
     */
    #[Override]
    public function expectKeyword(string $keyword): void
    {
        if (!$this->matchesKeyword($keyword)) {
            throw new ExpectedKeyword(
                $keyword,
                $this->current(mb_strlen($keyword)),
            );
        }

        $this->next(mb_strlen($keyword));
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

    #[Override]
    public function matchesExactly(string $input): bool
    {
        return $this->isActive() && $this->current(mb_strlen($input)) === $input;
    }

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
