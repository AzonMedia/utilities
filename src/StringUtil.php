<?php

declare(strict_types=1);

namespace Azonmedia\Utilities;

/**
 * Class StringUtil
 * @package Azonmedia\Utilities
 */
abstract class StringUtil
{

    public const DEFAULT_CHARACTERS_LIST = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Replaces the last occurence of a string in the subject.
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return mixed|string
     */
    public static function str_rreplace(string $search, string $replace, string $subject) {
        $pos = strrpos($subject, $search);

        if($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function starts_with(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function ends_with(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return TRUE;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generate_random_string(int $length): string
    {
        $str = '';
        static $list_length;
        if ($list_length === null) {
            $list_length = strlen(self::DEFAULT_CHARACTERS_LIST);
        }
        for ($aa = 0; $aa < $length; $aa++) {
            $str .= self::DEFAULT_CHARACTERS_LIST[mt_rand(0, $list_length - 1)];
        }

        return $str;
    }

    /**
     * Encodes the provided string as base64 and then replaces the non-URL-safe characters in the returned value
     * @see self::decode_base64_url_safe()
     * @param string $value
     * @return string
     */
    public static function encode_base64_url_safe($value) {
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($value));
    }

    /**
     * Decodes a encode_base64_url_safe() encoded string
     * @see self::encode_base64_url_safe()
     * @param string $value
     * @return string
     */
    public static function decode_base64_url_safe($value) {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $value));
    }

}