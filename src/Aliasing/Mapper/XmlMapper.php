<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

/**
 * Helper to map urls in an Sitemap XML string from internal to public aliasing or vice versa.
 */
class XmlMapper extends AbstractMapper
{
    public function __construct()
    {
        parent::__construct(['text/xml', 'application/xml'], '/(<loc>)(.*?)(<\/loc>)/');
    }
}
