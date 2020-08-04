<?php
declare(strict_types=1);


namespace Azonmedia\Utilities;


abstract class DebugUtil
{
    public static function dump_code_with_lines(string $code) : string
    {
        $ret = '';
        $code = explode(PHP_EOL, $code);
        foreach ($code as $line_num => $code_line) {
            $ret .= '#'.$line_num.str_repeat(' ',4).$code_line.PHP_EOL;
        }
        return $ret;
    }

}