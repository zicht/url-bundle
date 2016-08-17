<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

/**
 * Class RssMapper
 *
 * Helper to map urls in an Rss string from internal to public aliasing or vice versa.
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing
 */
class RssMapper extends AbstractMapper
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(['application/rss+xml'], '/(<link>)(.*?)(<\/link>)/');
    }
}
