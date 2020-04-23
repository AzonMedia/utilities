<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;


abstract class ArrayUtil
{

    /**
     * Similar to validate_array but throws exception on first error.
     * @throws \InvalidArgumentException
     * @param array $data_array
     * @param array $reference_array
     */
    public static function check_array(array $data_array, array $reference_array) : void
    {
        self::validate_array($data_array, $reference_array, $errors);
        if (count($errors)) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }
    }

    /**
     * Validates an array against a reference array.
     * If the provided array has more keys than the reference one it fails.
     * If the provided array has less keys than the reference one it is not considered a failure.
     * @param array $data_array
     * @param array $reference_array
     * @param array|null $errors
     * @return bool
     */
    public static function validate_array(array $data_array, array $reference_array, ?array &$errors = []) : bool
    {
        $ret = TRUE;
//        if ($errors !== NULL) {
//            throw new \InvalidArgumentException(sprintf('The $errors argument must be NULL. %s provided instead.', gettype($errors) ));
//        }
        $errors = [];
        foreach ($data_array as $key=>$value) {
            if (!array_key_exists($key, $reference_array)) {
                $errors[] = sprintf('The provided array contains an unsupported key %s.', $key);
                $ret = FALSE;
                break;
            } elseif (is_array($value)) {
                $ret = self::validate_array($value, $reference_array[$key], $errors);
                if (!$ret) {
                    break;
                }
            } elseif (!TypesUtil::validate_type($value, $reference_array[$key])) {
                $errors[] = sprintf('The key %s is of incorrect type.', $key);
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

    /**
     * Returns an array that contains only the specified keys
     * @param array $array
     * @param array $keys
     * @return array
     */
    public static function extract_keys(array $array, array $keys) : array
    {
        $ret = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                throw new \InvalidArgumentException(sprintf('The provided array does not contain key %s.', $key));
            }
            $ret[$key] = $array[$key];
        }
        return $ret;
    }


    /**
     * Returns the array structure but without the values
     * @return array
     */
    public static function array_keys_recursive(array $array) : array
    {
        $keys = [];
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $keys[$key] = self::array_keys_recursive($value);
            } else {
                $keys[$key] = NULL;
            }
        }
        return $keys;
    }

    /**
     * Removes the columns matching $column_name_regex from a two-dimensional $data array.
     * @param string $data
     * @param string $column_name_regex
     * @return array
     */
    public static function remove_columns_by_name(string $data, string $column_name_regex) : array
    {
        $ret = [];
        foreach ($data as $row) {
            $new_row = [];
            foreach ($row as $column_name => $column_data) {
                if (!preg_match($column_name_regex, $column_name)) {
                    $new_row[$column_name] = $column_data;
                }
            }
            $ret[] = $new_row;
        }
        return $ret;
    }
}