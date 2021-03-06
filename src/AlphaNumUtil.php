<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;


abstract class AlphaNumUtil
{

    public const INDENTATION_LENGTH = 4;

    /**
     * Converts the provided value to a string no matter what is provided.
     * To be used in error messages.
     *
     * @param mixed $value
     * @return string
     *
     * @author vesko@azonmedia.com
     * @created 22.10.2018
     * @since 0.7.4
     */
    public static function as_string( /* mixed */ $value): string
    {
        $string_max_length = 100;
        $max_array_depth = 10;

        $ret = '';
        if (is_null($value)) {
            $ret = 'NULL (null)';
        } elseif (is_bool($value)) {
            if ($value) {
                $ret = 'TRUE (bool)';
            } else {
                $ret = 'FALSE (bool)';
            }
        } elseif (is_string($value)) {
            if (strlen($value) > $string_max_length) {
                $value = substr($value, 0, $string_max_length) . '...';
            }
            $ret = $value . ' (string)';
        } elseif (is_int($value)) {
            $ret = $value . ' (int)';
        } elseif (is_float($value)) {
            $ret = $value . ' (float)';
        } elseif (is_resource($value)) {
            $ret = $value . ' (resource)';
        } elseif (is_array($value)) {
            //it expects that all elements have the same count
            $depth = 0;
            $count_f = function ($value) use (&$count_f, $depth, $max_array_depth) : string {
                $ret = '';
                if (is_array($value)) {
                    if ($depth < $max_array_depth) {
                        $ret .= '[' . count($value) . ']';
                        $ret .= $count_f(array_shift($value));
                    } else {
                        $ret .= '...';
                    }
                }
                return $ret;
            };
            $ret = 'Array' . $count_f($value) . ' (array)';
        } elseif (is_object($value)) {
            $ret = get_class($value);
            $ret .= ' OID:' . spl_object_hash($value);
            $ret .= ' (object)';
        }

        return $ret;
    }

    /**
     * @param string $string
     * @param int $indentation Number of indentations
     * @param int $indentation_length Size of a single indent
     * @return string
     */
    public static function indent(string $string, $indentation = 1, $indentation_length = self::INDENTATION_LENGTH) : string
    {
        $ret = '';
        $lines = explode(PHP_EOL, $string);
        foreach ($lines as $line) {
            $ret .= str_repeat(' ',$indentation * $indentation_length).$line.PHP_EOL;
        }
        $ret = substr($ret, 0, -strlen(PHP_EOL));
        return $ret;
    }
}