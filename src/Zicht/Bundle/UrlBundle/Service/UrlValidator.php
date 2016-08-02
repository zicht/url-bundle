<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Service;

use GuzzleHttp\Client;

/**
 * Class UrlValidator
 *
 * @package Zicht\Bundle\UrlBundle\Util
 */
class UrlValidator
{
    /**
     * @var Client
     */
    private $client;

    private $invalidStatusCodes;

    /**
     * UrlValidator constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->invalidStatusCodes = array(
            400,
            401,
            403,
            404,
            405,
            500,
            501,
            502,
            503,
            504,
            511,
        );
    }

    /**
     * Returns true when url does not return error codes or not found.
     *
     * @param string $url
     * @return boolean
     */
    public function validate($url)
    {
        $response = $this->client->request('GET', $url);

        if (in_array($response->getStatusCode(), $this->invalidStatusCodes)) {
            return false;
        }

        return true;
    }
}
