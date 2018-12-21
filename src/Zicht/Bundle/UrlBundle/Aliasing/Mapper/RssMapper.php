<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

/**
 * Class RssMapper
 *
 * Helper to map urls in an Rss string from internal to public aliasing or vice versa.
 *
 */
class RssMapper extends AbstractMapper
{
    public function __construct()
    {
        parent::__construct(['application/rss+xml'], '/(<link>)(.*?)(<\/link>)/');
    }
}
