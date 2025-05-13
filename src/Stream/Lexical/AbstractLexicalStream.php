<?php
declare(strict_types=1);

namespace LesCoder\Stream\Lexical;

use Override;
use LesCoder\Stream\AbstractStream;
use LesCoder\Interpreter\Lexer\Lexical\Lexical;

/**
 * @extends AbstractStream<Lexical>
 */
abstract class AbstractLexicalStream extends AbstractStream implements LexicalStream
{
    #[Override]
    public function skip(string $type, string ...$types): LexicalStream
    {
        $skip = [$type, ...$types];

        while ($this->isActive()) {
            if (in_array($this->current()->getType(), $skip, true)) {
                $this->next();

                continue;
            }

            break;
        }

        return $this;
    }
}
