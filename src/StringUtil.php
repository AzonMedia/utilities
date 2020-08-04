<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;


abstract class StringUtil
{

    public static function starts_with(string $haystack, string $needle) : bool
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function ends_with(string $haystack, string $needle) : bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return TRUE;
        }

        return (substr($haystack, -$length) === $needle);
    }
}