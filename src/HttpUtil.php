<?php
declare(strict_types=1);

namespace Azonmedia\Utilities;

use Azonmedia\Exceptions\InvalidArgumentException;

abstract class HttpUtil
{
    /**
     * Checks the response headers for 200 OK
     * Follows on redirects
     * @param string $url
     * @return bool
     */
    public static function resource_exists(string $url): bool
    {
        if (!$url) {
            throw new InvalidArgumentException(sprintf(t::_('No URL provided.')));
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf(t::_('The provided URL %s is not a valid one.'), $url));
        }

        $ret = FALSE;
        $headers = get_headers($url, 1);//1 will provide an associative array with the headers. But the HTTP status will remain indexed ([0])
        //if there are redirects these will be available in subsequest indexes - [0], [1], [2] ...
        //limit the redirect lookup up to 10
        for ($aa = 0; $aa < 10; $aa++) {
            if (isset($headers[$aa]) && strpos($headers[$aa], '200 OK') !== FALSE) {
                $ret = TRUE;
                break;
            }
        }
        return $ret;
    }

    public static function get_resource_status_code(string $url): int
    {

    }

    /**
     * Returns an array with the HTTP headers from the provided request.
     * The $php_server_array is the $_SERVER array.
     * @link https://www.php.net/manual/en/function.apache-request-headers.php#70810
     * @param array $php_server_array
     * @return array
     */
    /**
     * From package ralouphie/getallheaders
     * @link https://github.com/ralouphie/getallheaders
     * Get all HTTP header key/values as an associative array for the current request.
     * @param array $php_server_array $_SERVER to be provided
     * @return string[string] The HTTP header key/value pairs.
     */
    function getallheaders(array $server_array)
    {
        $headers = array();

        $copy_server = array(
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        );

        foreach ($server_array as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (!isset($copy_server[$key]) || !isset($server_array[$key])) {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                    $headers[$key] = $value;
                }
            } elseif (isset($copy_server[$key])) {
                $headers[$copy_server[$key]] = $value;
            }
        }

        if (!isset($headers['Authorization'])) {
            if (isset($server_array['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['Authorization'] = $server_array['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($server_array['PHP_AUTH_USER'])) {
                $basic_pass = isset($server_array['PHP_AUTH_PW']) ? $server_array['PHP_AUTH_PW'] : '';
                $headers['Authorization'] = 'Basic ' . base64_encode($server_array['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($server_array['PHP_AUTH_DIGEST'])) {
                $headers['Authorization'] = $server_array['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}