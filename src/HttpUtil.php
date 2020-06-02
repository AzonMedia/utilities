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
            if (isset($headers[$aa]) && strpos($headers[$aa], '200 OK') !== FALSE ) {
                $ret = TRUE;
                break;
            }
        }
        return $ret;
    }

    public static function get_resource_status_code(string $url): int
    {
        
    }
}