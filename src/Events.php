<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle;

final class Events
{
    /** Before the sitemap is passed back from the AliasSitemapProvider */
    const EVENT_SITEMAP_FILTER = 'zicht_url.sitemap.filter';

    /**
     * no need for this class to initialize
     */
    private function __construct()
    {
    }
}
