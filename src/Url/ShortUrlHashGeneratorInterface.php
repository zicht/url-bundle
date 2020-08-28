<?php
declare(strict_types=1);
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

interface ShortUrlHashGeneratorInterface
{
    /**
     * @param string $url
     * @param int $length
     * @return string
     */
    public function generate($url, $length);
}
