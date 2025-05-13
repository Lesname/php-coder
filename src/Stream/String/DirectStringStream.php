<?php
declare(strict_types=1);

namespace LesCoder\Stream\String;

use Override;
use LesCoder\Stream\String\Exception\ExpectedExactly;
use LesCoder\Stream\String\Exception\ExpectedKeyword;

final class DirectStringStream implements StringStream
{
    private int $line = 1;

    private int $column = 1;

    public function __construct(private string $input)
    {}

    #[Override]
    public function current(int $length = 1): string
    {
        return mb_substr($this->input, 0, $length);
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
    public function next(int $size = 1): void
    {
        for ($step = 1; $step <= $size; $step += 1) {
            if ($this->current() === PHP_EOL) {
                $this->line += 1;
                $this->column = 1;
            } else {
                $this->column += 1;
            }

            $this->input = mb_substr($this->input, 1);
        }
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
    public function isActive(): bool
    {
        return !$this->isEnd();
    }

    #[Override]
    public function isEnd(): bool
    {
        return strlen($this->input) === 0;
    }
}
