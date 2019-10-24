<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;


abstract class ArrayUtil
{
    /**
     * @param array $data_array
     * @param array $reference_array
     * @return bool
     */
    public static function validate_array(array $data_array, array $reference_array) : bool
    {
        $ret = TRUE;
        foreach ($data_array as $key=>$value) {
            if (!array_key_exists($key, $reference_array)) {
                $ret = FALSE;
                break;
            } elseif (is_array($value)) {
                $ret = self::validate_array($value, $reference_array[$key]);
                if (!$ret) {
                    break;
                }
            } elseif (!TypesUtil::validate_type($value, $reference_array[$key])) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    /**
     * The array is expected to have only values that can be cast to strings.
     * To be used for hashing arrays
     * @param array $array Multidimensional array
     * @return string The concatenated string of keys and values
     */
    public static function array_as_string(array $array)
    {
        $str = '';//concatenated keys and values recuresively
        $func = function (array $array) use (&$str, &$func) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $func($value);
                } else {
                    $str .= $key . $value;
                }
            }
        };
        $func($array);
        return $str;
    }

    public static function prefix_keys(array $array, string $prefix) : array
    {
        $ret = [];
        foreach ($array as $key=>$value) {
            $ret[$prefix.$key] = $value;
        }
        return $ret;
    }
}