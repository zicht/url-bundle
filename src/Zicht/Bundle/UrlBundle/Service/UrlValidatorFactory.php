<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Service;

use GuzzleHttp\Client;

/**
 * Class UrlValidatorFactory
 *
 * @package Zicht\Bundle\UrlBundle\Service
 */
class UrlValidatorFactory
{
    /**
     * Factory UrlValidator
     *
     * @return UrlValidator
     */
    public static function createUrlValidatorFactory()
    {
        $client = new Client();

        return new UrlValidator($client);
    }
}
