<?php

declare(strict_types=1);

namespace LesCoder\Stream\String;

use Override;
use LesCoder\Stream\String\Exception\CannotReadFile;

final class FileStringStream extends AbstractStringStream
{
    /** @var resource */
    private $handle;

    private string $bufferContent = '';
    private int $bufferSize = 0;

    /**
     * @param int<1, max> $minBufferSize
     *
     * @throws CannotReadFile
     */
    public function __construct(private readonly string $file, private readonly int $minBufferSize = 256)
    {
        $handle = fopen($file, 'r');

        if ($handle === false) {
            throw new CannotReadFile($this->file);
        }

        $this->handle = $handle;
        $this->buffer($this->minBufferSize);
    }

    #[Override]
    public function isEnd(): bool
    {
        return $this->bufferContent === '';
    }

    /**
     * @param int<1, max> $length
     *
     * @throws CannotReadFile
     */
    #[Override]
    public function current(int $length = 1): string
    {
        if ($this->bufferSize < $length) {
            $this->buffer(max($length * 4, $this->minBufferSize));
        }

        return mb_substr($this->bufferContent, 0, $length);
    }

    /**
     * @param int<1, max> $size
     *
     * @throws CannotReadFile
     */
    #[Override]
    public function next(int $size = 1): void
    {
        if (($this->bufferSize - $size) < $this->minBufferSize) {
            $this->buffer(($size * 4) + $this->minBufferSize);
        }

        $this->bufferSize -= $size;
        $this->bufferContent = mb_substr($this->bufferContent, $size);
    }

    /**
     * @param int<1, max> $bytes
     *
     * @throws CannotReadFile
     */
    private function buffer(int $bytes): void
    {
        $further = fread($this->handle, $bytes);

        if ($further === false) {
            throw new CannotReadFile($this->file);
        }

        $this->bufferSize += mb_strlen($further);
        $this->bufferContent .= $further;
    }
}
