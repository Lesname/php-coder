<?php
declare(strict_types=1);

namespace LesCoder\Stream\Lexical;

use LesCoder\Stream\Stream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

/**
 * @extends Stream<Lexical>
 */
interface LexicalStream extends Stream
{
    public function skip(string $type, string ...$types): LexicalStream;

    /**
     * @param positive-int $step
     */
    public function lookahead(int $step = 1): ?Lexical;
}
