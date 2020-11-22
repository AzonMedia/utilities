<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;


abstract class ArrayUtil
{

    public const REMOVE_COLUMN_REGEX = '/.*_id|.*_password/';

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
     * Removes array columns matching $column_name_regex from a two-dimensional $data array.
     * @param string $data
     * @param string $column_name_regex
     * @return array
     */
    public static function remove_columns_by_name(array $data, string $column_name_regex = self::REMOVE_COLUMN_REGEX) : array
    {
        $ret = [];
        foreach ($data as $row) {
            $ret[] = self::remove_columns_by_name_from_row($row, $column_name_regex);
        }
        return $ret;
    }

    public static function remove_columns_by_name_from_row(array $row, string $column_name_regex = self::REMOVE_COLUMN_REGEX): array
    {
        $new_row = [];
        foreach ($row as $column_name => $column_data) {
            if (!preg_match($column_name_regex, $column_name)) {
                $new_row[$column_name] = $column_data;
            }
        }
        return $new_row;
    }

    /**
     * Adds a _formatted column for each column in the twodimensional $data array that appears to be a unix timestamp (10 digits)
     * @param array $data Twodimensional array (rows & columns)
     * @param string $datetime_format
     * @return array
     */
    public static function add_formatted_datetime(array $data, string $datetime_format): array
    {
//        foreach ($data as &$_row) {
//            foreach ($_row as $column_name => $column_data) {
//                if ( ctype_digit( (string) $column_data) && strlen( (string) $column_data) === 10 ) {
//                    //assume this is a timestamp
//                    $time = $column_data;
//                    $_row[$column_name.'_formatted'] = date($datetime_format, $time);
//                } elseif ( ctype_digit( (string) $column_data) && strlen( (string) $column_data) === 16 ) {
//                    //this is a microtime * 1_000_000
//                    $time = (int) ($column_data / 1_000_000);
//                    $_row[$column_name.'_formatted'] = date($datetime_format, $time);
//                } elseif (is_numeric($column_data) && strlen((string) $column_data) === 15) {
//                    //this is microtime(true) - float
//                    $time = (int) $column_data;
//                    $_row[$column_name.'_formatted'] = date($datetime_format, $time);
//                }
//            }
//        }
        $ret = [];
        foreach ($data as $row) {
            $ret[] = self::add_formatted_datetime_to_row($row, $datetime_format);
        }
        return $ret;
    }

    /**
     * Adds a _formatted column for each column that appears to be a unix timestamp (10 digits)
     * @param array $row One-dimensional array representing a row in dataset (may contain columns which array arrays but these are not processed)
     * @param string $datetime_format
     * @return array
     */
    public static function add_formatted_datetime_to_row(array $row, string $datetime_format): array
    {
        $new_row = $row;
        foreach ($row as $column_name => $column_data) {
            if (is_scalar($column_data)) {
                if (ctype_digit((string)$column_data) && strlen((string)$column_data) === 10) {
                    //assume this is a timestamp
                    $time = $column_data;
                    $new_row[$column_name . '_formatted'] = date($datetime_format, $time);
                } elseif (ctype_digit((string)$column_data) && strlen((string)$column_data) === 16) {
                    //this is a microtime * 1_000_000
                    $time = (int)($column_data / 1_000_000);
                    $new_row[$column_name . '_formatted'] = date($datetime_format, $time);
                } elseif (is_numeric($column_data) && strlen((string)$column_data) === 15) {
                    //this is microtime(true) - float
                    $time = (int)$column_data;
                    $new_row[$column_name . '_formatted'] = date($datetime_format, $time);
                }
            }
        }
        return $new_row;
    }

    /**
     * Prepares the provided $data array for serving over API.
     * Uses:
     * @see self::remove_columns_by_name()
     * @see self::add_formatted_datetime()
     * @param array $data Twodimensional array (rows + columns)
     * @param string $remove_by_column_name_regex
     * @param string $add_column_with_datetime_format
     * @return array
     */
    public static function frontify(array $data, string $add_column_with_datetime_format, string $remove_by_column_name_regex = self::REMOVE_COLUMN_REGEX): array
    {
        $data = self::remove_columns_by_name($data, $remove_by_column_name_regex);
        $data = self::add_formatted_datetime($data, $add_column_with_datetime_format);
        return $data;
    }

    /**
     * @param array $row
     * @param string $add_column_with_datetime_format
     * @param string $remove_by_column_name_regex
     * @return array
     */
    public static function frontify_row(array $row, string $add_column_with_datetime_format, string $remove_by_column_name_regex = self::REMOVE_COLUMN_REGEX): array
    {
        $row = self::remove_columns_by_name_from_row($row, $remove_by_column_name_regex);
        $row = self::add_formatted_datetime_to_row($row, $add_column_with_datetime_format);
        return $row;
    }
}