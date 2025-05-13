<?php
declare(strict_types=1);

namespace LesCoder\Utility;

final class TextUtility
{
    private function __construct()
    {}

    /**
     * @psalm-pure
     */
    public static function indent(string $text): string
    {
        $lines = explode(PHP_EOL, $text);
        $intended = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $intended[] = '';

                continue;
            }

            $intended[] = '    ' . $line;
        }

        return implode(PHP_EOL, $intended);
    }
}
