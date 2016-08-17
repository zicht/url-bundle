<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

/**
 * Class XmlMapper
 *
 * Helper to map urls in an Sitemap XML string from internal to public aliasing or vice versa.
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing
 */
class XmlMapper extends AbstractMapper
{
    public function __construct()
    {
        parent::__construct(['text/xml', 'application/xml'], '/(<loc>)(.*?)(<\/loc>)/');
    }
}
