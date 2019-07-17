<?php


namespace Azonmedia\Utilities;


abstract class ArrayUtil
{

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
}