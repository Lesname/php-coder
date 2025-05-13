<?php
declare(strict_types=1);

namespace LesCoder\Token\Hint;

use LesCoder\Token\CodeToken;
use LesCoder\Token\Helper\NoImportsHelper;

/**
 * @psalm-immutable
 */
enum BuiltInCodeToken implements CodeToken
{
    use NoImportsHelper;

    case Any;
    case Boolean;
    case Collection;
    case Dictionary;
    case False;
    case Float;
    case Integer;
    case Never;
    case Null;
    case String;
    case True;
    case Void;
    case Undefined;
}
