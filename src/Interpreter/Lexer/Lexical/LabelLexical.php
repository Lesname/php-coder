<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Lexer\Lexical;

use Override;

/**
 * @psalm-immutable
 */
final class LabelLexical extends AbstractLexical
{
    public const TYPE = 'label';

    #[Override]
    public function getType(): string
    {
        return self::TYPE;
    }
}
