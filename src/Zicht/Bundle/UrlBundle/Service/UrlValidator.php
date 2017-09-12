<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class UrlValidator
 *
 * @package Zicht\Bundle\UrlBundle\Util
 */
class UrlValidator
{
    /**
     * Returns true when url does not return error codes or not found.
     *
     * @param string $url
     * @return boolean
     */
    public function validate($url)
    {
        if (null !== ($headers = $this->getHeader($url))) {
            $statusCode = $this->getStatusCode($headers);
            // see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
            return ($statusCode >= 200 && $statusCode < 300);
        }
        return false;
    }

    /**
     * Fetch a url with a HEAD request because we just want to check status code.
     *
     * @param string $url
     * @return array|null
     */
    protected function getHeader($url)
    {
        if (false !== @file_get_contents($url, false, stream_context_create(['http' => ['method' => 'HEAD']]))) {
            return $http_response_header;
        } else {
            return null;
        }
    }

    /**
     * Parse the headers array and search for the status pattern
     *
     * @param array $headers
     * @return int
     */
    protected function getStatusCode(array $headers)
    {
        $status = 0;
        foreach ($headers as $header) {
            if (preg_match('#^HTTP/(?:[^\s]+)\s(?P<code>\d+)\s#', $header, $match)) {
                $status = (int)$match['code'];
            }
        }
        return $status;
    }
}
