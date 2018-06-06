<?php

namespace Zicht\Bundle\UrlBundle;

/**
 * Class Events
 */
class Events
{
    /**
     * Modify query in the the AliasSitemapProvider
     */
    const EVENT_SITEMAP_QUERY = 'zicht_url.sitemap.query';

    /**
     * Before the sitemap is passed back from the AliasSitemapProvider
     */
    const EVENT_SITEMAP_FILTER = 'zicht_url.sitemap.filter';
}
