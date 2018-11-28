<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle;

/**
 * Class Events
 */
final class Events
{
    /**
     * no need for this class to initialize
     */
    private function __construct()
    {
    }

    /** Before the sitemap is passed back from the AliasSitemapProvider */
    const EVENT_SITEMAP_FILTER = 'zicht_url.sitemap.filter';
}
